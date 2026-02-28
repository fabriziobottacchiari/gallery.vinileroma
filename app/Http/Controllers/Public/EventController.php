<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\GalleryUserFavorite;
use App\Models\PhotoUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $q     = trim((string) $request->query('q', ''));
        $year  = (string) $request->query('year', '');
        $month = (string) $request->query('month', '');
        $day   = (string) $request->query('day', '');

        $query = Event::published()->public()->with('media')->orderByDesc('event_date');

        if ($q !== '') {
            $query->where('title', 'like', '%' . $q . '%');
        }
        if ($year !== '' && ctype_digit($year)) {
            $query->whereYear('event_date', $year);
        }
        if ($month !== '' && ctype_digit($month)) {
            $query->whereMonth('event_date', $month);
        }
        if ($day !== '' && ctype_digit($day)) {
            $query->whereDay('event_date', $day);
        }

        $events = $query->paginate(24)->withQueryString();

        // Available filter options (scoped to already-applied filters for cascading UX)
        $base = Event::published()->public();

        $availableYears = (clone $base)
            ->selectRaw('YEAR(event_date) as y')
            ->distinct()->orderByDesc('y')->pluck('y');

        $availableMonths = (clone $base)
            ->when($year !== '' && ctype_digit($year), fn ($q) => $q->whereYear('event_date', $year))
            ->selectRaw('MONTH(event_date) as m')
            ->distinct()->orderBy('m')->pluck('m');

        $availableDays = (clone $base)
            ->when($year !== '' && ctype_digit($year), fn ($q) => $q->whereYear('event_date', $year))
            ->when($month !== '' && ctype_digit($month), fn ($q) => $q->whereMonth('event_date', $month))
            ->selectRaw('DAY(event_date) as d')
            ->distinct()->orderBy('d')->pluck('d');

        $monthNames = [
            1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo',
            4 => 'Aprile',  5 => 'Maggio',   6 => 'Giugno',
            7 => 'Luglio',  8 => 'Agosto',   9 => 'Settembre',
            10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre',
        ];

        // Preload cover URLs keyed by event ID
        $coverUrls = [];
        foreach ($events as $event) {
            if ($event->cover_media_id) {
                $media = $event->getMedia('gallery')->firstWhere('id', $event->cover_media_id);
                if ($media) {
                    $coverUrls[$event->id] = $media->getUrl('thumb');
                }
            }
            if (! isset($coverUrls[$event->id])) {
                $first = $event->getFirstMedia('gallery');
                if ($first) {
                    $coverUrls[$event->id] = $first->getUrl('thumb');
                }
            }
        }

        return view('public.events.index', compact(
            'events', 'coverUrls',
            'q', 'year', 'month', 'day',
            'availableYears', 'availableMonths', 'availableDays', 'monthNames'
        ));
    }

    public function show(Request $request, string $year, string $month, string $day, string $slug): View
    {
        $event = Event::where('slug', $slug)
            ->whereYear('event_date', $year)
            ->whereMonth('event_date', $month)
            ->whereDay('event_date', $day)
            ->firstOrFail();

        abort_if($event->status !== 'published', 404);

        // Track view — once per session per event to avoid bot inflation
        $viewKey = 'viewed_' . $event->id;
        if (! $request->session()->has($viewKey)) {
            $event->increment('views_count');
            $request->session()->put($viewKey, true);
        }

        $uploads = $event->photoUploads()
            ->where('status', 'completed')
            ->whereNotNull('media_id')
            ->get()
            ->keyBy('media_id');

        $hiddenMediaIds = $uploads->filter(fn (PhotoUpload $u) => $u->is_hidden)->keys()->all();

        // Determine which media the current user has favourited
        $galleryUser  = auth('gallery')->user();
        $favoritedIds = [];
        if ($galleryUser) {
            $allMediaIds  = $event->getMedia('gallery')->pluck('id')->all();
            $favoritedIds = GalleryUserFavorite::where('gallery_user_id', $galleryUser->id)
                ->whereIn('media_id', $allMediaIds)
                ->pluck('media_id')
                ->all();
        }

        $photos = $event->getMedia('gallery')
            ->reject(fn ($media) => in_array($media->id, $hiddenMediaIds, true))
            ->map(function ($media) use ($event, $uploads, $favoritedIds) {
                $upload = $uploads->get($media->id);

                return [
                    'id'          => $media->id,
                    'thumb'       => $media->getUrl('thumb'),
                    'preview'     => $media->getUrl('preview'),
                    'filename'    => $media->file_name,
                    'favoriteUrl' => route('public.favorites.toggle', $media->id),
                    'favorited'   => in_array($media->id, $favoritedIds, true),
                    'reportUrl'   => $upload
                        ? route('public.photo-report.store', [...$event->publicRouteParams(), 'photoUpload' => $upload->id])
                        : null,
                    'downloadUrl' => $upload
                        ? route('public.events.photos.download', [...$event->publicRouteParams(), 'photoUpload' => $upload->id])
                        : null,
                ];
            })
            ->values()
            ->all();

        // Determine OG image: ?foto={mediaId} > cover_media_id > first photo
        $ogImageUrl = null;
        $fotoParam  = (int) $request->query('foto', 0);

        if ($fotoParam) {
            $ogMedia    = $event->getMedia('gallery')->firstWhere('id', $fotoParam);
            $ogImageUrl = $ogMedia?->getUrl('preview');
        }

        if (! $ogImageUrl && $event->cover_media_id) {
            $coverMedia = $event->getMedia('gallery')->firstWhere('id', $event->cover_media_id);
            $ogImageUrl = $coverMedia?->getUrl('preview');
        }

        if (! $ogImageUrl) {
            $first      = $event->getMedia('gallery')->first();
            $ogImageUrl = $first?->getUrl('preview');
        }

        return view('public.events.show', compact('event', 'photos', 'ogImageUrl'));
    }

    /**
     * Track a photo download and redirect to the full-resolution media URL.
     */
    public function download(string $year, string $month, string $day, string $slug, PhotoUpload $photoUpload): RedirectResponse
    {
        $event = Event::where('slug', $slug)
            ->whereYear('event_date', $year)
            ->whereMonth('event_date', $month)
            ->whereDay('event_date', $day)
            ->firstOrFail();

        abort_if($event->status !== 'published', 404);
        abort_if($photoUpload->event_id !== $event->id, 404);
        abort_if($photoUpload->status !== 'completed', 404);
        abort_if($photoUpload->is_hidden, 404);
        abort_if(! $photoUpload->media_id, 404);

        $media = $event->getMedia('gallery')->firstWhere('id', $photoUpload->media_id);
        abort_if(! $media, 404);

        $photoUpload->increment('downloads_count');

        return redirect($media->getUrl());
    }
}
