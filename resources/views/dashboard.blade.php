@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Welcome back, {{ Auth::user()->name }}!</h1>
        <p style="color: var(--text-light); margin-top: 0.25rem;">Here is the summary of your travel requests and cash reimbursements.</p>
    </div>
    
    @if(Auth::user()->isRole('employee') || Auth::user()->isRole('admin'))
        <div class="btn-group">
            <a href="{{ route('reimbursement-choice') }}" class="btn btn-primary">
                + New Travel or Cash Reimbursement
            </a>
        </div>
    @endif
</div>

<!-- Task Notifications / Widgets for Manager & Finance -->
@if(Auth::user()->isRole('manager') && $pendingTRFCount > 0)
    <div class="alert alert-success" style="background-color: #FFF3CD; color: #856404; border-color: #FFEBAA;">
        <span>🔔 You have <strong>{{ $pendingTRFCount }}</strong> Travel Requests (TRF) pending your approval.</span>
        <a href="{{ route('travel-requests.index') }}" class="btn btn-accent" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Review TRF</a>
    </div>
@endif

@if(Auth::user()->isRole('finance') && $pendingCRFCount > 0)
    <div class="alert alert-success" style="background-color: #D1ECF1; color: #0C5460; border-color: #BEE5EB;">
        <span>💵 You have <strong>{{ $pendingCRFCount }}</strong> Cash Reimbursements (CRF) pending your verification.</span>
        <a href="{{ route('reimbursements.index') }}" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Review CRF</a>
    </div>
@endif

<div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
    <!-- TRF List -->
    <div class="dashboard-card">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #E2E8F0; padding-bottom: 0.75rem; margin-bottom: 1rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.2rem; font-weight: 700;">
                Travel Request Forms (TRF)
            </h3>
            <a href="{{ route('travel-requests.index') }}" style="font-size: 0.9rem; color: var(--accent); font-weight: 600; text-decoration: none;">View All</a>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($travelRequests as $tr)
                            <tr>
                                <td style="font-family: var(--font-paper); font-weight: bold;">{{ $tr->request_number }}</td>
                                <td>{{ $tr->date->format('d-M-y') }}</td>
                                <td>{{ Str::limit($tr->company, 30) }}</td>
                                <td>{{ $tr->category }}</td>
                                <td>{{ $tr->user->name }}</td>
                                <td>
                                    <span class="badge badge-{{ $tr->status === 'pending_manager' ? 'pending' : $tr->status }}">
                                        {{ str_replace('_', ' ', $tr->status) }}
                                    </span>
                                </td>
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
            <a href="{{ route('reimbursements.index') }}" style="font-size: 0.9rem; color: var(--accent); font-weight: 600; text-decoration: none;">View All</a>
        </div>
        
        @if($reimbursements->isEmpty())
            <p style="color: var(--text-light); text-align: center; padding: 2rem 0;">No cash reimbursements found.</p>
        @else
            <div style="overflow-x: auto;">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>TRF Link No</th>
                            <th>Date</th>
                            <th>Company</th>
                            <th>Transfer To</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reimbursements as $crf)
                            <tr>
                                <td style="font-family: var(--font-paper); font-weight: bold;">{{ $crf->request_number }}</td>
                                <td>{{ $crf->date->format('d-M-y') }}</td>
                                <td>{{ Str::limit($crf->company, 30) }}</td>
                                <td>{{ $crf->transfer_to }}</td>
                                <td style="font-family: var(--font-paper); font-weight: bold;">Rp {{ number_format($crf->total, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge badge-{{ $crf->status === 'pending_finance' ? 'pending' : $crf->status }}">
                                        {{ str_replace('_', ' ', $crf->status) }}
                                    </span>
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
