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
        $totalEvents  = Event::count();
        $totalPhotos  = PhotoUpload::where('status', 'completed')->count();
        $totalReports = PhotoReport::count();

        $recentEvents = Event::with('media')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalEvents',
            'totalPhotos',
            'totalReports',
            'recentEvents',
        ));
    }
}
