<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PhotoUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::published()->public()
            ->with('media')
            ->orderByDesc('event_date')
            ->paginate(24);

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

        return view('public.events.index', compact('events', 'coverUrls'));
    }

    public function show(Request $request, string $year, string $month, string $slug): View
    {
        $event = Event::where('slug', $slug)
            ->whereYear('event_date', $year)
            ->whereMonth('event_date', $month)
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

        $photos = $event->getMedia('gallery')
            ->reject(fn ($media) => in_array($media->id, $hiddenMediaIds, true))
            ->map(function ($media) use ($event, $uploads) {
                $upload = $uploads->get($media->id);

                return [
                    'id'          => $media->id,
                    'thumb'       => $media->getUrl('thumb'),
                    'preview'     => $media->getUrl('preview'),
                    'filename'    => $media->file_name,
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
    public function download(string $year, string $month, string $slug, PhotoUpload $photoUpload): RedirectResponse
    {
        $event = Event::where('slug', $slug)
            ->whereYear('event_date', $year)
            ->whereMonth('event_date', $month)
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
