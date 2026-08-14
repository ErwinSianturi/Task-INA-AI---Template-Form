<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTravelRequest;
use App\Models\ApprovalHistory;
use App\Models\TravelRequest;
use App\Models\TravelRequestDestination;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelRequestController extends Controller
{
    /**
     * Check which approval roles the user can perform for this TRF.
     */
    protected function getAvailableApprovalRoles(User $user, TravelRequest $tr): array
    {
        $roles = [];
        $name = strtolower(trim($user->name));
        $email = strtolower(trim($user->email));

        $isBilly = ($name === 'billy gunawan' || $email === 'billy@example.com');
        $isApriliansyah = ($name === 'apriliansyah' || $email === 'apriliansyah@example.com');
        $isPantro = ($name === 'pantro pander' || $email === 'pantro@example.com');

        if ($user->isRole('admin')) {
            if (!$tr->category_approved_at) $roles[] = 'category';
            if (!$tr->manager_approved_at) $roles[] = 'manager';
            if (!$tr->pantro_approved_at) $roles[] = 'pantro';
            return $roles;
        }

        // Category Approver Slot
        if (!$tr->category_approved_at) {
            if ($tr->category === 'Technology' && $isBilly) {
                $roles[] = 'category';
            } elseif ($tr->category === 'Commercial' && $isApriliansyah) {
                $roles[] = 'category';
            } elseif ($tr->category === 'Others' && ($isBilly || $isApriliansyah)) {
                $roles[] = 'category';
            }
        }

        // Manager Slot (Role 'manager' or named Account Manager)
        if (!$tr->manager_approved_at && ($user->isRole('manager') || $email === 'manager@example.com' || str_contains($name, 'manager'))) {
            $roles[] = 'manager';
        }

        // Pantro Pander Slot
        if (!$tr->pantro_approved_at && $isPantro) {
            $roles[] = 'pantro';
        }

        return array_unique($roles);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = TravelRequest::with(['user', 'manager', 'categoryApprover', 'pantroUser', 'approvedByUser']);

        if ($user->isRole('employee')) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $travelRequests = $query->latest()->get();

        return view('travel-requests.index', compact('travelRequests'));
    }

    public function create()
    {
        return view('travel-requests.create');
    }

    public function store(StoreTravelRequest $request)
    {
        $user = Auth::user();

        $count = TravelRequest::count() + 1;
        $request_number = sprintf('%03d/WM-YBAR', $count);

        while (TravelRequest::where('request_number', $request_number)->exists()) {
            $count++;
            $request_number = sprintf('%03d/WM-YBAR', $count);
        }

        $status = $request->input('action') === 'submit' ? 'pending_manager' : 'draft';
        $submitted_at = $status === 'pending_manager' ? now() : null;

        $travelRequest = TravelRequest::create([
            'user_id' => $user->id,
            'request_number' => $request_number,
            'category' => $request->category,
            'date' => $request->date,
            'company' => 'PT Teknologi Cerdas Berdaulat Indonesia',
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

        ApprovalHistory::create([
            'approvable_type' => TravelRequest::class,
            'approvable_id' => $travelRequest->id,
            'user_id' => $user->id,
            'role' => $user->role,
            'action' => $status === 'pending_manager' ? 'submitted' : 'draft_saved',
            'comment' => $status === 'pending_manager' ? 'Submitted travel request' : 'Saved travel request draft',
        ]);

        $message = $status === 'pending_manager' ? 'Travel Request submitted successfully.' : 'Travel Request saved as draft.';
        return redirect()->route('travel-requests.show', $travelRequest)->with('success', $message);
    }

    public function show(TravelRequest $travelRequest)
    {
        if (Auth::user()->isRole('employee') && $travelRequest->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $travelRequest->load(['user', 'manager', 'categoryApprover', 'pantroUser', 'approvedByUser', 'destinations', 'reimbursement', 'approvalHistories.user']);
        $availableRoles = $this->getAvailableApprovalRoles(Auth::user(), $travelRequest);
        $canApprove = count($availableRoles) > 0 && $travelRequest->status === 'pending_manager';

        return view('travel-requests.show', compact('travelRequest', 'canApprove', 'availableRoles'));
    }

    public function submit(TravelRequest $travelRequest)
    {
        if ($travelRequest->user_id !== Auth::id() || !in_array($travelRequest->status, ['draft', 'rejected'])) {
            abort(403, 'Unauthorized action.');
        }

        $travelRequest->update([
            'status' => 'pending_manager',
            'submitted_at' => now(),
            'category_approver_id' => null,
            'category_approved_at' => null,
            'manager_approved_at' => null,
            'pantro_id' => null,
            'pantro_approved_at' => null,
            'approved_by_user_id' => null,
            'approved_at' => null,
            'signed_date' => null,
        ]);

        ApprovalHistory::create([
            'approvable_type' => TravelRequest::class,
            'approvable_id' => $travelRequest->id,
            'user_id' => Auth::id(),
            'role' => Auth::user()->role,
            'action' => 'submitted',
            'comment' => 'Submitted travel request for multi-role approval',
        ]);

        return back()->with('success', 'Travel Request submitted successfully for approval.');
    }

    public function approve(Request $request, TravelRequest $travelRequest)
    {
        $user = Auth::user();
        $availableRoles = $this->getAvailableApprovalRoles($user, $travelRequest);

        if (count($availableRoles) === 0) {
            abort(403, "Anda tidak memiliki hak akses approval untuk dokumen ini.");
        }

        if ($travelRequest->status !== 'pending_manager') {
            return back()->with('error', 'Travel request is not in pending approval status.');
        }

        $updateData = [];
        $now = now();
        $today = $now->toDateString();

        if (in_array('category', $availableRoles)) {
            $updateData['category_approver_id'] = $user->id;
            $updateData['category_approved_at'] = $now;
        }

        if (in_array('manager', $availableRoles)) {
            $updateData['manager_id'] = $user->id;
            $updateData['manager_approved_at'] = $now;
        }

        if (in_array('pantro', $availableRoles)) {
            $updateData['pantro_id'] = $user->id;
            $updateData['pantro_approved_at'] = $now;
        }

        $travelRequest->update($updateData);
        $travelRequest->refresh();

        $isCategoryDone = (bool)$travelRequest->category_approved_at;
        $isManagerDone = (bool)$travelRequest->manager_approved_at;
        $isPantroDone = (bool)$travelRequest->pantro_approved_at;

        if ($isCategoryDone && $isManagerDone && $isPantroDone) {
            $travelRequest->update([
                'status' => 'approved',
                'approved_at' => $now,
                'signed_date' => $today,
                'approved_by_user_id' => $user->id,
            ]);
            $msg = "Persetujuan akhir lengkap! Status Travel Request kini APPROVED.";
        } else {
            $pendingList = [];
            if (!$isCategoryDone) $pendingList[] = 'Category Approver';
            if (!$isManagerDone) $pendingList[] = 'Manager';
            if (!$isPantroDone) $pendingList[] = 'Pantro Pander';
            $msg = "Approval oleh {$user->name} berhasil dicatat. Menunggu persetujuan dari: " . implode(', ', $pendingList) . ".";
        }

        ApprovalHistory::create([
            'approvable_type' => TravelRequest::class,
            'approvable_id' => $travelRequest->id,
            'user_id' => $user->id,
            'role' => $user->role,
            'action' => 'approved',
            'comment' => $request->input('comment') ?? "Approved by {$user->name}",
            'signed_date' => $today,
        ]);

        return back()->with('success', $msg);
    }

    public function reject(Request $request, TravelRequest $travelRequest)
    {
        $user = Auth::user();
        $availableRoles = $this->getAvailableApprovalRoles($user, $travelRequest);

        if (count($availableRoles) === 0 && !$user->isRole('admin')) {
            abort(403, "Anda tidak memiliki hak akses untuk menolak permohonan ini.");
        }

        if ($travelRequest->status !== 'pending_manager') {
            return back()->with('error', 'Travel request is not in pending approval status.');
        }

        $request->validate([
            'comment' => 'required|string|max:1000',
        ], [
            'comment.required' => 'Alasan penolakan (reject reason) wajib diisi.',
        ]);

        $travelRequest->update([
            'status' => 'rejected',
            'manager_id' => $user->id,
            'approved_by_user_id' => $user->id,
            'manager_comment' => $request->input('comment'),
        ]);

        ApprovalHistory::create([
            'approvable_type' => TravelRequest::class,
            'approvable_id' => $travelRequest->id,
            'user_id' => $user->id,
            'role' => $user->role,
            'action' => 'rejected',
            'comment' => $request->input('comment'),
        ]);

        return back()->with('success', 'Travel Request has been rejected.');
    }
}
