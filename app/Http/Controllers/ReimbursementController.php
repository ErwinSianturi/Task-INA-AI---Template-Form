<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReimbursementRequest;
use App\Models\Reimbursement;
use App\Models\ReimbursementItem;
use App\Models\TravelRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReimbursementController extends Controller
{
    public function choice()
    {
        return view('reimbursements.choice');
    }

    public function index()
    {
        $user = Auth::user();
        if ($user->isRole('employee')) {
            $reimbursements = Reimbursement::where('user_id', $user->id)->latest()->get();
        } elseif ($user->isRole('finance')) {
            $reimbursements = Reimbursement::latest()->get();
        } else {
            $reimbursements = Reimbursement::latest()->get();
        }

        return view('reimbursements.index', compact('reimbursements'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $isNonTravel = $request->input('type') === 'non_travel';

        // Get approved travel requests belonging to user that do NOT have a reimbursement yet
        $approvedTravelRequests = [];
        if (!$isNonTravel) {
            $approvedTravelRequests = TravelRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereDoesntHave('reimbursement', function ($query) {
                    $query->whereIn('status', ['pending_finance', 'verified']);
                })
                ->get();
        }

        $selectedTR = null;
        if (!$isNonTravel && $request->has('travel_request_id')) {
            $selectedTR = TravelRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->find($request->travel_request_id);
        }

        return view('reimbursements.create', compact('approvedTravelRequests', 'selectedTR', 'isNonTravel'));
    }

    public function store(StoreReimbursementRequest $request)
    {
        $user = Auth::user();
        $isNonTravel = $request->reimbursement_type === 'non_travel';
        
        $travelRequest = null;
        if (!$isNonTravel) {
            $travelRequest = TravelRequest::findOrFail($request->travel_request_id);
            if ($travelRequest->user_id !== $user->id || $travelRequest->status !== 'approved') {
                abort(403, 'Unauthorized action.');
            }
        }

        // Calculate total amount from items
        $total = 0;
        foreach ($request->items as $item) {
            $total += floatval($item['amount']);
        }

        $status = $request->input('action') === 'submit' ? 'pending_finance' : 'draft';
        $submitted_at = $status === 'pending_finance' ? now() : null;

        $reimbursement = null;
        if (!$isNonTravel) {
            // Travel CRF - check for existing draft/rejected reimbursement
            $reimbursement = Reimbursement::where('travel_request_id', $travelRequest->id)
                ->whereIn('status', ['draft', 'rejected'])
                ->first();
        } else {
            // Non-Travel CRF - check for existing draft/rejected reimbursement by user for editing
            if ($request->has('reimbursement_id')) {
                $reimbursement = Reimbursement::where('user_id', $user->id)
                    ->where('id', $request->reimbursement_id)
                    ->whereIn('status', ['draft', 'rejected'])
                    ->first();
            }
        }

        if ($reimbursement) {
            // Delete old items
            $reimbursement->items()->delete();

            // Update
            $reimbursement->update([
                'category' => $request->category,
                'date' => $request->date,
                'company' => $request->company,
                'note' => $request->note,
                'bank' => $request->bank,
                'account_number' => $request->account_number,
                'transfer_to' => $request->transfer_to,
                'total' => $total,
                'status' => $status,
                'submitted_at' => $submitted_at,
            ]);
        } else {
            // Generate Request Number if Non-Travel
            if ($isNonTravel) {
                $count = Reimbursement::count() + TravelRequest::count() + 1;
                $request_number = sprintf('%03d/WM-YBAR', $count);
                while (Reimbursement::where('request_number', $request_number)->exists() || TravelRequest::where('request_number', $request_number)->exists()) {
                    $count++;
                    $request_number = sprintf('%03d/WM-YBAR', $count);
                }
            } else {
                $request_number = $travelRequest->request_number;
            }

            // Create new
            $reimbursement = Reimbursement::create([
                'user_id' => $user->id,
                'travel_request_id' => $travelRequest ? $travelRequest->id : null,
                'request_number' => $request_number,
                'reimbursement_type' => $request->reimbursement_type,
                'category' => $request->category,
                'date' => $request->date,
                'company' => $request->company,
                'note' => $request->note,
                'bank' => $request->bank,
                'account_number' => $request->account_number,
                'transfer_to' => $request->transfer_to,
                'total' => $total,
                'status' => $status,
                'submitted_at' => $submitted_at,
            ]);
        }

        // Create items
        foreach ($request->items as $item) {
            ReimbursementItem::create([
                'reimbursement_id' => $reimbursement->id,
                'date' => $item['date'],
                'details' => $item['details'],
                'amount' => $item['amount'],
            ]);
        }

        $message = $status === 'pending_finance' ? 'Cash Reimbursement submitted successfully.' : 'Cash Reimbursement saved as draft.';
        return redirect()->route('reimbursements.show', $reimbursement)->with('success', $message);
    }

    public function show(Reimbursement $reimbursement)
    {
        if (Auth::user()->isRole('employee') && $reimbursement->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('reimbursements.show', compact('reimbursement'));
    }

    public function submit(Reimbursement $reimbursement)
    {
        if ($reimbursement->user_id !== Auth::id() || !in_array($reimbursement->status, ['draft', 'rejected'])) {
            abort(403, 'Unauthorized action.');
        }

        $reimbursement->update([
            'status' => 'pending_finance',
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'Cash Reimbursement submitted successfully for verification.');
    }

    public function verify(Request $request, Reimbursement $reimbursement)
    {
        if (!Auth::user()->isRole('finance') && !Auth::user()->isRole('admin')) {
            abort(403, 'Only finance users or admins can verify reimbursements.');
        }

        if ($reimbursement->status !== 'pending_finance') {
            return back()->with('error', 'Reimbursement is not in pending finance status.');
        }

        $reimbursement->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Reimbursement has been verified successfully.');
    }

    public function reject(Request $request, Reimbursement $reimbursement)
    {
        if (!Auth::user()->isRole('finance') && !Auth::user()->isRole('admin')) {
            abort(403, 'Only finance users or admins can reject reimbursements.');
        }

        if ($reimbursement->status !== 'pending_finance') {
            return back()->with('error', 'Reimbursement is not in pending finance status.');
        }

        $reimbursement->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Reimbursement has been rejected.');
    }
}
