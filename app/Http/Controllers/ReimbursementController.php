<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReimbursementRequest;
use App\Models\ApprovalHistory;
use App\Models\Reimbursement;
use App\Models\ReimbursementAttachment;
use App\Models\ReimbursementItem;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReimbursementController extends Controller
{
    /**
     * Check which approval roles the user can perform for this CRF.
     */
    protected function getAvailableApprovalRoles(User $user, Reimbursement $reimbursement): array
    {
        $roles = [];
        $name = strtolower(trim($user->name));
        $email = strtolower(trim($user->email));

        $isBilly = ($name === 'billy gunawan' || $email === 'billy@example.com');
        $isApriliansyah = ($name === 'apriliansyah' || $email === 'apriliansyah@example.com');
        $isPantro = ($name === 'pantro pander' || $email === 'pantro@example.com');
        $isTungsen = ($name === 'tung sen' || $email === 'tungsen@example.com');

        if ($user->isRole('admin')) {
            if (!$reimbursement->category_approved_at) $roles[] = 'category';
            if (!$reimbursement->manager_approved_at) $roles[] = 'manager';
            if (!$reimbursement->finance_approved_at) $roles[] = 'finance';
            if (!$reimbursement->pantro_approved_at) $roles[] = 'pantro';
            if (!$reimbursement->tungsen_approved_at) $roles[] = 'tungsen';
            return $roles;
        }

        // 1. Category Approver Slot
        if (!$reimbursement->category_approved_at) {
            if ($reimbursement->category === 'Technology' && $isBilly) {
                $roles[] = 'category';
            } elseif ($reimbursement->category === 'Commercial' && $isApriliansyah) {
                $roles[] = 'category';
            } elseif ($reimbursement->category === 'Others' && ($isBilly || $isApriliansyah)) {
                $roles[] = 'category';
            }
        }

        // 2. Manager Slot (Role 'manager' or named Account Manager)
        if (!$reimbursement->manager_approved_at && ($user->isRole('manager') || $email === 'manager@example.com' || str_contains($name, 'manager'))) {
            $roles[] = 'manager';
        }

        // 3. Finance Slot (Role 'finance' or named Account Finance)
        if (!$reimbursement->finance_approved_at && ($user->isRole('finance') || $email === 'finance@example.com' || str_contains($name, 'finance'))) {
            $roles[] = 'finance';
        }

        // 4. Pantro Pander Slot
        if (!$reimbursement->pantro_approved_at && $isPantro) {
            $roles[] = 'pantro';
        }

        // 5. Tung Sen Slot
        if (!$reimbursement->tungsen_approved_at && $isTungsen) {
            $roles[] = 'tungsen';
        }

        return array_unique($roles);
    }

    public function choice()
    {
        return view('reimbursements.choice');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Reimbursement::with(['user', 'finance', 'categoryApprover', 'pantroUser', 'tungsenUser', 'approvedByUser', 'travelRequest']);

        if ($user->isRole('employee')) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('reimbursement_status')) {
            $query->where('reimbursement_status', $request->reimbursement_status);
        }

        if ($request->filled('type')) {
            $query->where('reimbursement_type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $reimbursements = $query->latest()->get();

        return view('reimbursements.index', compact('reimbursements'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $isNonTravel = $request->input('type') === 'non_travel';

        $approvedTravelRequests = [];
        if (!$isNonTravel) {
            $approvedTravelRequests = TravelRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereDoesntHave('reimbursement', function ($query) {
                    $query->whereIn('status', ['pending_finance', 'verified', 'approved']);
                })
                ->get();
        }

        $selectedTR = null;
        if (!$isNonTravel && $request->has('travel_request_id')) {
            $selectedTR = TravelRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->find($request->travel_request_id);
        }

        $banks = config('banks', []);

        return view('reimbursements.create', compact('approvedTravelRequests', 'selectedTR', 'isNonTravel', 'banks'));
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

        $total = 0;
        foreach ($request->items as $item) {
            $total += floatval($item['amount']);
        }

        $status = $request->input('action') === 'submit' ? 'pending_finance' : 'draft';
        $submitted_at = $status === 'pending_finance' ? now() : null;

        $reimbursement = null;
        if (!$isNonTravel) {
            $reimbursement = Reimbursement::where('travel_request_id', $travelRequest->id)
                ->whereIn('status', ['draft', 'rejected'])
                ->first();
        } else {
            if ($request->has('reimbursement_id')) {
                $reimbursement = Reimbursement::where('user_id', $user->id)
                    ->where('id', $request->reimbursement_id)
                    ->whereIn('status', ['draft', 'rejected'])
                    ->first();
            }
        }

        if ($reimbursement) {
            $reimbursement->items()->delete();
            $reimbursement->update([
                'category' => $request->category,
                'date' => $request->date,
                'company' => 'PT Teknologi Cerdas Berdaulat Indonesia',
                'note' => $request->note,
                'bank' => $request->bank,
                'account_number' => $request->account_number,
                'transfer_to' => $request->transfer_to,
                'total' => $total,
                'status' => $status,
                'submitted_at' => $submitted_at,
            ]);
        } else {
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

            $reimbursement = Reimbursement::create([
                'user_id' => $user->id,
                'travel_request_id' => $travelRequest ? $travelRequest->id : null,
                'request_number' => $request_number,
                'reimbursement_type' => $request->reimbursement_type,
                'category' => $request->category,
                'date' => $request->date,
                'company' => 'PT Teknologi Cerdas Berdaulat Indonesia',
                'note' => $request->note,
                'bank' => $request->bank,
                'account_number' => $request->account_number,
                'transfer_to' => $request->transfer_to,
                'total' => $total,
                'status' => $status,
                'reimbursement_status' => 'not_reimbursed',
                'submitted_at' => $submitted_at,
            ]);
        }

        foreach ($request->items as $item) {
            ReimbursementItem::create([
                'reimbursement_id' => $reimbursement->id,
                'date' => $item['date'],
                'details' => $item['details'],
                'amount' => $item['amount'],
            ]);
        }

        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            $receiptDates = $request->input('receipt_dates', []);

            foreach ($files as $index => $file) {
                $path = $file->store('receipts', 'public');
                $receiptDate = $receiptDates[$index] ?? now()->toDateString();

                ReimbursementAttachment::create([
                    'reimbursement_id' => $reimbursement->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'receipt_date' => $receiptDate,
                ]);
            }
        }

        ApprovalHistory::create([
            'approvable_type' => Reimbursement::class,
            'approvable_id' => $reimbursement->id,
            'user_id' => $user->id,
            'role' => $user->role,
            'action' => $status === 'pending_finance' ? 'submitted' : 'draft_saved',
            'comment' => $status === 'pending_finance' ? 'Submitted cash reimbursement' : 'Saved cash reimbursement draft',
        ]);

        $message = $status === 'pending_finance' ? 'Cash Reimbursement submitted successfully.' : 'Cash Reimbursement saved as draft.';
        return redirect()->route('reimbursements.show', $reimbursement)->with('success', $message);
    }

    public function show(Reimbursement $reimbursement)
    {
        if (Auth::user()->isRole('employee') && $reimbursement->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $reimbursement->load(['user', 'finance', 'managerUser', 'categoryApprover', 'pantroUser', 'tungsenUser', 'approvedByUser', 'reimbursedByUser', 'items', 'attachments', 'travelRequest', 'approvalHistories.user']);
        $availableRoles = $this->getAvailableApprovalRoles(Auth::user(), $reimbursement);
        $canApprove = count($availableRoles) > 0 && $reimbursement->status === 'pending_finance';

        return view('reimbursements.show', compact('reimbursement', 'canApprove', 'availableRoles'));
    }

    public function submit(Reimbursement $reimbursement)
    {
        if ($reimbursement->user_id !== Auth::id() || !in_array($reimbursement->status, ['draft', 'rejected'])) {
            abort(403, 'Unauthorized action.');
        }

        if ($reimbursement->attachments()->count() === 0) {
            return back()->with('error', 'Setidaknya satu Invoice / Receipt (bukti pembayaran) wajib di-upload sebelum CRF disubmit.');
        }

        $reimbursement->update([
            'status' => 'pending_finance',
            'submitted_at' => now(),
            'category_approver_id' => null,
            'category_approved_at' => null,
            'manager_id' => null,
            'manager_approved_at' => null,
            'finance_id' => null,
            'finance_approved_at' => null,
            'pantro_id' => null,
            'pantro_approved_at' => null,
            'tungsen_id' => null,
            'tungsen_approved_at' => null,
            'approved_by_user_id' => null,
            'verified_at' => null,
            'signed_date' => null,
        ]);

        ApprovalHistory::create([
            'approvable_type' => Reimbursement::class,
            'approvable_id' => $reimbursement->id,
            'user_id' => Auth::id(),
            'role' => Auth::user()->role,
            'action' => 'submitted',
            'comment' => 'Submitted cash reimbursement for 5-role verification',
        ]);

        return back()->with('success', 'Cash Reimbursement submitted successfully for verification.');
    }

    public function verify(Request $request, Reimbursement $reimbursement)
    {
        $user = Auth::user();
        $availableRoles = $this->getAvailableApprovalRoles($user, $reimbursement);

        if (count($availableRoles) === 0) {
            abort(403, "Anda tidak memiliki hak akses approval untuk dokumen ini.");
        }

        if ($reimbursement->status !== 'pending_finance') {
            return back()->with('error', 'Reimbursement is not in pending verification status.');
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

        if (in_array('finance', $availableRoles)) {
            $updateData['finance_id'] = $user->id;
            $updateData['finance_approved_at'] = $now;
        }

        if (in_array('pantro', $availableRoles)) {
            $updateData['pantro_id'] = $user->id;
            $updateData['pantro_approved_at'] = $now;
        }

        if (in_array('tungsen', $availableRoles)) {
            $updateData['tungsen_id'] = $user->id;
            $updateData['tungsen_approved_at'] = $now;
        }

        $reimbursement->update($updateData);
        $reimbursement->refresh();

        $isCategoryDone = (bool)$reimbursement->category_approved_at;
        $isManagerDone = (bool)$reimbursement->manager_approved_at;
        $isFinanceDone = (bool)$reimbursement->finance_approved_at;
        $isPantroDone = (bool)$reimbursement->pantro_approved_at;
        $isTungsenDone = (bool)$reimbursement->tungsen_approved_at;

        if ($isCategoryDone && $isManagerDone && $isFinanceDone && $isPantroDone && $isTungsenDone) {
            $reimbursement->update([
                'status' => 'approved',
                'verified_at' => $now,
                'signed_date' => $today,
                'approved_by_user_id' => $user->id,
                'reimbursement_status' => 'not_reimbursed',
            ]);
            $msg = "Persetujuan 5 pihak lengkap! Status Reimbursement kini APPROVED.";
        } else {
            $pendingList = [];
            if (!$isCategoryDone) $pendingList[] = 'Category Approver';
            if (!$isManagerDone) $pendingList[] = 'Manager';
            if (!$isFinanceDone) $pendingList[] = 'Finance';
            if (!$isPantroDone) $pendingList[] = 'Pantro Pander';
            if (!$isTungsenDone) $pendingList[] = 'Tung Sen';

            $msg = "Approval oleh {$user->name} berhasil dicatat. Menunggu persetujuan dari: " . implode(', ', $pendingList) . ".";
        }

        ApprovalHistory::create([
            'approvable_type' => Reimbursement::class,
            'approvable_id' => $reimbursement->id,
            'user_id' => $user->id,
            'role' => $user->role,
            'action' => 'approved',
            'comment' => $request->input('comment') ?? "Approved by {$user->name}",
            'signed_date' => $today,
        ]);

        return back()->with('success', $msg);
    }

    public function reject(Request $request, Reimbursement $reimbursement)
    {
        $user = Auth::user();
        $availableRoles = $this->getAvailableApprovalRoles($user, $reimbursement);

        if (count($availableRoles) === 0 && !$user->isRole('admin')) {
            abort(403, "Anda tidak memiliki hak akses untuk menolak permohonan ini.");
        }

        if ($reimbursement->status !== 'pending_finance') {
            return back()->with('error', 'Reimbursement is not in pending verification status.');
        }

        $request->validate([
            'comment' => 'required|string|max:1000',
        ], [
            'comment.required' => 'Alasan penolakan (reject reason) wajib diisi.',
        ]);

        $reimbursement->update([
            'status' => 'rejected',
            'finance_id' => $user->id,
            'approved_by_user_id' => $user->id,
            'finance_comment' => $request->input('comment'),
        ]);

        ApprovalHistory::create([
            'approvable_type' => Reimbursement::class,
            'approvable_id' => $reimbursement->id,
            'user_id' => $user->id,
            'role' => $user->role,
            'action' => 'rejected',
            'comment' => $request->input('comment'),
        ]);

        return back()->with('success', 'Reimbursement has been rejected.');
    }

    public function markReimbursed(Request $request, Reimbursement $reimbursement)
    {
        if (!Auth::user()->isRole('finance') && !Auth::user()->isRole('admin') && !Auth::user()->isPengawas()) {
            abort(403, 'Only finance users, pengawas or admins can mark reimbursement as paid/reimbursed.');
        }

        if (!in_array($reimbursement->status, ['approved', 'verified'])) {
            return back()->with('error', 'Reimbursement must be approved before marking as reimbursed.');
        }

        if ($reimbursement->reimbursement_status === 'reimbursed') {
            return back()->with('error', 'Reimbursement status is already marked as Reimbursed.');
        }

        $request->validate([
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'transaction_reference' => 'nullable|string|max:255',
        ]);

        $reimbursement->update([
            'reimbursement_status' => 'reimbursed',
            'reimbursed_at' => now(),
            'reimbursed_by' => Auth::id(),
            'paid_amount' => $request->input('paid_amount', $reimbursement->total),
            'payment_method' => $request->input('payment_method', 'Bank Transfer'),
            'transaction_reference' => $request->input('transaction_reference'),
        ]);

        ApprovalHistory::create([
            'approvable_type' => Reimbursement::class,
            'approvable_id' => $reimbursement->id,
            'user_id' => Auth::id(),
            'role' => Auth::user()->role,
            'action' => 'reimbursed',
            'comment' => 'Marked as Reimbursed. Method: ' . ($request->input('payment_method') ?? 'Bank Transfer') . ', Ref: ' . ($request->input('transaction_reference') ?? '-'),
        ]);

        return back()->with('success', 'Reimbursement status successfully updated to Reimbursed.');
    }

    public function deleteAttachment(ReimbursementAttachment $attachment)
    {
        $reimbursement = $attachment->reimbursement;
        if ($reimbursement->user_id !== Auth::id() || !in_array($reimbursement->status, ['draft', 'rejected'])) {
            abort(403, 'Unauthorized action.');
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted successfully.');
    }
}
