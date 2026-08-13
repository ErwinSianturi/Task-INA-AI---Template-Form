@extends('layouts.app')

@section('title', 'Reimbursement Type Selection')

@section('content')
<div style="max-width: 700px; margin: 3rem auto; text-align: center;">
    <h1 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem; letter-spacing: -0.5px;">
        Travel or Cash Reimbursement
    </h1>
    <p style="color: var(--text-light); font-size: 1.1rem; margin-bottom: 3rem;">
        Choose the type of reimbursement form you want to create.
    </p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Travel Option Card -->
        <a href="{{ route('travel-requests.create') }}" class="dashboard-card" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem 2rem; border: 2px solid #E2E8F0; border-radius: 12px; transition: all 0.3s ease; cursor: pointer;">
            <div style="font-size: 3rem; margin-bottom: 1.5rem; background-color: rgba(44, 62, 80, 0.05); width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                ✈️
            </div>
            <h3 style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 0.75rem;">
                Travel
            </h3>
            <p style="color: var(--text-light); font-size: 0.95rem; line-height: 1.5; margin: 0;">
                Requires creating and obtaining approval for a **Travel Request Form (TRF)** prior to submitting reimbursement.
            </p>
        </a>

        <!-- Non-Travel Option Card -->
        <a href="{{ route('reimbursements.create', ['type' => 'non_travel']) }}" class="dashboard-card" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem 2rem; border: 2px solid #E2E8F0; border-radius: 12px; transition: all 0.3s ease; cursor: pointer;">
            <div style="font-size: 3rem; margin-bottom: 1.5rem; background-color: rgba(230, 161, 0, 0.05); width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                💼
            </div>
            <h3 style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 0.75rem;">
                Non Travel
            </h3>
            <p style="color: var(--text-light); font-size: 0.95rem; line-height: 1.5; margin: 0;">
                Directly submit a **Cash Reimbursement Form (CRF)** without a travel request document.
            </p>
        </a>
    </div>
</div>

<style>
    .dashboard-card:hover {
        transform: translateY(-5px);
        border-color: var(--accent) !important;
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
</style>
@endsection
