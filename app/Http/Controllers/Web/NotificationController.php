<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        $books = Book::where('date', $date)
            ->where('status', '!=', 'cancelled')
            ->with([
                'jobs.site.client',
                'jobs.containers',
                'workers.user',
                'notifiedBy',
            ])
            ->orderBy('notified_at')
            ->get();

        $pendingCount  = $books->whereNull('notified_at')->count();
        $notifiedCount = $books->whereNotNull('notified_at')->count();
        $totalWorkers  = $books->whereNull('notified_at')
            ->flatMap(fn($b) => $b->workers)
            ->unique('id')
            ->count();

        return view('notifications.index', compact(
            'books', 'date', 'pendingCount', 'notifiedCount', 'totalWorkers'
        ));
    }

    /**
     * Send notifications for a single book.
     * Also used for resending.
     */
    public function notify(Book $book)
    {
        $book->load(['workers.user', 'jobs.site.client']);
        $this->notifications->sendBookNotifications($book, auth()->id());

        $count = $book->workers->count();
        return back()->with('success', "Notifications sent to {$count} worker(s).");
    }

    /**
     * Send notifications for selected books (bulk).
     */
    public function notifySelected(Request $request)
    {
        $request->validate([
            'book_ids'   => 'required|array|min:1',
            'book_ids.*' => 'exists:books,id',
        ]);

        $books = Book::whereIn('id', $request->book_ids)
            ->with(['workers.user', 'jobs.site.client'])
            ->get();

        $workerCount = 0;
        foreach ($books as $book) {
            $this->notifications->sendBookNotifications($book, auth()->id());
            $workerCount += $book->workers->count();
        }

        return back()->with('success',
            $books->count() . ' book(s) notified — ' . $workerCount . ' worker(s) targeted.'
        );
    }

    /**
     * Send notifications for ALL unnotified books on a date.
     */
    public function notifyAll(Request $request)
    {
        $date  = $request->date ?? today()->toDateString();
        $count = $this->notifications->sendAllPendingNotifications($date, auth()->id());

        if ($count === 0) {
            return back()->with('info', 'All books for this day have already been notified.');
        }

        return back()->with('success', $count . ' book(s) notified for ' . $date . '.');
    }
}
