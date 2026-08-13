@extends('layouts.app')

@section('title', 'Travel Request Document - ' . $travelRequest->request_number)

@section('content')
<div class="document-actions-bar" style="margin-left: auto; margin-right: auto;">
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('travel-requests.index') }}" class="btn btn-secondary">&larr; Back</a>
        <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Form</button>
    </div>
    
    <div class="btn-group">
        <!-- Employee Submit Action -->
        @if($travelRequest->status === 'draft' && Auth::id() === $travelRequest->user_id)
            <form action="{{ route('travel-requests.submit', $travelRequest) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">Submit for Approval</button>
            </form>
        @endif

        <!-- Manager / Admin Approval Actions -->
        @if($travelRequest->status === 'pending_manager' && (Auth::user()->isRole('manager') || Auth::user()->isRole('admin')))
            <form action="{{ route('travel-requests.approve', $travelRequest) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-success">Approve TRF</button>
            </form>
            <form action="{{ route('travel-requests.reject', $travelRequest) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-danger">Reject TRF</button>
            </form>
        @endif

        <!-- Employee Link to Create CRF (if approved and no CRF exists yet) -->
        @if($travelRequest->status === 'approved' && !$travelRequest->reimbursement && (Auth::user()->isRole('employee') || Auth::user()->isRole('admin')))
            <a href="{{ route('reimbursements.create', ['travel_request_id' => $travelRequest->id]) }}" class="btn btn-accent">
                Create Cash Reimbursement (CRF)
            </a>
        @endif
    </div>
</div>

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
                <!-- Print padding rows if destinations are few to mimic paper sheet layout -->
                @for($i = count($travelRequest->destinations); $i < 8; $i++)
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
            <div class="paper-textarea-box" style="min-height: 90px; white-space: pre-wrap;">{{ $travelRequest->justification }}</div>
        </div>

        <!-- Benefit -->
        <div class="paper-textarea-section">
            <label class="paper-textarea-label">Benefit</label>
            <div class="paper-textarea-box" style="min-height: 90px; white-space: pre-wrap;">{{ $travelRequest->benefit }}</div>
        </div>

        <!-- Supporting Data Checkbox List (2 kolom: kiri 1&2, kanan 3&4) -->
        <div class="supporting-section">
            <div class="supporting-title">Supporting Datas <em>(check if applicable)</em>:</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 2rem;">
                <!-- Baris 1 Kiri -->
                <div class="supporting-row">
                    <div style="flex: 1; border-bottom: 1px dotted #000000; font-family: var(--font-main); font-size: 0.95rem; padding-bottom: 2px;">
                        {{ $travelRequest->supporting_label_1 ?? 'Invitation' }}
                    </div>
                    <div class="checkbox-box" style="margin-left: 0.5rem;">
                        {!! $travelRequest->supporting_value_1 ? '✓' : '&nbsp;' !!}
                    </div>
                </div>

                <!-- Baris 1 Kanan -->
                <div class="supporting-row">
                    <div style="flex: 1; border-bottom: 1px dotted #000000; font-family: var(--font-main); font-size: 0.95rem; padding-bottom: 2px;">
                        {{ $travelRequest->supporting_label_3 ?? '' }}
                    </div>
                    <div class="checkbox-box" style="margin-left: 0.5rem;">
                        {!! $travelRequest->supporting_value_3 ? '✓' : '&nbsp;' !!}
                    </div>
                </div>

                <!-- Baris 2 Kiri -->
                <div class="supporting-row">
                    <div style="flex: 1; border-bottom: 1px dotted #000000; font-family: var(--font-main); font-size: 0.95rem; padding-bottom: 2px;">
                        {{ $travelRequest->supporting_label_2 ?? 'Travel Invitation Letter' }}
                    </div>
                    <div class="checkbox-box" style="margin-left: 0.5rem;">
                        {!! $travelRequest->supporting_value_2 ? '✓' : '&nbsp;' !!}
                    </div>
                </div>

                <!-- Baris 2 Kanan -->
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

        <!-- Signature block grid layout (2 columns) -->
        <div class="signature-grid-2">
            <!-- Column 1: Requested & Acknowledged -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Requested by -->
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

                <!-- Acknowledged by -->
                <div class="signature-card">
                    <div class="signature-title">Acknowledged by</div>
                    <div class="signature-body">
                        @if($travelRequest->status === 'approved')
                            Pantro Pander
                        @else
                            XXXX
                        @endif
                    </div>
                    <div class="signature-name-field">
                        @if($travelRequest->status === 'approved')
                            Pantro Pander
                        @else
                            XXXX
                        @endif
                    </div>
                    <div class="signature-date">
                        Signed Date: @if($travelRequest->approved_at) {{ $travelRequest->approved_at->format('d-M-Y') }} @endif
                    </div>
                </div>
            </div>

            <!-- Column 2: Approved by -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Approved by (1) -->
                <div class="signature-card">
                    <div class="signature-title">Approved by</div>
                    <div class="signature-body highlight" style="font-family: var(--font-paper); font-weight: bold; text-align: center; display: flex; flex-direction: column; justify-content: center;">
                        @if($travelRequest->status === 'approved')
                            <span style="font-size: 1.2rem; letter-spacing: 2px;">APPROVED</span>
                            <span style="font-size: 0.8rem; font-weight: normal;">SYSTEM MANAGER</span>
                        @endif
                    </div>
                    <div class="signature-name-field">
                        @if($travelRequest->status === 'approved')
                            Manager
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="signature-date">
                        Signed Date: @if($travelRequest->approved_at) {{ $travelRequest->approved_at->format('d-M-Y') }} @endif
                    </div>
                </div>

                <!-- Approved by (2) -->
                <div class="signature-card">
                    <div class="signature-title">Approved by</div>
                    <div class="signature-body">
                        @if($travelRequest->status === 'approved')
                            Pantro Pander
                        @else
                            Pantro Pander
                        @endif
                    </div>
                    <div class="signature-name-field">
                        Pantro Pander
                    </div>
                    <div class="signature-date">
                        Signed Date: @if($travelRequest->approved_at) {{ $travelRequest->approved_at->format('d-M-Y') }} @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
