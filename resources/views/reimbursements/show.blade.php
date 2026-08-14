@extends('layouts.app')

@section('title', 'Cash Reimbursement Document - ' . $reimbursement->request_number)

@section('content')
<div class="document-actions-bar" style="margin-left: auto; margin-right: auto;">
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('reimbursements.index') }}" class="btn btn-secondary">&larr; Back</a>
        <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Form</button>
    </div>
    
    <div class="btn-group">
        <!-- Employee Submit / Resubmit Action -->
        @if(in_array($reimbursement->status, ['draft', 'rejected']) && Auth::id() === $reimbursement->user_id)
            <form action="{{ route('reimbursements.submit', $reimbursement) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">
                    {{ $reimbursement->status === 'rejected' ? 'Resubmit for Verification' : 'Submit for Verification' }}
                </button>
            </form>
        @endif

        <!-- Multi-Role Verification / Approval Actions -->
        @if($reimbursement->status === 'pending_finance' && ($canApprove ?? false))
            <form action="{{ route('reimbursements.verify', $reimbursement) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-success">
                    ✓ Approve CRF as {{ Auth::user()->name }}
                </button>
            </form>
            <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectFinanceModal').style.display='flex'">Reject</button>
        @endif

        <!-- Finance / Admin Mark as Reimbursed Action -->
        @if(in_array($reimbursement->status, ['approved', 'verified']) && $reimbursement->reimbursement_status !== 'reimbursed' && (Auth::user()->isRole('finance') || Auth::user()->isRole('admin') || Auth::user()->isPengawas()))
            <button type="button" class="btn btn-accent" onclick="document.getElementById('reimburseModal').style.display='flex'">
                💵 Mark as Reimbursed
            </button>
        @endif
    </div>
</div>

<!-- Multi-Role Approval Requirement Tracker Banner for Pending Status -->
@if($reimbursement->status === 'pending_finance')
    <div class="alert alert-success" style="max-width: 850px; margin: 0 auto 1.5rem auto; background-color: #EFF6FF; color: #1E40AF; border-color: #BFDBFE;">
        <div style="width: 100%;">
            <div style="font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                <span>📋 Status Approval 5 Pihak (CRF - Category: {{ $reimbursement->category }})</span>
                <span style="font-size: 0.8rem; background: #DBEAFE; color: #1E40AF; padding: 0.2rem 0.6rem; border-radius: 4px;">Perlu Semua Persetujuan</span>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.5rem; font-size: 0.85rem; margin-top: 0.5rem; background: white; padding: 0.75rem; border-radius: 6px; border: 1px solid #BFDBFE;">
                <!-- 1. Category Approver -->
                <div>
                    <span style="color: #64748B;">Category ({{ $reimbursement->category === 'Technology' ? 'Billy' : ($reimbursement->category === 'Commercial' ? 'Apriliansyah' : 'Billy/April') }}):</span><br>
                    @if($reimbursement->category_approved_at)
                        <strong style="color: #059669;">✓ Approved ({{ $reimbursement->categoryApprover->name ?? 'Approver' }})</strong>
                    @else
                        <strong style="color: #D97706;">⏳ Pending Approval</strong>
                    @endif
                </div>

                <!-- 2. Manager -->
                <div>
                    <span style="color: #64748B;">Manager:</span><br>
                    @if($reimbursement->manager_approved_at)
                        <strong style="color: #059669;">✓ Approved ({{ $reimbursement->managerUser->name ?? 'Manager' }})</strong>
                    @else
                        <strong style="color: #D97706;">⏳ Pending Approval</strong>
                    @endif
                </div>

                <!-- 3. Finance -->
                <div>
                    <span style="color: #64748B;">Finance:</span><br>
                    @if($reimbursement->finance_approved_at)
                        <strong style="color: #059669;">✓ Approved ({{ $reimbursement->finance->name ?? 'Finance' }})</strong>
                    @else
                        <strong style="color: #D97706;">⏳ Pending Approval</strong>
                    @endif
                </div>

                <!-- 4. Pantro Pander -->
                <div>
                    <span style="color: #64748B;">Pantro Pander:</span><br>
                    @if($reimbursement->pantro_approved_at)
                        <strong style="color: #059669;">✓ Approved ({{ $reimbursement->pantroUser->name ?? 'Pantro Pander' }})</strong>
                    @else
                        <strong style="color: #D97706;">⏳ Pending Approval</strong>
                    @endif
                </div>

                <!-- 5. Tung Sen -->
                <div>
                    <span style="color: #64748B;">Tung Sen:</span><br>
                    @if($reimbursement->tungsen_approved_at)
                        <strong style="color: #059669;">✓ Approved ({{ $reimbursement->tungsenUser->name ?? 'Tung Sen' }})</strong>
                    @else
                        <strong style="color: #D97706;">⏳ Pending Approval</strong>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@if($reimbursement->status === 'rejected')
    <div class="alert alert-danger" style="max-width: 850px; margin: 0 auto 1.5rem auto; border-left: 4px solid var(--danger);">
        <div>
            <strong>🚫 Reimbursement Rejected</strong>
            <p style="margin-top: 0.25rem;">Alasan Penolakan: <em>{{ $reimbursement->finance_comment ?? 'Tidak ada catatan.' }}</em></p>
        </div>
    </div>
