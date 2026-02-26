<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PhotoReport;
use App\Models\PhotoUpload;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalEvents        = Event::count();
        $totalPhotos        = PhotoUpload::where('status', 'completed')->count();
        $totalPendingReports = PhotoReport::where('status', 'pending')->count();
        $totalDownloads     = PhotoUpload::sum('downloads_count');

        $recentEvents = Event::with('media')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $topEvents = Event::where('views_count', '>', 0)
            ->orderByDesc('views_count')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalEvents',
            'totalPhotos',
            'totalPendingReports',
            'totalDownloads',
            'recentEvents',
            'topEvents',
        ));
    }
}
