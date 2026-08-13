<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TravelRequestController;
use App\Http\Controllers\ReimbursementController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
    
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user->isRole('employee')) {
            $travelRequests = \App\Models\TravelRequest::where('user_id', $user->id)->latest()->take(10)->get();
            $reimbursements = \App\Models\Reimbursement::where('user_id', $user->id)->latest()->take(10)->get();
        } elseif ($user->isRole('manager')) {
            // Managers focus on pending manager travel requests
            $travelRequests = \App\Models\TravelRequest::latest()->get();
            $reimbursements = \App\Models\Reimbursement::latest()->get();
        } elseif ($user->isRole('finance')) {
            // Finance focus on pending finance cash reimbursements
            $travelRequests = \App\Models\TravelRequest::latest()->get();
            $reimbursements = \App\Models\Reimbursement::latest()->get();
        } else {
            $travelRequests = \App\Models\TravelRequest::latest()->get();
            $reimbursements = \App\Models\Reimbursement::latest()->get();
        }

        // Count pending tasks
        $pendingTRFCount = \App\Models\TravelRequest::where('status', 'pending_manager')->count();
        $pendingCRFCount = \App\Models\Reimbursement::where('status', 'pending_finance')->count();

        return view('dashboard', compact('travelRequests', 'reimbursements', 'pendingTRFCount', 'pendingCRFCount'));
    })->name('dashboard');

    // Reimbursement choice page (Travel / Non Travel)
    Route::get('/reimbursement-choice', [ReimbursementController::class, 'choice'])->name('reimbursement-choice');

    // Travel Requests (TRF)
    Route::prefix('travel-requests')->name('travel-requests.')->group(function () {
        Route::get('/', [TravelRequestController::class, 'index'])->name('index');
        Route::get('/create', [TravelRequestController::class, 'create'])->name('create');
        Route::post('/', [TravelRequestController::class, 'store'])->name('store');
        Route::get('/{travelRequest}', [TravelRequestController::class, 'show'])->name('show');
        Route::post('/{travelRequest}/submit', [TravelRequestController::class, 'submit'])->name('submit');
        Route::post('/{travelRequest}/approve', [TravelRequestController::class, 'approve'])->name('approve');
        Route::post('/{travelRequest}/reject', [TravelRequestController::class, 'reject'])->name('reject');
    });

    // Reimbursements (CRF)
    Route::prefix('reimbursements')->name('reimbursements.')->group(function () {
        Route::get('/', [ReimbursementController::class, 'index'])->name('index');
        Route::get('/create', [ReimbursementController::class, 'create'])->name('create');
        Route::post('/', [ReimbursementController::class, 'store'])->name('store');
        Route::get('/{reimbursement}', [ReimbursementController::class, 'show'])->name('show');
        Route::post('/{reimbursement}/submit', [ReimbursementController::class, 'submit'])->name('submit');
        Route::post('/{reimbursement}/verify', [ReimbursementController::class, 'verify'])->name('verify');
        Route::post('/{reimbursement}/reject', [ReimbursementController::class, 'reject'])->name('reject');
    });
});
