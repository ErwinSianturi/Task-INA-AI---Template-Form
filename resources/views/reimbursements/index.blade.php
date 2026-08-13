@extends('layouts.app')

@section('title', 'Cash Reimbursement Forms')

@section('content')
<div class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Cash Reimbursement Forms (CRF)</h1>
        <p style="color: var(--text-light); margin-top: 0.25rem;">Manage and review cash reimbursement requests.</p>
    </div>
    
    @if(Auth::user()->isRole('employee') || Auth::user()->isRole('admin'))
        <a href="{{ route('reimbursement-choice') }}" class="btn btn-primary">
            + Create New Form
        </a>
    @endif
</div>

<div class="dashboard-card">
    @if($reimbursements->isEmpty())
        <p style="color: var(--text-light); text-align: center; padding: 3rem 0;">No cash reimbursements found.</p>
    @else
        <div style="overflow-x: auto;">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>TRF Link No</th>
                        <th>Date</th>
                        <th>Company</th>
                        <th>Category</th>
                        <th>Transfer To</th>
                        <th>Bank / Account</th>
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
                            <td>{{ $crf->company }}</td>
                            <td>{{ $crf->category }}</td>
                            <td>{{ $crf->transfer_to }}</td>
                            <td>{{ $crf->bank }} / {{ $crf->account_number }}</td>
                            <td style="font-family: var(--font-paper); font-weight: bold;">Rp {{ number_format($crf->total, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-{{ $crf->status === 'pending_finance' ? 'pending' : $crf->status }}">
                                    {{ str_replace('_', ' ', $crf->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('reimbursements.show', $crf) }}" class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">View Document</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
