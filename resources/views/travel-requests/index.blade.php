@extends('layouts.app')

@section('title', 'Travel Request Forms')

@section('content')
<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Travel Request Forms (TRF)</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Manage and review travel reimbursement requests.</p>
    </div>
    
    <div class="btn-group">
        @if(Auth::user()->isRole('employee') || Auth::user()->isRole('admin'))
            <a href="{{ route('travel-requests.create') }}" class="btn btn-primary">
                + New Travel Request
            </a>
        @endif

        @if(Auth::user()->isRole('finance') || Auth::user()->isRole('admin'))
            <a href="{{ route('export.trf', request()->query()) }}" class="btn btn-secondary">
                📊 Export Excel
            </a>
        @endif
    </div>
</div>

<!-- Filter Bar -->
<div class="dashboard-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('travel-requests.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 120px 80px; gap: 1rem; align-items: end;">
        <div>
            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 0.3rem;">Category:</label>
            <select name="category" class="input-control">
                <option value="">All Categories</option>
                <option value="Technology" {{ request('category') === 'Technology' ? 'selected' : '' }}>Technology</option>
                <option value="Commercial" {{ request('category') === 'Commercial' ? 'selected' : '' }}>Commercial</option>
                <option value="Others" {{ request('category') === 'Others' ? 'selected' : '' }}>Others</option>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 0.3rem;">Status:</label>
            <select name="status" class="input-control">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="pending_manager" {{ request('status') === 'pending_manager' ? 'selected' : '' }}>Pending Manager</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 0.3rem;">From Date:</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-control">
        </div>

        <div>
            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 0.3rem;">To Date:</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-control">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Filter</button>
        <a href="{{ route('travel-requests.index') }}" class="btn btn-secondary" style="width: 100%; text-align: center;">Reset</a>
    </form>
</div>

<div class="dashboard-card">
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
                        <th>Manager Approver</th>
                        <th>Signed Date</th>
                        <th>Status</th>
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
                            <td>{{ $tr->manager->name ?? '-' }}</td>
                            <td>{{ $tr->signed_date ? $tr->signed_date->format('d-M-Y') : '-' }}</td>
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
@endsection
