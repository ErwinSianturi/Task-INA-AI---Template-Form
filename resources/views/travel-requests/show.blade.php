@extends('layouts.app')

@section('title', 'Travel Request Document - ' . $travelRequest->request_number)

@section('content')
<div class="document-actions-bar" style="margin-left: auto; margin-right: auto;">
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('travel-requests.index') }}" class="btn btn-secondary">&larr; Back</a>
        <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Form</button>
    </div>
    
    <div class="btn-group">
        <!-- Employee Submit / Resubmit Action -->
        @if(in_array($travelRequest->status, ['draft', 'rejected']) && Auth::id() === $travelRequest->user_id)
            <form action="{{ route('travel-requests.submit', $travelRequest) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">
                    {{ $travelRequest->status === 'rejected' ? 'Resubmit for Approval' : 'Submit for Approval' }}
                </button>
            </form>
        @endif

        <!-- Category / Manager / Pantro Approver Actions -->
        @if($travelRequest->status === 'pending_manager' && ($canApprove ?? false))
            <form action="{{ route('travel-requests.approve', $travelRequest) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-success">
                    ✓ Approve TRF as {{ Auth::user()->name }}
                </button>
            </form>
            
            <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectModal').style.display='flex'">Reject TRF</button>
        @endif

        <!-- Employee Link to Create CRF (if approved and no CRF exists yet) -->
        @if($travelRequest->status === 'approved' && !$travelRequest->reimbursement && (Auth::user()->isRole('employee') || Auth::user()->isRole('admin')))
            <a href="{{ route('reimbursements.create', ['travel_request_id' => $travelRequest->id]) }}" class="btn btn-accent">
                Create Cash Reimbursement (CRF)
            </a>
        @endif
    </div>
</div>

<!-- Multi-Role Approval Requirement Tracker Banner for Pending Status -->
@if($travelRequest->status === 'pending_manager')
    <div class="alert alert-success" style="max-width: 850px; margin: 0 auto 1.5rem auto; background-color: #EFF6FF; color: #1E40AF; border-color: #BFDBFE;">
        <div style="width: 100%;">
            <div style="font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                <span>📋 Status Approval 3 Pihak (TRF - Category: {{ $travelRequest->category }})</span>
                <span style="font-size: 0.8rem; background: #DBEAFE; color: #1E40AF; padding: 0.2rem 0.6rem; border-radius: 4px;">Perlu Persetujuan Penuh</span>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem; font-size: 0.85rem; margin-top: 0.5rem; background: white; padding: 0.75rem; border-radius: 6px; border: 1px solid #BFDBFE;">
                <!-- 1. Category Approver -->
                <div>
                    <span style="color: #64748B;">Category ({{ $travelRequest->category === 'Technology' ? 'Billy' : ($travelRequest->category === 'Commercial' ? 'Apriliansyah' : 'Billy/April') }}):</span><br>
                    @if($travelRequest->category_approved_at)
                        <strong style="color: #059669;">✓ Approved ({{ $travelRequest->categoryApprover->name ?? 'Approver' }})</strong>
                    @else
                        <strong style="color: #D97706;">⏳ Pending Approval</strong>
                    @endif
                </div>

                <!-- 2. Manager -->
                <div>
                    <span style="color: #64748B;">Manager:</span><br>
                    @if($travelRequest->manager_approved_at)
                        <strong style="color: #059669;">✓ Approved ({{ $travelRequest->manager->name ?? 'Manager' }})</strong>
                    @else
                        <strong style="color: #D97706;">⏳ Pending Approval</strong>
                    @endif
                </div>

                <!-- 3. Pantro Pander -->
                <div>
                    <span style="color: #64748B;">Pantro Pander:</span><br>
                    @if($travelRequest->pantro_approved_at)
                        <strong style="color: #059669;">✓ Approved ({{ $travelRequest->pantroUser->name ?? 'Pantro Pander' }})</strong>
                    @else
                        <strong style="color: #D97706;">⏳ Pending Approval</strong>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@if($travelRequest->status === 'rejected')
    <div class="alert alert-danger" style="max-width: 850px; margin: 0 auto 1.5rem auto; border-left: 4px solid var(--danger);">
        <div>
            <strong>🚫 Travel Request Rejected</strong>
            <p style="margin-top: 0.25rem;">Alasan Penolakan: <em>{{ $travelRequest->manager_comment ?? 'Tidak ada catatan.' }}</em></p>
        </div>
    </div>
