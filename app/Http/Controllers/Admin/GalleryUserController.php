<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GalleryUserController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'active');

        if ($tab === 'deleted') {
            $users = GalleryUser::onlyTrashed()->orderByDesc('deleted_at')->paginate(30)->withQueryString();
        } else {
            $users = GalleryUser::orderByDesc('created_at')->paginate(30)->withQueryString();
        }

        return view('admin.gallery-users.index', compact('users', 'tab'));
    }

    public function edit(GalleryUser $galleryUser): View
    {
        return view('admin.gallery-users.edit', ['user' => $galleryUser]);
    }

    public function update(Request $request, GalleryUser $galleryUser): RedirectResponse
    {
        $data = $request->validate([
            'first_name'       => ['required', 'string', 'max:80'],
            'last_name'        => ['required', 'string', 'max:80'],
            'email'            => ['required', 'email', 'max:255', Rule::unique('gallery_users', 'email')->ignore($galleryUser->id)],
            'phone'            => ['required', 'string', 'max:30'],
            'birth_year'       => ['required', 'integer', 'min:1900', 'max:' . now()->year],
            'gender'           => ['required', Rule::in(['male', 'female', 'prefer_not_to_say'])],
            'instagram_handle' => ['nullable', 'string', 'max:60', 'regex:/^[a-zA-Z0-9_.]+$/'],
        ]);

        $galleryUser->update($data);

        return redirect()->route('admin.gallery-users.index')
            ->with('success', 'Utente aggiornato con successo.');
    }

    public function destroy(GalleryUser $galleryUser): RedirectResponse
    {
        $galleryUser->delete();

        return redirect()->route('admin.gallery-users.index')
            ->with('success', 'Utente eliminato.');
    }

    public function restore(int $id): RedirectResponse
    {
        GalleryUser::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.gallery-users.index', ['tab' => 'deleted'])
            ->with('success', 'Utente ripristinato.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filter = $request->query('filter', 'all'); // all | newsletter | marketing

        $query = GalleryUser::orderBy('last_name')->orderBy('first_name');

        if ($filter === 'newsletter') {
            $query->where('newsletter_consent', true);
        } elseif ($filter === 'marketing') {
            $query->where('marketing_consent', true);
        }

        $filename = 'iscritti-' . $filter . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nome',
                'Cognome',
                'Email',
                'Telefono',
                'Anno nascita',
                'Sesso',
                'Instagram',
                'Email verificata',
                'Newsletter',
                'Marketing',
                'Iscritto il',
            ], ';');

            $query->chunk(500, function ($users) use ($handle): void {
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->first_name,
                        $user->last_name,
                        $user->email,
                        $user->phone,
                        $user->birth_year,
                        GalleryUser::genderLabel($user->gender),
                        $user->instagram_handle ? '@' . $user->instagram_handle : '',
                        $user->email_verified_at ? 'Sì' : 'No',
                        $user->newsletter_consent ? 'Sì' : 'No',
                        $user->marketing_consent ? 'Sì' : 'No',
                        $user->created_at->format('d/m/Y H:i'),
                    ], ';');
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
