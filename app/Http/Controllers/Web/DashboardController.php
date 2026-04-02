<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Worksheet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $viewMode = $request->get('view', 'day'); // 'day' or 'week'
        $date     = $request->get('date', today()->toDateString());

        $parsedDate = Carbon::parse($date);

        if ($viewMode === 'week') {
            $from = $parsedDate->copy()->startOfWeek();
            $to   = $parsedDate->copy()->endOfWeek();
        } else {
            $from = $parsedDate->copy()->startOfDay();
            $to   = $parsedDate->copy()->endOfDay();
        }

        $jobs = Job::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->with([
                'site.client',
                'book.workers',
                'teamLeader',
                'containers',
                'worksheet',
            ])
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn($j) => $j->status instanceof \BackedEnum ? $j->status->value : (string)$j->status);

        $allJobs = $jobs->flatten();

        $kpis = [
            'jobs_today'         => $allJobs->count(),
            'workers_allocated'  => $allJobs->flatMap(fn($j) => $j->book->workers->pluck('id'))->unique()->count(),
            'worksheets_pending' => Worksheet::where('sync_status', 'pending')->count(),
            'containers_today'   => $allJobs->sum(fn($j) => $j->containers->count()),
        ];

        return view('dashboard.index', compact('jobs', 'kpis', 'date', 'viewMode'));
    }
}
