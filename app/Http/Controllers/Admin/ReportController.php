<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotoReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $reports = PhotoReport::with(['event', 'photoUpload'])
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.reports.index', compact('reports'));
    }

    /**
     * Hide the reported photo from the public gallery and mark report as resolved.
     */
    public function hidePhoto(PhotoReport $report): RedirectResponse
    {
        $report->load('photoUpload');

        if ($report->photoUpload) {
            $report->photoUpload->update(['is_hidden' => true]);
        }

        $report->update(['status' => 'resolved']);

        return back()->with('success', 'Foto nascosta dalla galleria pubblica.');
    }

    /**
     * Archive an unfounded report without touching the photo.
     */
    public function ignore(PhotoReport $report): RedirectResponse
    {
        $report->update(['status' => 'ignored']);

        return back()->with('success', 'Segnalazione archiviata.');
    }

    public function destroy(PhotoReport $report): RedirectResponse
    {
        $report->delete();

        return redirect()->route('admin.reports.index')
            ->with('success', 'Segnalazione eliminata.');
    }
}
