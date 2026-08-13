@extends('layouts.app')

@section('title', 'Cash Reimbursement Document - ' . $reimbursement->request_number)

@section('content')
<div class="document-actions-bar" style="margin-left: auto; margin-right: auto;">
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('reimbursements.index') }}" class="btn btn-secondary">&larr; Back</a>
        <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Form</button>
    </div>
    
    <div class="btn-group">
        <!-- Employee Submit Action -->
        @if(in_array($reimbursement->status, ['draft', 'rejected']) && Auth::id() === $reimbursement->user_id)
            <form action="{{ route('reimbursements.submit', $reimbursement) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">Submit for Verification</button>
            </form>
        @endif

        <!-- Finance / Admin Verification Actions -->
        @if($reimbursement->status === 'pending_finance' && (Auth::user()->isRole('finance') || Auth::user()->isRole('admin')))
            <form action="{{ route('reimbursements.verify', $reimbursement) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-success">Verify & Approve</button>
            </form>
            <form action="{{ route('reimbursements.reject', $reimbursement) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-danger">Reject</button>
            </form>
        @endif
    </div>
</div>

<div class="paper-container">
    <div class="paper-sheet">
        <!-- Status Watermark (For non-printed screens) -->
        <div style="position: absolute; top: 1.5rem; left: 3rem; border: 3px double {{ $reimbursement->status === 'verified' ? '#2ECC71' : ($reimbursement->status === 'rejected' ? '#E74C3C' : '#F1C40F') }}; color: {{ $reimbursement->status === 'verified' ? '#2ECC71' : ($reimbursement->status === 'rejected' ? '#E74C3C' : '#F1C40F') }}; padding: 0.25rem 1rem; font-family: var(--font-heading); font-size: 1.1rem; font-weight: 800; text-transform: uppercase; transform: rotate(-5deg); z-index: 10;">
            Status: {{ str_replace('_', ' ', $reimbursement->status) }}
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
                <!-- Print padding rows -->
                @for($i = count($reimbursement->items); $i < 6; $i++)
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

        <!-- NOTE -->
        <div class="paper-textarea-section">
            <label class="paper-textarea-label">NOTE :</label>
            <div class="paper-textarea-box" style="min-height: 70px; white-space: pre-wrap;">{{ $reimbursement->note ?? '-' }}</div>
        </div>

        <!-- Transfer To bank details -->
        <div style="width: 100%; max-width: 400px; margin-bottom: 2rem;">
            <h4 style="font-weight: bold; font-size: 0.95rem; margin-bottom: 0.5rem; text-transform: uppercase;">Bank Account Details</h4>
            <div style="border: 1px solid var(--border-color); padding: 0.75rem; background-color: transparent;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.3rem 0; font-weight: bold; font-size: 0.9rem; border: none; width: 130px;">Transfer To</td>
                        <td style="padding: 0.3rem 0; border: none; font-family: var(--font-paper);">{{ $reimbursement->transfer_to }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.3rem 0; font-weight: bold; font-size: 0.9rem; border: none;">Bank</td>
                        <td style="padding: 0.3rem 0; border: none; font-family: var(--font-paper);">{{ $reimbursement->bank }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.3rem 0; font-weight: bold; font-size: 0.9rem; border: none;">Account Number</td>
                        <td style="padding: 0.3rem 0; border: none; font-family: var(--font-paper);">{{ $reimbursement->account_number }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Signature block grid layout (3 columns) -->
        <div class="signature-grid-3">
            <!-- Column 1: Requested & Acknowledged -->
            <div class="signature-card">
                <div class="signature-title">Requested by</div>
                <div class="signature-body" style="font-family: var(--font-paper); font-weight: bold;">
                    {{ $reimbursement->user->name }}
                </div>
                <div class="signature-name-field">
                    XXXX
                </div>
                <div class="signature-date">
                    Signed Date: {{ $reimbursement->submitted_at ? $reimbursement->submitted_at->format('d-M-Y') : $reimbursement->created_at->format('d-M-Y') }}
                </div>
                
                <div class="signature-title" style="border-top: 1px solid var(--border-color);">Acknowledged by</div>
                <div class="signature-body">
                    @if($reimbursement->status === 'verified')
                        Verified
                    @else
                        XXXX
                    @endif
                </div>
                <div class="signature-name-field">
                    XXXX
                </div>
                <div class="signature-date">
                    Signed Date: @if($reimbursement->verified_at) {{ $reimbursement->verified_at->format('d-M-Y') }} @endif
                </div>
            </div>

            <!-- Column 2: Approved & Checked (Middle Column) -->
            <div class="signature-card">
                <div class="signature-title">Approved by</div>
                <div class="signature-body highlight" style="font-family: var(--font-paper); font-weight: bold; text-align: center; display: flex; flex-direction: column; justify-content: center;">
                    @if($reimbursement->status === 'verified')
                        <span style="font-size: 1.1rem; letter-spacing: 2px;">APPROVED</span>
                        <span style="font-size: 0.75rem; font-weight: normal;">FINANCE SYSTEM</span>
                    @endif
                </div>
                <div class="signature-name-field" style="color: #bdc3c7;">
                    &nbsp;
                </div>
                <div class="signature-date">
                    Signed Date: @if($reimbursement->verified_at) {{ $reimbursement->verified_at->format('d-M-Y') }} @endif
                </div>
                
                <div class="signature-title" style="border-top: 1px solid var(--border-color);">Checked by</div>
                <div class="signature-body">
                    FA Manager
                </div>
                <div class="signature-name-field">
                    FA Manager
                </div>
                <div class="signature-date">
                    Signed Date: @if($reimbursement->verified_at) {{ $reimbursement->verified_at->format('d-M-Y') }} @endif
                </div>
            </div>

            <!-- Column 3: Approved & Checked (Right Column) -->
            <div class="signature-card">
                <div class="signature-title">Approved by</div>
                <div class="signature-body">
                    Pantro Pander
                </div>
                <div class="signature-name-field">
                    Pantro Pander
                </div>
                <div class="signature-date">
                    Signed Date: @if($reimbursement->verified_at) {{ $reimbursement->verified_at->format('d-M-Y') }} @endif
                </div>
                
                <div class="signature-title" style="border-top: 1px solid var(--border-color);">Checked by</div>
                <div class="signature-body">
                    Tung Sen
                </div>
                <div class="signature-name-field">
                    Tung Sen
                </div>
                <div class="signature-date">
                    Signed Date: @if($reimbursement->verified_at) {{ $reimbursement->verified_at->format('d-M-Y') }} @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
