<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\BookController;
use App\Http\Controllers\Web\JobController;
use App\Http\Controllers\Web\WorksheetController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\WorkerController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\PlanningController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\SiteController;
use App\Http\Controllers\Web\SpecialDayController;
use App\Http\Controllers\Web\WorksheetAttachmentController;


// Auth
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    // Dashboard (Kanban)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Job History
    Route::get('/jobs', [JobController::class, 'history'])->name('jobs.history');
    Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
    Route::put('/jobs/{job}/cancel', [JobController::class, 'cancel'])->name('jobs.cancel');
    Route::put('/jobs/{job}/team-leader', [JobController::class, 'updateTeamLeader'])->name('jobs.team-leader');

    // Books
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/worker-alerts', [BookController::class, 'workerAlerts'])->name('books.worker-alerts');

    // Book notifications — manual send
    Route::post('/books/{book}/notify', [BookController::class, 'notify'])->name('books.notify');
    Route::post('/books/notify-all', [BookController::class, 'notifyAll'])->name('books.notify-all');

    // Worksheets
    /*
    Route::get('/worksheets', [WorksheetController::class, 'index'])->name('worksheets.index');
    Route::get('/worksheets/{worksheet}', [WorksheetController::class, 'show'])->name('worksheets.show');
    Route::post('/worksheets/{worksheet}/approve', [WorksheetController::class, 'approve'])->name('worksheets.approve');
    Route::post('/worksheets/{worksheet}/correct', [WorksheetController::class, 'correct'])->name('worksheets.correct');
    Route::post('/containers/{container}/override', [WorksheetController::class, 'overrideContainerPrice'])->name('containers.override');
    Route::post('/worksheets/ocr', [WorksheetController::class, 'ocr'])->name('worksheets.ocr');
    */
    // Worksheets
    Route::get('/worksheets', [WorksheetController::class, 'index'])->name('worksheets.index');
    Route::get('/worksheets/{worksheet}', [WorksheetController::class, 'show'])->name('worksheets.show');
    Route::post('/worksheets/{worksheet}/save', [WorksheetController::class, 'save'])->name('worksheets.save');
    Route::post('/worksheets/{worksheet}/approve', [WorksheetController::class, 'approve'])->name('worksheets.approve');
    Route::post('/worksheets/{worksheet}/add-worker', [WorksheetController::class, 'addWorker'])->name('worksheets.addWorker');
    Route::post('/worksheets/{worksheet}/attachments/upload',  [WorksheetAttachmentController::class, 'upload'])->name('worksheets.attachments.upload');
    Route::post('/worksheets/{worksheet}/attachments/delete',  [WorksheetAttachmentController::class, 'delete'])->name('worksheets.attachments.delete');
    Route::post('/worksheets/{worksheet}/attachments/primary', [WorksheetAttachmentController::class, 'setPrimary'])->name('worksheets.attachments.primary');
    Route::get('/worksheets/{worksheet}/attachments/ocr',      [WorksheetAttachmentController::class, 'ocr'])->name('worksheets.attachments.ocr');


    Route::post('/jobs/{job}/worksheet', [WorksheetController::class, 'create'])->name('worksheets.create');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/export', [PaymentController::class, 'export'])->name('payments.export');
    Route::post('/payments/mark-all-paid', [PaymentController::class, 'markAllPaid'])->name('payments.markAllPaid');
    Route::post('/payments/{worker}/paid', [PaymentController::class, 'markPaid'])->name('payments.markPaid');
    Route::post('/payments/{worker}/unpaid', [PaymentController::class, 'markUnpaid'])->name('payments.markUnpaid');

    // Workers
    Route::get('/workers', [WorkerController::class, 'index'])->name('workers.index');
    Route::get('/workers/create', [WorkerController::class, 'create'])->name('workers.create');
    Route::post('/workers', [WorkerController::class, 'store'])->name('workers.store');
    Route::get('/workers/{worker}', [WorkerController::class, 'show'])->name('workers.show');
    Route::put('/workers/{worker}', [WorkerController::class, 'update'])->name('workers.update');
    
    // Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::post('/clients/{client}/prices', [ClientController::class, 'savePrices'])->name('clients.prices.save');
    Route::post('/clients/{client}/skills', [ClientController::class, 'saveSkills'])->name('clients.skills.save');
    Route::post('/clients/{client}/boxes',  [ClientController::class, 'saveBoxes'])->name('clients.boxes.save');
    Route::post('/clients/{client}/hourly', [ClientController::class, 'saveHourly'])->name('clients.hourly.save');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggleActive'])->name('products.toggleActive');
    
    // Client pricing (all through ProductController)
    Route::post('/clients/{client}/container-prices', [ClientController::class, 'saveContainerPrices'])->name('clients.container-prices.save');
    Route::post('/clients/{client}/box-additional', [ProductController::class, 'saveBoxAdditional'])->name('clients.box-additional.save');
    Route::post('/clients/{client}/skill-additional', [ProductController::class, 'saveSkillAdditional'])->name('clients.skill-additional.save');
    Route::post('/clients/{client}/hourly-rates', [ClientController::class, 'saveHourlyRates'])->name('clients.hourly-rates.save');

    // Special days
    Route::post('/special-days', [SpecialDayController::class, 'store'])->name('special-days.store');
    Route::delete('/special-days/{specialDay}', [SpecialDayController::class, 'destroy'])->name('special-days.destroy');

    // Sites (managed from clients page)
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::patch('/sites/{site}/toggle', [SiteController::class, 'toggleActive'])->name('sites.toggleActive');


    // Notifications tab
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/books/{book}/notify', [BookController::class, 'notify'])->name('books.notify');
    Route::post('/books/notify-selected', [NotificationController::class, 'notifySelected'])->name('books.notify-selected');
    Route::post('/books/notify-all', [NotificationController::class, 'notifyAll'])->name('books.notify-all');

    // Day Planning
    Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index');
    Route::post('/planning', [PlanningController::class, 'store'])->name('planning.store');
    Route::patch('/planning/{demand}/allocate', [PlanningController::class, 'markAllocated'])->name('planning.mark-allocated');
    Route::patch('/planning/{demand}/cancel', [PlanningController::class, 'cancel'])->name('planning.cancel');
    Route::delete('/planning/{demand}', [PlanningController::class, 'destroy'])->name('planning.destroy');

    // Container Additional
    Route::post('/clients/{client}/container-additionals', [ProductController::class, 'saveContainerAdditionals'])->name('clients.container-additionals.save');
});