@endif

<div class="paper-container">
    <div class="paper-sheet">
        <!-- Status Watermark -->
        <div style="position: absolute; top: 1.5rem; left: 3rem; display: flex; flex-direction: column; gap: 0.4rem; z-index: 10;">
            <div style="border: 3px double {{ in_array($reimbursement->status, ['approved', 'verified']) ? '#2ECC71' : ($reimbursement->status === 'rejected' ? '#E74C3C' : '#F1C40F') }}; color: {{ in_array($reimbursement->status, ['approved', 'verified']) ? '#2ECC71' : ($reimbursement->status === 'rejected' ? '#E74C3C' : '#F1C40F') }}; padding: 0.2rem 0.8rem; font-family: var(--font-heading); font-size: 0.95rem; font-weight: 800; text-transform: uppercase; transform: rotate(-3deg);">
                Approval: {{ in_array($reimbursement->status, ['approved', 'verified']) ? 'APPROVED' : str_replace('_', ' ', $reimbursement->status) }}
            </div>
            
            @if(in_array($reimbursement->status, ['approved', 'verified']))
                <div style="border: 3px double {{ $reimbursement->reimbursement_status === 'reimbursed' ? '#2563EB' : '#64748B' }}; color: {{ $reimbursement->reimbursement_status === 'reimbursed' ? '#2563EB' : '#64748B' }}; padding: 0.2rem 0.8rem; font-family: var(--font-heading); font-size: 0.85rem; font-weight: 800; text-transform: uppercase; transform: rotate(-3deg);">
                    Payment: {{ $reimbursement->reimbursement_status === 'reimbursed' ? 'REIMBURSED' : 'NOT REIMBURSED' }}
                </div>
            @endif
        </div>

        <!-- Header Block -->
        <div class="form-header-block">
            Cash Reimbursement Form
        </div>

        <!-- Fields Grid -->
        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <!-- Left Header Column -->
            <div>
                <div class="form-group-custom" style="margin-bottom: 1rem;">
                    <span class="form-label-custom">Request No:</span>
                    <div class="form-value-box flex-1" style="font-weight: bold;">
                        {{ $reimbursement->request_number }}
                    </div>
                </div>
                <div class="form-group-custom">
                    <span class="form-label-custom">Date:</span>
                    <div class="form-value-box flex-1">
                        {{ $reimbursement->date->format('d F Y') }}
                    </div>
                </div>
            </div>

            <!-- Right Header Column -->
            <div>
                <div class="form-group-custom" style="margin-bottom: 1rem;">
                    <span class="form-label-custom">Category:</span>
                    <div class="form-value-box-yellow flex-1" style="font-weight: bold;">
                        {{ $reimbursement->category }}
                    </div>
                </div>
                <div class="form-group-custom" style="align-items: flex-start;">
                    <span class="form-label-custom" style="margin-top: 0.4rem;">Company:</span>
                    <div class="form-value-box flex-1" style="min-height: 45px; line-height: 1.3;">
                        {{ $reimbursement->company }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Reimbursement Table -->
        <h3 style="font-weight: 700; font-size: 0.95rem; text-transform: uppercase; margin-top: 1.5rem;">Cash Reimbursement Table</h3>
        <table class="paper-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Date</th>
                    <th style="width: 55%;">Details of Cash Reimbursement</th>
                    <th style="width: 25%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reimbursement->items as $item)
                    <tr class="dashed-row">
                        <td>{{ $item->date->format('d-M-y') }}</td>
                        <td>{{ $item->details }}</td>
                        <td style="text-align: right; font-family: var(--font-paper);">
                            <div style="display: flex; justify-content: space-between; width: 100%;">
                                <span>Rp</span>
                                <span>{{ number_format($item->amount, 0, ',', '.') }}</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @for($i = count($reimbursement->items); $i < 5; $i++)
                    <tr class="dashed-row">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="text-align: right; font-family: var(--font-paper);">
                            <div style="display: flex; justify-content: space-between; width: 100%;">
                                <span>Rp</span>
                                <span>&nbsp;</span>
                            </div>
                        </td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid var(--border-color); font-weight: bold;">
                    <td colspan="2" style="text-align: right; font-family: var(--font-main); font-weight: 700; font-size: 0.95rem; padding: 0.75rem;">Total:</td>
                    <td style="text-align: right; font-family: var(--font-paper); font-size: 1.1rem; padding: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; width: 100%;">
                            <span>Rp</span>
                            <span>{{ number_format($reimbursement->total, 0, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- INVOICE & RECEIPT ATTACHMENTS SECTION WITH LIGHTBOX VIEW FOR ALL APPROVERS -->
        <div style="margin-top: 1.5rem; margin-bottom: 1.5rem; border: 1px solid var(--border-color); padding: 1.25rem; background-color: #FAFBFD;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <h4 style="font-weight: bold; font-size: 0.95rem; text-transform: uppercase; color: var(--primary); font-family: var(--font-heading);">
                    📎 Invoices & Receipts Attachments ({{ $reimbursement->attachments->count() }})
                </h4>
                <span style="font-size: 0.75rem; background: #DBEAFE; color: #1E40AF; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600;">
                    👁️ Dapat Dilihat oleh Semua Approver
                </span>
            </div>

            @if($reimbursement->attachments->isEmpty())
                <p style="color: var(--danger); font-size: 0.85rem; font-style: italic;">Belum ada bukti pembayaran (receipt) ter-upload.</p>
            @else
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 1rem;">
                    @foreach($reimbursement->attachments as $att)
                        <div style="border: 1px solid var(--border-soft); border-radius: 8px; padding: 0.6rem; background: white; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
                            <div>
                                <div style="height: 120px; background: #F1F5F9; border-radius: 6px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 0.6rem; cursor: pointer; position: relative;" 
                                     onclick="openReceiptModal('{{ asset('storage/' . $att->file_path) }}', '{{ $att->original_name }}', '{{ Str::startsWith($att->mime_type, 'image/') ? 'image' : 'pdf' }}')">
                                    @if(Str::startsWith($att->mime_type, 'image/'))
                                        <img src="{{ asset('storage/' . $att->file_path) }}" alt="{{ $att->original_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.2); opacity: 0; transition: opacity 0.2s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.8rem;" onmouseenter="this.style.opacity='1'" onmouseleave="this.style.opacity='0'">
                                            🔍 Klik Zoom Image
                                        </div>
                                    @else
                                        <div style="text-align: center; color: var(--danger); font-weight: bold;">
                                            <span style="font-size: 2rem;">📄</span><br>
                                            <span style="font-size: 0.8rem; color: #1E293B;">PDF Document</span>
                                        </div>
                                    @endif
                                </div>
                                <div style="font-size: 0.82rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--primary);" title="{{ $att->original_name }}">
                                    {{ $att->original_name }}
                                </div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.2rem;">
                                    Tgl Receipt: {{ $att->receipt_date ? $att->receipt_date->format('d-M-Y') : '-' }}<br>
                                    Ukuran: {{ round($att->file_size / 1024, 1) }} KB
                                </div>
                            </div>
                            <div style="margin-top: 0.6rem; display: flex; gap: 0.4rem;">
                                <button type="button" class="btn btn-secondary" onclick="openReceiptModal('{{ asset('storage/' . $att->file_path) }}', '{{ $att->original_name }}', '{{ Str::startsWith($att->mime_type, 'image/') ? 'image' : 'pdf' }}')" style="flex: 1; padding: 0.3rem 0.5rem; font-size: 0.75rem;">
                                    👁️ View Receipt
                                </button>
                                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" download class="btn btn-secondary" style="padding: 0.3rem 0.5rem; font-size: 0.75rem;" title="Download File">
                                    ⬇️
                                </a>
                                @if(in_array($reimbursement->status, ['draft', 'rejected']) && Auth::id() === $reimbursement->user_id)
                                    <form action="{{ route('reimbursements.attachments.destroy', $att) }}" method="POST" onsubmit="return confirm('Hapus receipt ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 0.3rem 0.5rem; font-size: 0.75rem;">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- NOTE -->
        <div class="paper-textarea-section">
            <label class="paper-textarea-label">NOTE :</label>
            <div class="paper-textarea-box" style="min-height: 60px; white-space: pre-wrap;">{{ $reimbursement->note ?? '-' }}</div>
        </div>

        <!-- Reimbursement Payment Details if Reimbursed -->
        @if($reimbursement->reimbursement_status === 'reimbursed')
            <div style="margin-bottom: 1.5rem; background-color: #ECFDF5; border: 1px solid #A7F3D0; padding: 1rem; border-radius: 6px;">
                <h4 style="font-weight: bold; font-size: 0.9rem; color: #065F46; margin-bottom: 0.5rem;">
                    ✅ Reimbursement Payment Completed
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; font-size: 0.85rem;">
                    <div><strong>Paid Amount:</strong> Rp {{ number_format($reimbursement->paid_amount ?? $reimbursement->total, 0, ',', '.') }}</div>
                    <div><strong>Payment Method:</strong> {{ $reimbursement->payment_method ?? 'Bank Transfer' }}</div>
                    <div><strong>Transaction Ref:</strong> {{ $reimbursement->transaction_reference ?? '-' }}</div>
                    <div><strong>Reimbursed Date:</strong> {{ $reimbursement->reimbursed_at ? $reimbursement->reimbursed_at->format('d-M-Y H:i') : '-' }}</div>
                    <div><strong>Processed By:</strong> {{ $reimbursement->reimbursedByUser->name ?? 'Finance Admin' }}</div>
                </div>
            </div>
        @endif

        <!-- Transfer To Bank Account Details (Formatted Display) -->
        <div style="width: 100%; max-width: 420px; margin-bottom: 2rem;">
            <h4 style="font-weight: bold; font-size: 0.95rem; margin-bottom: 0.5rem; text-transform: uppercase;">Bank Account Details</h4>
            <div style="border: 1px solid var(--border-color); padding: 0.75rem; background-color: #FAFBFD; border-radius: 4px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.3rem 0; font-weight: bold; font-size: 0.9rem; border: none; width: 130px;">Transfer To</td>
                        <td style="padding: 0.3rem 0; border: none; font-family: var(--font-paper);">{{ $reimbursement->transfer_to }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.3rem 0; font-weight: bold; font-size: 0.9rem; border: none;">Bank</td>
                        <td style="padding: 0.3rem 0; border: none; font-family: var(--font-paper); font-weight: bold;">
                            {{ $reimbursement->bank }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.3rem 0; font-weight: bold; font-size: 0.9rem; border: none;">Account Number</td>
                        <td style="padding: 0.3rem 0; border: none; font-family: var(--font-paper); font-weight: bold; letter-spacing: 1px;">
                            {{ preg_replace('/(\d{4})(?=\d)/', '$1 ', $reimbursement->account_number) }}
                        </td>
                    </tr>
                </table>
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
                        {{ $reimbursement->user->name }}
                    </div>
                    <div class="signature-name-field">
                        {{ $reimbursement->user->name }}
                    </div>
                    <div class="signature-date">
                        Signed Date: {{ $reimbursement->submitted_at ? $reimbursement->submitted_at->format('d-M-Y') : $reimbursement->created_at->format('d-M-Y') }}
                    </div>
                </div>

                <!-- Box 2: Acknowledged by (Manager) -->
                <div class="signature-card">
                    <div class="signature-title">Acknowledged by</div>
                    <div class="signature-body" style="font-family: var(--font-paper); text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        @if($reimbursement->manager_approved_at)
                            <div style="border: 2px dashed #059669; padding: 0.2rem 0.5rem; border-radius: 4px; background-color: #ECFDF5; color: #065F46;">
                                <div style="font-size: 0.6rem; font-weight: bold;">APPROVED MANAGER</div>
                                <div style="font-family: 'Courier Prime', monospace; font-size: 0.95rem; font-weight: bold; color: #1A2A3A;">
                                    ✍️ {{ $reimbursement->managerUser->name ?? 'Manager' }}
                                </div>
                            </div>
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="signature-name-field">
                        {{ $reimbursement->managerUser->name ?? '' }}
                    </div>
                    <div class="signature-date">
                        Signed Date: {{ $reimbursement->manager_approved_at ? $reimbursement->manager_approved_at->format('d-M-Y') : '' }}
                    </div>
                </div>
            </div>

            <!-- Column 2 (Middle Column): Approved by (Category Approver) & Checked by (FA Manager / Finance) -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <!-- Box 3: Approved by (Billy Gunawan / Apriliansyah) -->
                <div class="signature-card">
                    <div class="signature-title">Approved by</div>
                    <div class="signature-body" style="font-family: var(--font-paper); text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        @if($reimbursement->category_approved_at)
                            <div style="border: 2px dashed #059669; padding: 0.2rem 0.5rem; border-radius: 4px; background-color: #ECFDF5; color: #065F46;">
                                <div style="font-size: 0.6rem; font-weight: bold; text-transform: uppercase;">CATEGORY APPROVED</div>
                                <div style="font-family: 'Courier Prime', monospace; font-size: 0.95rem; font-weight: bold; color: #1A2A3A;">
                                    ✍️ {{ $reimbursement->categoryApprover->name ?? 'Billy Gunawan' }}
                                </div>
                            </div>
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="signature-name-field" style="font-weight: bold;">
                        {{ $reimbursement->categoryApprover->name ?? ($reimbursement->category === 'Technology' ? 'Billy Gunawan' : ($reimbursement->category === 'Commercial' ? 'Apriliansyah' : 'Billy Gunawan OR Apriliansyah')) }}
                    </div>
                    <div class="signature-date">
                        Signed Date: {{ $reimbursement->category_approved_at ? $reimbursement->category_approved_at->format('d-M-Y') : '' }}
                    </div>
                </div>

                <!-- Box 4: Checked by (FA Manager / Finance) -->
                <div class="signature-card">
                    <div class="signature-title">Checked by</div>
                    <div class="signature-body" style="font-family: var(--font-paper); text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        @if($reimbursement->finance_approved_at)
                            <div style="border: 2px dashed #059669; padding: 0.2rem 0.5rem; border-radius: 4px; background-color: #ECFDF5; color: #065F46;">
                                <div style="font-size: 0.6rem; font-weight: bold;">CHECKED FINANCE</div>
                                <div style="font-family: 'Courier Prime', monospace; font-size: 0.95rem; font-weight: bold; color: #1A2A3A;">
                                    ✍️ {{ $reimbursement->finance->name ?? 'FA Manager' }}
                                </div>
                            </div>
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="signature-name-field">
                        {{ $reimbursement->finance->name ?? 'FA Manager' }}
                    </div>
                    <div class="signature-date">
                        Signed Date: {{ $reimbursement->finance_approved_at ? $reimbursement->finance_approved_at->format('d-M-Y') : '' }}
                    </div>
                </div>
            </div>

            <!-- Column 3 (Right Column): Approved by (Pantro Pander) & Checked by (Tung Sen) -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <!-- Box 5: Approved by (Pantro Pander) -->
                <div class="signature-card">
                    <div class="signature-title">Approved by</div>
                    <div class="signature-body" style="font-family: var(--font-paper); text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        @if($reimbursement->pantro_approved_at)
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
                        Signed Date: {{ $reimbursement->pantro_approved_at ? $reimbursement->pantro_approved_at->format('d-M-Y') : '' }}
                    </div>
                </div>

                <!-- Box 6: Checked by (Tung Sen) -->
                <div class="signature-card">
                    <div class="signature-title">Checked by</div>
                    <div class="signature-body" style="font-family: var(--font-paper); text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        @if($reimbursement->tungsen_approved_at)
                            <div style="border: 2px dashed #059669; padding: 0.2rem 0.5rem; border-radius: 4px; background-color: #ECFDF5; color: #065F46;">
                                <div style="font-size: 0.6rem; font-weight: bold;">CHECKED PENGAWAS</div>
                                <div style="font-family: 'Courier Prime', monospace; font-size: 0.95rem; font-weight: bold; color: #1A2A3A;">
                                    ✍️ Tung Sen
                                </div>
                            </div>
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="signature-name-field">
                        Tung Sen
                    </div>
                    <div class="signature-date">
                        Signed Date: {{ $reimbursement->tungsen_approved_at ? $reimbursement->tungsen_approved_at->format('d-M-Y') : '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Lightbox Preview Modal for Approvers -->
<div id="receiptViewerModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 2000; justify-content: center; align-items: center; padding: 1.5rem;">
    <div style="background: white; border-radius: 8px; width: 100%; max-width: 750px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: var(--shadow-lg);">
        <div style="padding: 1rem 1.25rem; background: var(--primary); color: white; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="receiptModalTitle" style="font-size: 1rem; font-family: var(--font-heading); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                Preview Receipt
            </h3>
            <button type="button" onclick="closeReceiptModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div id="receiptModalContent" style="padding: 1rem; flex: 1; overflow-y: auto; display: flex; justify-content: center; align-items: center; background: #F8FAFC;">
            <!-- Dynamic Image / PDF content inserted via JS -->
        </div>
        <div style="padding: 0.75rem 1.25rem; background: white; border-top: 1px solid var(--border-soft); display: flex; justify-content: space-between; align-items: center;">
            <a id="receiptModalDownload" href="#" target="_blank" download class="btn btn-secondary" style="font-size: 0.85rem;">
                ⬇️ Download Receipt Original
            </a>
            <button type="button" class="btn btn-primary" onclick="closeReceiptModal()" style="font-size: 0.85rem;">Tutup</button>
        </div>
    </div>
</div>

<!-- Finance / Approver Reject Modal -->
@if($reimbursement->status === 'pending_finance' && ($canApprove ?? false))
<div id="rejectFinanceModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 8px; width: 100%; max-width: 450px; padding: 1.5rem; box-shadow: var(--shadow-lg);">
        <h3 style="margin-bottom: 1rem; color: var(--danger);">Reject Cash Reimbursement</h3>
        <form action="{{ route('reimbursements.reject', $reimbursement) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.9rem;">Alasan Penolakan (Reject Reason) <span style="color:red">*</span>:</label>
                <textarea name="comment" rows="4" required class="input-control" placeholder="Tuliskan alasan penolakan..."></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('rejectFinanceModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-danger">Ya, Reject CRF</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Finance / Admin Mark as Reimbursed Modal -->
@if(in_array($reimbursement->status, ['approved', 'verified']) && $reimbursement->reimbursement_status !== 'reimbursed' && (Auth::user()->isRole('finance') || Auth::user()->isRole('admin') || Auth::user()->isPengawas()))
<div id="reimburseModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 8px; width: 100%; max-width: 480px; padding: 1.5rem; box-shadow: var(--shadow-lg);">
        <h3 style="margin-bottom: 0.5rem; color: var(--primary);">Mark as Reimbursed</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
            Konfirmasi pembayaran reimbursement sebesar <strong>Rp {{ number_format($reimbursement->total, 0, ',', '.') }}</strong> ke {{ $reimbursement->transfer_to }}.
        </p>

        <form action="{{ route('reimbursements.mark-reimbursed', $reimbursement) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.3rem; font-size: 0.85rem;">Jumlah Pembayaran (Paid Amount):</label>
                <input type="number" step="0.01" name="paid_amount" value="{{ $reimbursement->total }}" class="input-control" required>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.3rem; font-size: 0.85rem;">Metode Pembayaran:</label>
                <select name="payment_method" class="input-control">
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cash">Cash / Tunai</option>
                    <option value="Company Card">Company Credit Card</option>
                </select>
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.3rem; font-size: 0.85rem;">Nomor Referensi Transaksi / Transfer:</label>
                <input type="text" name="transaction_reference" placeholder="e.g. TRX-88912301" class="input-control">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('reimburseModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-success">Konfirmasi Reimbursed</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function openReceiptModal(url, name, type) {
        const modal = document.getElementById('receiptViewerModal');
        const title = document.getElementById('receiptModalTitle');
        const content = document.getElementById('receiptModalContent');
        const download = document.getElementById('receiptModalDownload');

        title.innerText = 'Preview Receipt: ' + name;
        download.href = url;

        if (type === 'image') {
            content.innerHTML = `<img src="${url}" alt="${name}" style="max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 6px; box-shadow: var(--shadow-md);">`;
        } else if (type === 'pdf') {
            content.innerHTML = `<iframe src="${url}" style="width: 100%; height: 70vh; border: none; border-radius: 6px;"></iframe>`;
        } else {
            content.innerHTML = `<div style="text-align: center; padding: 2rem;"><p>Format file tidak dapat di-preview langsung.</p><a href="${url}" target="_blank" class="btn btn-primary" style="margin-top: 1rem;">Buka File</a></div>`;
        }

        modal.style.display = 'flex';
    }

    function closeReceiptModal() {
        document.getElementById('receiptViewerModal').style.display = 'none';
        document.getElementById('receiptModalContent').innerHTML = '';
    }
</script>
@endsection
