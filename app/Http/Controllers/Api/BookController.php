<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $workerId = $request->user()->worker_id;

        $books = Book::whereHas('workers', fn($q) => $q->where('worker_id', $workerId))
            ->with(['jobs.site.client', 'jobs.teamLeader'])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['books' => $books]);
    }

    public function show(Request $request, Book $book)
    {
        $workerId = $request->user()->worker_id;

        abort_unless($book->workers->contains($workerId), 403);

        $book->load(['jobs.site.client', 'jobs.teamLeader', 'jobs.containers.product', 'jobs.worksheet']);

        $book->jobs->each(function ($job) use ($workerId) {
            $job->is_team_leader = $job->team_leader_id === $workerId;
        });

        return response()->json(['book' => $book]);
    }
}
