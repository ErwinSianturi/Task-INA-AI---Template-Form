<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExcelExportController;
use App\Http\Controllers\ReimbursementController;
use App\Http\Controllers\TravelRequestController;
use Illuminate\Support\Facades\Auth;
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
        } else {
            $travelRequests = \App\Models\TravelRequest::latest()->take(10)->get();
            $reimbursements = \App\Models\Reimbursement::latest()->take(10)->get();
        }

        // Count stats
        $pendingTRFCount = \App\Models\TravelRequest::where('status', 'pending_manager')->count();
        $pendingCRFCount = \App\Models\Reimbursement::where('status', 'pending_finance')->count();
        $approvedNotReimbursedCount = \App\Models\Reimbursement::whereIn('status', ['approved', 'verified'])
            ->where('reimbursement_status', 'not_reimbursed')
            ->count();
        $reimbursedCount = \App\Models\Reimbursement::where('reimbursement_status', 'reimbursed')->count();
        $rejectedCRFCount = \App\Models\Reimbursement::where('status', 'rejected')->count();

        return view('dashboard', compact(
            'travelRequests',
            'reimbursements',
            'pendingTRFCount',
            'pendingCRFCount',
            'approvedNotReimbursedCount',
            'reimbursedCount',
            'rejectedCRFCount'
        ));
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
        Route::post('/{reimbursement}/mark-reimbursed', [ReimbursementController::class, 'markReimbursed'])->name('mark-reimbursed');
        Route::delete('/attachments/{attachment}', [ReimbursementController::class, 'deleteAttachment'])->name('attachments.destroy');
    });

    // Excel Exports
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/trf', [ExcelExportController::class, 'exportTravelRequests'])->name('trf');
        Route::get('/crf', [ExcelExportController::class, 'exportReimbursements'])->name('crf');
    });
});
