<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::published()->public()
            ->with('media')
            ->orderByDesc('event_date')
            ->paginate(24);

        return view('public.events.index', compact('events'));
    }

    public function show(Event $event): View
    {
        abort_if($event->status !== 'published', 404);

        // Map Spatie media to a safe array for Alpine.js, keyed by media_id
        $uploads = $event->photoUploads()
            ->where('status', 'completed')
            ->whereNotNull('media_id')
            ->get()
            ->keyBy('media_id');

        $photos = $event->getMedia('gallery')
            ->map(function ($media) use ($event, $uploads) {
                $upload = $uploads->get($media->id);

                return [
                    'id'        => $media->id,
                    'thumb'     => $media->getUrl('thumb'),
                    'preview'   => $media->getUrl('preview'),
                    'filename'  => $media->file_name,
                    'reportUrl' => $upload
                        ? route('public.photo-report.store', [$event, $upload])
                        : null,
                ];
            })
            ->values()
            ->all();

        return view('public.events.show', compact('event', 'photos'));
    }
}
