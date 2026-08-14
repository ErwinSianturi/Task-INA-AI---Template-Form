@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Welcome back, {{ Auth::user()->name }}!</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Here is the executive summary of Travel Requests (TRF) and Cash Reimbursements (CRF).</p>
    </div>
    
    <div class="btn-group" style="align-items: center;">
        @if(Auth::user()->isRole('employee') || Auth::user()->isRole('admin'))
            <a href="{{ route('reimbursement-choice') }}" class="btn btn-primary">
                + New TRF or CRF
            </a>
        @endif

        @if(Auth::user()->isRole('finance') || Auth::user()->isRole('admin'))
            <a href="{{ route('export.trf') }}" class="btn btn-secondary" title="Export TRF to Excel">📊 Export TRF</a>
            <a href="{{ route('export.crf') }}" class="btn btn-secondary" title="Export CRF to Excel">📊 Export CRF</a>
        @endif
    </div>
</div>

<!-- Executive Summary Widget Cards for Finance / Admin / Manager -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
    <div class="dashboard-card" style="margin-bottom: 0; border-left: 4px solid #D97706; padding: 1rem;">
        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">TRF Pending Manager</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary); margin-top: 0.25rem;">{{ $pendingTRFCount }}</div>
    </div>
    <div class="dashboard-card" style="margin-bottom: 0; border-left: 4px solid #2563EB; padding: 1rem;">
        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">CRF Pending Finance</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary); margin-top: 0.25rem;">{{ $pendingCRFCount }}</div>
    </div>
    <div class="dashboard-card" style="margin-bottom: 0; border-left: 4px solid #059669; padding: 1rem;">
        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Waiting for Reimbursement</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary); margin-top: 0.25rem;">{{ $approvedNotReimbursedCount }}</div>
    </div>
    <div class="dashboard-card" style="margin-bottom: 0; border-left: 4px solid #1E40AF; padding: 1rem;">
        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Already Reimbursed</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary); margin-top: 0.25rem;">{{ $reimbursedCount }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
    <!-- TRF List -->
    <div class="dashboard-card">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #E2E8F0; padding-bottom: 0.75rem; margin-bottom: 1rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.2rem; font-weight: 700;">
                Travel Request Forms (TRF)
            </h3>
            <a href="{{ route('travel-requests.index') }}" style="font-size: 0.9rem; color: var(--accent); font-weight: 600; text-decoration: none;">View All &rarr;</a>
        </div>
        
        @if($travelRequests->isEmpty())
            <p style="color: var(--text-light); text-align: center; padding: 2rem 0;">No travel requests found.</p>
        @else
            <div style="overflow-x: auto;">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Req No</th>
                            <th>Date</th>
                            <th>Company</th>
                            <th>Category</th>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Signed Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($travelRequests as $tr)
                            <tr>
                                <td style="font-family: var(--font-paper); font-weight: bold;">{{ $tr->request_number }}</td>
                                <td>{{ $tr->date->format('d-M-y') }}</td>
                                <td>{{ Str::limit($tr->company, 28) }}</td>
                                <td><span class="badge" style="background: #FFF8E7; color: #D97706; border: 1px solid #FCD34D;">{{ $tr->category }}</span></td>
                                <td>{{ $tr->user->name }}</td>
                                <td>
                                    <span class="badge badge-{{ $tr->status === 'pending_manager' ? 'pending' : $tr->status }}">
                                        {{ str_replace('_', ' ', $tr->status) }}
                                    </span>
                                </td>
                                <td>{{ $tr->signed_date ? $tr->signed_date->format('d-M-Y') : '-' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('travel-requests.show', $tr) }}" class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">View</a>
                                        @if($tr->status === 'approved' && (Auth::user()->isRole('employee') || Auth::user()->isRole('admin')) && !$tr->reimbursement)
                                            <a href="{{ route('reimbursements.create', ['travel_request_id' => $tr->id]) }}" class="btn btn-accent" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">Create CRF</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- CRF List -->
    <div class="dashboard-card">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #E2E8F0; padding-bottom: 0.75rem; margin-bottom: 1rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.2rem; font-weight: 700;">
                Cash Reimbursement Forms (CRF)
            </h3>
            <a href="{{ route('reimbursements.index') }}" style="font-size: 0.9rem; color: var(--accent); font-weight: 600; text-decoration: none;">View All &rarr;</a>
        </div>
        
        @if($reimbursements->isEmpty())
            <p style="color: var(--text-light); text-align: center; padding: 2rem 0;">No cash reimbursements found.</p>
        @else
            <div style="overflow-x: auto;">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Req No</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Transfer To</th>
                            <th>Total Amount</th>
                            <th>Approval Status</th>
                            <th>Reimbursement Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reimbursements as $crf)
                            <tr>
                                <td style="font-family: var(--font-paper); font-weight: bold;">{{ $crf->request_number }}</td>
                                <td><span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">{{ str_replace('_', ' ', $crf->reimbursement_type) }}</span></td>
                                <td>{{ $crf->date->format('d-M-y') }}</td>
                                <td><span class="badge" style="background: #FFF8E7; color: #D97706; border: 1px solid #FCD34D;">{{ $crf->category }}</span></td>
                                <td>{{ $crf->transfer_to }}</td>
                                <td style="font-family: var(--font-paper); font-weight: bold;">Rp {{ number_format($crf->total, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge badge-{{ in_array($crf->status, ['approved', 'verified']) ? 'approved' : ($crf->status === 'pending_finance' ? 'pending' : $crf->status) }}">
                                        {{ in_array($crf->status, ['approved', 'verified']) ? 'APPROVED' : str_replace('_', ' ', $crf->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if(in_array($crf->status, ['approved', 'verified']))
                                        <span class="badge" style="background-color: {{ $crf->reimbursement_status === 'reimbursed' ? '#DBEAFE' : '#F3F4F6' }}; color: {{ $crf->reimbursement_status === 'reimbursed' ? '#1E40AF' : '#4B5563' }};">
                                            {{ $crf->reimbursement_status === 'reimbursed' ? 'REIMBURSED' : 'NOT REIMBURSED' }}
                                        </span>
                                    @else
                                        <span style="color: #94A3B8;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('reimbursements.show', $crf) }}" class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
