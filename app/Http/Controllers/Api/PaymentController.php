<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkerPayment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $workerId = $request->user()->worker_id;

        $payments = WorkerPayment::where('worker_id', $workerId)
            ->with(['worksheet.job.site.client'])
            ->orderBy('week_period', 'desc')
            ->paginate(20);

        return response()->json(['payments' => $payments]);
    }

    public function currentWeek(Request $request)
    {
        $workerId  = $request->user()->worker_id;
        $weekStart = now()->startOfWeek()->toDateString();

        $payments = WorkerPayment::where('worker_id', $workerId)
            ->where('week_period', $weekStart)
            ->with(['worksheet.job.site.client'])
            ->get();

        return response()->json([
            'week_start' => $weekStart,
            'total'      => $payments->sum('total_amount'),
            'payments'   => $payments,
        ]);
    }
}
