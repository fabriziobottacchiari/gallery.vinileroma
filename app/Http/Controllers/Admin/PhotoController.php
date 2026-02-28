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
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use ZipArchive;

class PhotoController extends Controller
{
    /**
     * Show the photo management page, passing existing gallery photos to Alpine.
     */
    public function show(Event $event): View
    {
        $uploads = $event->photoUploads()
            ->where('status', 'completed')
            ->whereNotNull('media_id')
            ->get()
            ->keyBy('media_id');

        $galleryPhotos = $event->getMedia('gallery')
            ->map(function (Media $media) use ($event, $uploads): array {
                $upload = $uploads->get($media->id);

                return [
                    'mediaId'    => $media->id,
                    'uploadId'   => $upload?->id,
                    'thumb'      => $media->getUrl('thumb'),
                    'isCover'    => $media->id === $event->cover_media_id,
                    'isHidden'   => (bool) $upload?->is_hidden,
                    'coverUrl'   => $upload
                        ? route('admin.events.photos.cover', [$event, $upload])
                        : null,
                ];
            })
            ->values()
            ->all();

        $hasOriginals = $event->photoUploads()
            ->where('status', 'completed')
            ->whereNotNull('original_path')
            ->exists();

        return view('admin.events.photos', compact('event', 'galleryPhotos', 'hasOriginals'));
    }

    /**
     * FilePond process endpoint.
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

        return response((string) $upload->id, 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * FilePond revert endpoint.
     */
    public function revert(Request $request, Event $event): Response
    {
        $uploadId = trim($request->getContent());

        $upload = PhotoUpload::where('event_id', $event->id)
            ->where('id', $uploadId)
            ->where('status', 'pending')
            ->first();

        if ($upload !== null) {
            $upload->delete(); // boot() hook cleans temp file
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
     * Save the new display order for gallery photos.
     * Accepts { media_ids: [id1, id2, ...] } sorted as desired.
     */
    public function reorder(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'media_ids'   => ['required', 'array'],
            'media_ids.*' => ['integer'],
        ]);

        // Only reorder media that actually belongs to this event's gallery
        $validIds   = $event->getMedia('gallery')->pluck('id')->all();
        $orderedIds = array_values(array_intersect($request->media_ids, $validIds));

        if (count($orderedIds) > 0) {
            Media::setNewOrder($orderedIds);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Set a photo as the event cover (used on the public homepage card).
     */
    public function setCover(Event $event, PhotoUpload $photoUpload): JsonResponse
    {
        abort_if($photoUpload->event_id !== $event->id, 404);
        abort_if(! $photoUpload->media_id, 404);

        $event->update(['cover_media_id' => $photoUpload->media_id]);

        return response()->json(['ok' => true]);
    }

    /**
     * Bulk delete multiple gallery photos by their media IDs.
     * Accepts { media_ids: [id1, id2, ...] }.
     */
    public function bulkDestroy(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'media_ids'   => ['required', 'array', 'min:1'],
            'media_ids.*' => ['integer'],
        ]);

        $mediaIds = $request->input('media_ids');

        // Delete Spatie media (files + conversions)
        Media::whereIn('id', $mediaIds)
            ->where('model_type', Event::class)
            ->where('model_id', $event->id)
            ->get()
            ->each->delete();

        // Delete photo upload records (boot() hook cleans leftover temp files)
        $event->photoUploads()
            ->whereIn('media_id', $mediaIds)
            ->each(fn (PhotoUpload $u) => $u->delete());

        // Clear cover if it was one of the deleted photos
        if ($event->cover_media_id && in_array($event->cover_media_id, $mediaIds, true)) {
            $event->update(['cover_media_id' => null]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Download all original photos for an event as a ZIP archive.
     */
    public function downloadZip(Event $event): mixed
    {
        $uploads = $event->photoUploads()
            ->where('status', 'completed')
            ->whereNotNull('original_path')
            ->get(['id', 'original_filename', 'original_path']);

        abort_if($uploads->isEmpty(), 404, 'Nessun originale disponibile per questo evento.');

        $zipPath  = tempnam(sys_get_temp_dir(), 'gallery_zip_') . '.zip';
        $zip      = new ZipArchive();
        $opened   = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        abort_if($opened !== true, 500, 'Impossibile creare l\'archivio ZIP.');

        $usedNames = [];

        foreach ($uploads as $upload) {
            $filePath = Storage::disk('local')->path($upload->original_path);

            if (! file_exists($filePath)) {
                continue;
            }

            // Ensure unique filenames inside the ZIP
            $name = $upload->original_filename;
            if (isset($usedNames[$name])) {
                $usedNames[$name]++;
                $info = pathinfo($name);
                $name = ($info['filename'] ?? $name) . '_' . $usedNames[$name] . '.' . ($info['extension'] ?? '');
            } else {
                $usedNames[$name] = 1;
            }

            $zip->addFile($filePath, $name);
        }

        $zip->close();

        $downloadName = Str::slug($event->title) . '-originali.zip';

        return response()
            ->download($zipPath, $downloadName, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    /**
     * Delete a single already-processed photo.
     */
    public function destroy(Event $event, PhotoUpload $photoUpload): JsonResponse
    {
        abort_if($photoUpload->event_id !== $event->id, 404);

        if ($photoUpload->media_id) {
            $event->getMedia('gallery')
                ->firstWhere('id', $photoUpload->media_id)
                ?->delete();

            if ($event->cover_media_id === $photoUpload->media_id) {
                $event->update(['cover_media_id' => null]);
            }
        }

        $photoUpload->delete();

        return response()->json(['ok' => true]);
    }
}