@endif

<div class="paper-container">
    <div class="paper-sheet">
        <!-- Status Watermark (For non-printed screens) -->
        <div style="position: absolute; top: 1.5rem; left: 3rem; border: 3px double {{ $travelRequest->status === 'approved' ? '#2ECC71' : ($travelRequest->status === 'rejected' ? '#E74C3C' : '#F1C40F') }}; color: {{ $travelRequest->status === 'approved' ? '#2ECC71' : ($travelRequest->status === 'rejected' ? '#E74C3C' : '#F1C40F') }}; padding: 0.25rem 1rem; font-family: var(--font-heading); font-size: 1.1rem; font-weight: 800; text-transform: uppercase; transform: rotate(-5deg); z-index: 10;">
            Status: {{ str_replace('_', ' ', $travelRequest->status) }}
        </div>

        <!-- Header Block -->
        <div class="form-header-block">
            Travel Request Form
        </div>

        <!-- Fields Grid -->
        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <!-- Left Header Column -->
            <div>
                <div class="form-group-custom" style="margin-bottom: 1rem;">
                    <span class="form-label-custom">Request No:</span>
                    <div class="form-value-box flex-1">
                        {{ $travelRequest->request_number }}
                    </div>
                </div>
                <div class="form-group-custom">
                    <span class="form-label-custom">Date:</span>
                    <div class="form-value-box flex-1">
                        {{ $travelRequest->date->format('d F Y') }}
                    </div>
                </div>
            </div>

            <!-- Right Header Column -->
            <div>
                <div class="form-group-custom" style="margin-bottom: 1rem;">
                    <span class="form-label-custom">Category:</span>
                    <div class="form-value-box-yellow flex-1" style="font-weight: bold;">
                        {{ $travelRequest->category }}
                    </div>
                </div>
                <div class="form-group-custom" style="align-items: flex-start;">
                    <span class="form-label-custom" style="margin-top: 0.4rem;">Company:</span>
                    <div class="form-value-box flex-1" style="min-height: 45px; line-height: 1.3;">
                        {{ $travelRequest->company }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Destination Table Section -->
        <h3 style="font-weight: 700; font-size: 0.95rem; text-transform: uppercase; margin-top: 1.5rem;">Destination Table</h3>
        <table class="paper-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Destination</th>
                    <th style="width: 25%;">From</th>
                    <th style="width: 25%;">To</th>
                </tr>
            </thead>
            <tbody>
                @foreach($travelRequest->destinations as $dest)
                    <tr class="dashed-row">
                        <td>{{ $dest->destination }}</td>
                        <td>{{ $dest->from }}</td>
                        <td>{{ $dest->to }}</td>
                    </tr>
                @endforeach
                @for($i = count($travelRequest->destinations); $i < 6; $i++)
                    <tr class="dashed-row">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- Justification -->
        <div class="paper-textarea-section">
            <label class="paper-textarea-label">Justification</label>
            <div class="paper-textarea-box" style="min-height: 80px; white-space: pre-wrap;">{{ $travelRequest->justification }}</div>
        </div>

        <!-- Benefit -->
        <div class="paper-textarea-section">
            <label class="paper-textarea-label">Benefit</label>
            <div class="paper-textarea-box" style="min-height: 80px; white-space: pre-wrap;">{{ $travelRequest->benefit }}</div>
        </div>

        <!-- Supporting Data Checkbox List -->
        <div class="supporting-section">
            <div class="supporting-title">Supporting Datas <em>(check if applicable)</em>:</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 2rem;">
                <div class="supporting-row">
                    <div style="flex: 1; border-bottom: 1px dotted #000000; font-family: var(--font-main); font-size: 0.95rem; padding-bottom: 2px;">
                        {{ $travelRequest->supporting_label_1 ?? 'Invitation' }}
                    </div>
                    <div class="checkbox-box" style="margin-left: 0.5rem;">
                        {!! $travelRequest->supporting_value_1 ? '✓' : '&nbsp;' !!}
                    </div>
                </div>

                <div class="supporting-row">
                    <div style="flex: 1; border-bottom: 1px dotted #000000; font-family: var(--font-main); font-size: 0.95rem; padding-bottom: 2px;">
                        {{ $travelRequest->supporting_label_3 ?? '' }}
                    </div>
                    <div class="checkbox-box" style="margin-left: 0.5rem;">
                        {!! $travelRequest->supporting_value_3 ? '✓' : '&nbsp;' !!}
                    </div>
                </div>

                <div class="supporting-row">
                    <div style="flex: 1; border-bottom: 1px dotted #000000; font-family: var(--font-main); font-size: 0.95rem; padding-bottom: 2px;">
                        {{ $travelRequest->supporting_label_2 ?? 'Travel Invitation Letter' }}
                    </div>
                    <div class="checkbox-box" style="margin-left: 0.5rem;">
                        {!! $travelRequest->supporting_value_2 ? '✓' : '&nbsp;' !!}
                    </div>
                </div>

                <div class="supporting-row">
                    <div style="flex: 1; border-bottom: 1px dotted #000000; font-family: var(--font-main); font-size: 0.95rem; padding-bottom: 2px;">
                        {{ $travelRequest->supporting_label_4 ?? '' }}
                    </div>
                    <div class="checkbox-box" style="margin-left: 0.5rem;">
                        {!! $travelRequest->supporting_value_4 ? '✓' : '&nbsp;' !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Signature Block Grid Layout (Exact 3 Columns x 2 Rows = 6 Boxes matching Gambar 2) -->
        <div class="signature-grid-3">
            <!-- Column 1 (Left Column): Requested by & Acknowledged by -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <!-- Box 1: Requested by -->
                <div class="signature-card">
                    <div class="signature-title">Requested by</div>
                    <div class="signature-body" style="font-family: var(--font-paper); font-weight: bold;">
                        {{ $travelRequest->user->name }}
                    </div>
                    <div class="signature-name-field">
                        {{ $travelRequest->user->name }}
                    </div>
                    <div class="signature-date">
                        Signed Date: {{ $travelRequest->submitted_at ? $travelRequest->submitted_at->format('d-M-Y') : $travelRequest->created_at->format('d-M-Y') }}
                    </div>
                </div>

                <!-- Box 2: Acknowledged by (Manager) -->
                <div class="signature-card">
                    <div class="signature-title">Acknowledged by</div>
                    <div class="signature-body" style="font-family: var(--font-paper); text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        @if($travelRequest->manager_approved_at)
                            <div style="border: 2px dashed #059669; padding: 0.2rem 0.5rem; border-radius: 4px; background-color: #ECFDF5; color: #065F46;">
                                <div style="font-size: 0.6rem; font-weight: bold;">APPROVED MANAGER</div>
                                <div style="font-family: 'Courier Prime', monospace; font-size: 0.95rem; font-weight: bold; color: #1A2A3A;">
                                    ✍️ {{ $travelRequest->manager->name ?? 'Manager' }}
                                </div>
                            </div>
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="signature-name-field">
                        {{ $travelRequest->manager->name ?? '' }}
                    </div>
                    <div class="signature-date">
                        Signed Date: {{ $travelRequest->manager_approved_at ? $travelRequest->manager_approved_at->format('d-M-Y') : '' }}
                    </div>
                </div>
            </div>

            <!-- Column 2 (Middle Column): Approved by (Category Approver) & Checked by (FA Manager) -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <!-- Box 3: Approved by (Billy Gunawan / Apriliansyah) -->
                <div class="signature-card">
                    <div class="signature-title">Approved by</div>
                    <div class="signature-body" style="font-family: var(--font-paper); text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        @if($travelRequest->category_approved_at)
                            <div style="border: 2px dashed #059669; padding: 0.2rem 0.5rem; border-radius: 4px; background-color: #ECFDF5; color: #065F46;">
                                <div style="font-size: 0.6rem; font-weight: bold; text-transform: uppercase;">CATEGORY APPROVED</div>
                                <div style="font-family: 'Courier Prime', monospace; font-size: 0.95rem; font-weight: bold; color: #1A2A3A;">
                                    ✍️ {{ $travelRequest->categoryApprover->name ?? 'Billy Gunawan' }}
                                </div>
                            </div>
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="signature-name-field" style="font-weight: bold;">
                        {{ $travelRequest->categoryApprover->name ?? ($travelRequest->category === 'Technology' ? 'Billy Gunawan' : ($travelRequest->category === 'Commercial' ? 'Apriliansyah' : 'Billy Gunawan OR Apriliansyah')) }}
                    </div>
                    <div class="signature-date">
                        Signed Date: {{ $travelRequest->category_approved_at ? $travelRequest->category_approved_at->format('d-M-Y') : '' }}
                    </div>
                </div>

                <!-- Box 4: Checked by (FA Manager) -->
                <div class="signature-card">
                    <div class="signature-title">Checked by</div>
                    <div class="signature-body">
                        &nbsp;
                    </div>
                    <div class="signature-name-field">
                        FA Manager
                    </div>
                    <div class="signature-date">
                        Signed Date:
                    </div>
                </div>
            </div>

            <!-- Column 3 (Right Column): Approved by (Pantro Pander) & Checked by (Tung Sen) -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <!-- Box 5: Approved by (Pantro Pander) -->
                <div class="signature-card">
                    <div class="signature-title">Approved by</div>
                    <div class="signature-body" style="font-family: var(--font-paper); text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        @if($travelRequest->pantro_approved_at)
                            <div style="border: 2px dashed #059669; padding: 0.2rem 0.5rem; border-radius: 4px; background-color: #ECFDF5; color: #065F46;">
                                <div style="font-size: 0.6rem; font-weight: bold;">APPROVED PENGAWAS</div>
                                <div style="font-family: 'Courier Prime', monospace; font-size: 0.95rem; font-weight: bold; color: #1A2A3A;">
                                    ✍️ Pantro Pander
                                </div>
                            </div>
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="signature-name-field">
                        Pantro Pander
                    </div>
                    <div class="signature-date">
                        Signed Date: {{ $travelRequest->pantro_approved_at ? $travelRequest->pantro_approved_at->format('d-M-Y') : '' }}
                    </div>
                </div>

                <!-- Box 6: Checked by (Tung Sen) -->
                <div class="signature-card">
                    <div class="signature-title">Checked by</div>
                    <div class="signature-body">
                        &nbsp;
                    </div>
                    <div class="signature-name-field">
                        Tung Sen
                    </div>
                    <div class="signature-date">
                        Signed Date:
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Approver Reject Modal -->
@if($travelRequest->status === 'pending_manager' && ($canApprove ?? false))
<div id="rejectModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 8px; width: 100%; max-width: 450px; padding: 1.5rem; box-shadow: var(--shadow-lg);">
        <h3 style="margin-bottom: 1rem; color: var(--danger);">Reject Travel Request</h3>
        <form action="{{ route('travel-requests.reject', $travelRequest) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.9rem;">Alasan Penolakan (Reject Reason) <span style="color:red">*</span>:</label>
                <textarea name="comment" rows="4" required class="input-control" placeholder="Tuliskan alasan penolakan..."></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('rejectModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-danger">Ya, Reject TRF</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
