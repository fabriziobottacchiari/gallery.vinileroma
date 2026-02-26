<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\PhotoReportMail;
use App\Models\Event;
use App\Models\PhotoReport;
use App\Models\PhotoUpload;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PhotoReportController extends Controller
{
    public function store(Request $request, string $year, string $month, string $slug, PhotoUpload $photoUpload): JsonResponse
    {
        $event = Event::where('slug', $slug)
            ->whereYear('event_date', $year)
            ->whereMonth('event_date', $month)
            ->firstOrFail();

        abort_if($photoUpload->event_id !== $event->id, 404);
        abort_if($photoUpload->status !== 'completed', 404);

        $data = $request->validate([
            'reason'  => ['required', 'in:inappropriate,privacy,copyright,other'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $report = PhotoReport::create([
            'photo_upload_id' => $photoUpload->id,
            'event_id'        => $event->id,
            'reason'          => $data['reason'],
            'comment'         => $data['comment'] ?? null,
            'reporter_ip'     => $request->ip(),
        ]);

        $report->load('event');

        $media    = $event->getMedia('gallery')->firstWhere('id', $photoUpload->media_id);
        $thumbUrl = $media?->getUrl('thumb');
        $adminUrl = route('admin.events.photos', $event);

        $adminEmail = User::where('is_admin', true)->value('email');

        if ($adminEmail) {
            Mail::to($adminEmail)->queue(new PhotoReportMail($report, $thumbUrl, $adminUrl));
        }

        return response()->json(['ok' => true]);
    }
}
