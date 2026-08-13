<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTravelRequest;
use App\Models\TravelRequest;
use App\Models\TravelRequestDestination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TravelRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isRole('employee')) {
            $travelRequests = TravelRequest::where('user_id', $user->id)->latest()->get();
        } elseif ($user->isRole('manager')) {
            // Managers see all pending manager reviews, plus other states
            $travelRequests = TravelRequest::latest()->get();
        } else {
            $travelRequests = TravelRequest::latest()->get();
        }

        return view('travel-requests.index', compact('travelRequests'));
    }

    public function create()
    {
        return view('travel-requests.create');
    }

    public function store(StoreTravelRequest $request)
    {
        $user = Auth::user();

        // Systematically generate a unique request number matching XXX/WM-YBAR format
        $count = TravelRequest::count() + 1;
        $request_number = sprintf('%03d/WM-YBAR', $count);

        // Ensure uniqueness
        while (TravelRequest::where('request_number', $request_number)->exists()) {
            $count++;
            $request_number = sprintf('%03d/WM-YBAR', $count);
        }

        // Check if user clicked "Submit" or "Save Draft"
        $status = $request->input('action') === 'submit' ? 'pending_manager' : 'draft';
        $submitted_at = $status === 'pending_manager' ? now() : null;

        $travelRequest = TravelRequest::create([
            'user_id' => $user->id,
            'request_number' => $request_number,
            'category' => $request->category,
            'date' => $request->date,
            'company' => $request->company,
            'justification' => $request->justification,
            'benefit' => $request->benefit,
            'supporting_invitation' => $request->boolean('supporting_invitation'),
            'supporting_custom' => $request->boolean('supporting_custom'),
            'supporting_label_1' => $request->supporting_label_1,
            'supporting_label_2' => $request->supporting_label_2,
            'supporting_label_3' => $request->supporting_label_3,
            'supporting_label_4' => $request->supporting_label_4,
            'supporting_value_1' => $request->boolean('supporting_value_1'),
            'supporting_value_2' => $request->boolean('supporting_value_2'),
            'supporting_value_3' => $request->boolean('supporting_value_3'),
            'supporting_value_4' => $request->boolean('supporting_value_4'),
            'status' => $status,
            'submitted_at' => $submitted_at,
        ]);

        foreach ($request->destinations as $dest) {
            TravelRequestDestination::create([
                'travel_request_id' => $travelRequest->id,
                'destination' => $dest['destination'],
                'from' => $dest['from'],
                'to' => $dest['to'],
            ]);
        }

        $message = $status === 'pending_manager' ? 'Travel Request submitted successfully.' : 'Travel Request saved as draft.';
        return redirect()->route('travel-requests.show', $travelRequest)->with('success', $message);
    }

    public function show(TravelRequest $travelRequest)
    {
        // Check authorization policy
        if (Auth::user()->isRole('employee') && $travelRequest->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('travel-requests.show', compact('travelRequest'));
    }

    public function submit(TravelRequest $travelRequest)
    {
        if ($travelRequest->user_id !== Auth::id() || $travelRequest->status !== 'draft') {
            abort(403, 'Unauthorized action.');
        }

        $travelRequest->update([
            'status' => 'pending_manager',
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'Travel Request submitted successfully for approval.');
    }

    public function approve(Request $request, TravelRequest $travelRequest)
    {
        if (!Auth::user()->isRole('manager') && !Auth::user()->isRole('admin')) {
            abort(403, 'Only managers or admins can approve travel requests.');
        }

        if ($travelRequest->status !== 'pending_manager') {
            return back()->with('error', 'Travel request is not in pending manager status.');
        }

        $travelRequest->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Travel Request has been approved.');
    }

    public function reject(Request $request, TravelRequest $travelRequest)
    {
        if (!Auth::user()->isRole('manager') && !Auth::user()->isRole('admin')) {
            abort(403, 'Only managers or admins can reject travel requests.');
        }

        if ($travelRequest->status !== 'pending_manager') {
            return back()->with('error', 'Travel request is not in pending manager status.');
        }

        $travelRequest->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Travel Request has been rejected.');
    }
}
