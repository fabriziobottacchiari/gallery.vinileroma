<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryUserFavorite;
use App\Models\PhotoUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FavoriteController extends Controller
{
    /**
     * Toggle a photo as favourite for the authenticated gallery user.
     * Returns JSON {favorited: bool}.
     */
    public function toggle(int $mediaId): JsonResponse
    {
        /** @var \App\Models\GalleryUser $user */
        $user = auth('gallery')->user();

        $existing = GalleryUserFavorite::where('gallery_user_id', $user->id)
            ->where('media_id', $mediaId)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['favorited' => false]);
        }

        $media = Media::find($mediaId);
        abort_if(! $media || $media->collection_name !== 'gallery', 404);

        $photoUpload = PhotoUpload::where('media_id', $mediaId)
            ->where('status', 'completed')
            ->first();

        GalleryUserFavorite::create([
            'gallery_user_id' => $user->id,
            'media_id'        => $mediaId,
            'event_id'        => $media->model_id,
            'photo_upload_id' => $photoUpload?->id,
        ]);

        return response()->json(['favorited' => true]);
    }

    /**
     * Show the authenticated user's saved photos ("Le mie foto").
     */
    public function index(): View
    {
        /** @var \App\Models\GalleryUser $user */
        $user = auth('gallery')->user();

        $favorites = GalleryUserFavorite::where('gallery_user_id', $user->id)
            ->with('event')
            ->orderByDesc('created_at')
            ->paginate(48);

        $mediaMap = Media::whereIn('id', $favorites->pluck('media_id')->all())
            ->get()
            ->keyBy('id');

        $photos = $favorites
            ->map(function (GalleryUserFavorite $fav) use ($mediaMap): ?array {
                $media = $mediaMap->get($fav->media_id);
                if (! $media || ! $fav->event) {
                    return null;
                }

                return [
                    'id'          => $fav->media_id,
                    'thumb'       => $media->getUrl('thumb'),
                    'event'       => $fav->event,
                    'favoriteUrl' => route('public.favorites.toggle', $fav->media_id),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return view('public.favorites.index', compact('photos', 'favorites'));
    }
}
