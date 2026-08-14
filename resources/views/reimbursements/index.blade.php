@extends('layouts.app')

@section('title', 'Cash Reimbursement Forms')

@section('content')
<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Cash Reimbursement Forms (CRF)</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Manage and review cash reimbursement requests and payment status.</p>
    </div>
    
    <div class="btn-group">
        @if(Auth::user()->isRole('employee') || Auth::user()->isRole('admin'))
            <a href="{{ route('reimbursement-choice') }}" class="btn btn-primary">
                + New Cash Reimbursement
            </a>
        @endif

        @if(Auth::user()->isRole('finance') || Auth::user()->isRole('admin'))
            <a href="{{ route('export.crf', request()->query()) }}" class="btn btn-secondary">
                📊 Export Excel
            </a>
        @endif
    </div>
</div>

<!-- Filter Bar -->
<div class="dashboard-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('reimbursements.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) 120px 80px; gap: 0.75rem; align-items: end;">
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
            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 0.3rem;">Approval Status:</label>
            <select name="status" class="input-control">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="pending_finance" {{ request('status') === 'pending_finance' ? 'selected' : '' }}>Pending Finance</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 0.3rem;">Reimbursement:</label>
            <select name="reimbursement_status" class="input-control">
                <option value="">All Reimbursements</option>
                <option value="not_reimbursed" {{ request('reimbursement_status') === 'not_reimbursed' ? 'selected' : '' }}>Not Reimbursed</option>
                <option value="reimbursed" {{ request('reimbursement_status') === 'reimbursed' ? 'selected' : '' }}>Reimbursed</option>
            </select>
        </div>

        <div>
            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 0.3rem;">TRF Link Type:</label>
            <select name="type" class="input-control">
                <option value="">All Types</option>
                <option value="travel" {{ request('type') === 'travel' ? 'selected' : '' }}>Travel CRF</option>
                <option value="non_travel" {{ request('type') === 'non_travel' ? 'selected' : '' }}>Non-Travel CRF</option>
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
        <a href="{{ route('reimbursements.index') }}" class="btn btn-secondary" style="width: 100%; text-align: center;">Reset</a>
    </form>
</div>

<div class="dashboard-card">
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
                        <th>Employee</th>
                        <th>Transfer To</th>
                        <th>Total Amount</th>
                        <th>Approval Status</th>
                        <th>Reimbursement Status</th>
                        <th>Signed Date</th>
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
                            <td>{{ $crf->user->name }}</td>
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
                            <td>{{ $crf->signed_date ? $crf->signed_date->format('d-M-Y') : '-' }}</td>
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
@endsection
