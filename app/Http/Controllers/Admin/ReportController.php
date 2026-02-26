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

    public function destroy(PhotoReport $report): RedirectResponse
    {
        $report->delete();

        return redirect()->route('admin.reports.index')
            ->with('success', 'Segnalazione eliminata.');
    }
}
