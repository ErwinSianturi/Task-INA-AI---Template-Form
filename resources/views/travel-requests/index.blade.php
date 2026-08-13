@extends('layouts.app')

@section('title', 'Travel Request Forms')

@section('content')
<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Travel Request Forms (TRF)</h1>
        <p style="color: var(--text-light); margin-top: 0.25rem;">Manage and review company travel requests.</p>
    </div>
    
    @if(Auth::user()->isRole('employee') || Auth::user()->isRole('admin'))
        <a href="{{ route('reimbursement-choice') }}" class="btn btn-primary">
            + Create New Form
        </a>
    @endif
</div>

<div class="dashboard-card">
    @if($travelRequests->isEmpty())
        <p style="color: var(--text-light); text-align: center; padding: 3rem 0;">No travel requests found.</p>
    @else
        <div style="overflow-x: auto;">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Request Number</th>
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
                            <td>{{ $tr->company }}</td>
                            <td>{{ $tr->category }}</td>
                            <td>{{ $tr->user->name }}</td>
                            <td>
                                <span class="badge badge-{{ $tr->status === 'pending_manager' ? 'pending' : $tr->status }}">
                                    {{ str_replace('_', ' ', $tr->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('travel-requests.show', $tr) }}" class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">View Document</a>
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
@endsection
