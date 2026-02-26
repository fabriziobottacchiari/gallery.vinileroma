<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessEventPhoto;
use App\Models\Event;
use App\Models\PhotoUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PhotoController extends Controller
{
    /**
     * Show the photo management page for an event.
     */
    public function show(Event $event): View
    {
        return view('admin.events.photos', compact('event'));
    }

    /**
     * FilePond process endpoint — receives one file per request.
     * Returns the upload ID as plain text (FilePond server ID).
     */
    public function upload(Request $request, Event $event): Response
    {
        $request->validate([
            'filepond' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif,bmp,tiff', 'max:20480'],
        ]);

        $batchUuid = $request->header('X-Batch-Uuid') ?? Str::uuid()->toString();
        $file      = $request->file('filepond');

        $tempPath = $file->store('photo-uploads', ['disk' => 'local']);

        $upload = PhotoUpload::create([
            'event_id'          => $event->id,
            'batch_uuid'        => $batchUuid,
            'original_filename' => $file->getClientOriginalName(),
            'temp_path'         => $tempPath,
            'status'            => 'pending',
        ]);

        ProcessEventPhoto::dispatch($upload->id);

        // FilePond expects the server ID as plain text response
        return response((string) $upload->id, 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * FilePond revert endpoint — deletes a pending upload.
     * FilePond sends the server ID (upload ID) as plain text request body.
     */
    public function revert(Request $request, Event $event): Response
    {
        $uploadId = trim($request->getContent());

        $upload = PhotoUpload::where('event_id', $event->id)
            ->where('id', $uploadId)
            ->where('status', 'pending')
            ->first();

        if ($upload !== null) {
            if ($upload->temp_path) {
                Storage::disk('local')->delete($upload->temp_path);
            }
            $upload->delete();
        }

        return response('', 200);
    }

    /**
     * Polling endpoint — returns upload statuses for this event.
     */
    public function status(Request $request, Event $event): JsonResponse
    {
        $batchUuid = $request->query('batch');

        $query = PhotoUpload::where('event_id', $event->id)
            ->orderByDesc('created_at')
            ->limit(500);

        if ($batchUuid) {
            $query->where('batch_uuid', $batchUuid);
        }

        $uploads = $query->get(['id', 'original_filename', 'status', 'error_message', 'media_id', 'created_at']);

        return response()->json([
            'total'      => $uploads->count(),
            'pending'    => $uploads->where('status', 'pending')->count(),
            'processing' => $uploads->where('status', 'processing')->count(),
            'completed'  => $uploads->where('status', 'completed')->count(),
            'failed'     => $uploads->where('status', 'failed')->count(),
            'items'      => $uploads->values(),
        ]);
    }

    /**
     * Delete an already-processed photo from the gallery.
     */
    public function destroy(Event $event, PhotoUpload $photoUpload): JsonResponse
    {
        abort_if($photoUpload->event_id !== $event->id, 404);

        if ($photoUpload->media_id) {
            $media = $event->getMedia('gallery')->firstWhere('id', $photoUpload->media_id);
            $media?->delete();
        }

        $photoUpload->delete();

        return response()->json(['ok' => true]);
    }
}
