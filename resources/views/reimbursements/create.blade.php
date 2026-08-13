@extends('layouts.app')

@section('title', 'Create Cash Reimbursement')

@section('content')
<div class="document-actions-bar" style="margin-left: auto; margin-right: auto;">
    <a href="{{ route('reimbursements.index') }}" class="btn btn-secondary">&larr; Back to List</a>
    <h2 style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 700; color: var(--primary-light);">
        Filling Cash Reimbursement Form
    </h2>
</div>

@if(!$isNonTravel)
<!-- Select Approved TRF first -->
<div class="dashboard-card" style="max-width: 850px; margin: 0 auto 1.5rem auto;">
    <label for="tr_select" style="font-weight: 700; display: block; margin-bottom: 0.5rem;">Select Approved Travel Request (TRF):</label>
    <select id="tr_select" class="input-control" style="font-weight: 600; padding: 0.5rem 0.75rem;" onchange="updateFormFromTR(this)">
        <option value="">-- Choose Approved TRF --</option>
        @foreach($approvedTravelRequests as $tr)
            <option value="{{ $tr->id }}" 
                    data-reqno="{{ $tr->request_number }}" 
                    data-category="{{ $tr->category }}" 
                    data-company="{{ $tr->company }}"
                    {{ ($selectedTR && $selectedTR->id == $tr->id) ? 'selected' : '' }}>
                TRF: {{ $tr->request_number }} - {{ $tr->category }} ({{ $tr->date->format('d-M-Y') }})
            </option>
        @endforeach
    </select>
    <p style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.4rem;">
        Note: You can only reimburse for approved travel requests that have no pending or verified reimbursement.
    </p>
</div>
@endif

<form action="{{ route('reimbursements.store') }}" method="POST" id="crfForm" style="display: {{ ($isNonTravel || $selectedTR || old('travel_request_id')) ? 'block' : 'none' }};">
    @csrf
    
    <input type="hidden" name="reimbursement_type" value="{{ $isNonTravel ? 'non_travel' : 'travel' }}">
    <input type="hidden" name="travel_request_id" id="hidden_tr_id" value="{{ $selectedTR ? $selectedTR->id : old('travel_request_id') }}">

    <div class="paper-container">
        <div class="paper-sheet">
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
                        <div class="form-value-box flex-1" id="tr_req_no_display" style="background-color: #f7f7f7; font-weight: bold; color: #7f8c8d; font-style: italic;">
                            @if($isNonTravel)
                                XXX/WM-YBAR (Auto-generated)
                            @else
                                {{ $selectedTR ? $selectedTR->request_number : '' }}
                            @endif
                        </div>
                    </div>
                    <div class="form-group-custom">
                        <span class="form-label-custom">Date:</span>
                        <div class="form-value-box flex-1" style="padding: 0.1rem 0.5rem;">
                            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Right Header Column -->
                <div>
                    <div class="form-group-custom" style="margin-bottom: 1rem;">
                        <span class="form-label-custom">Category:</span>
                        @if($isNonTravel)
                            <div class="form-value-box-yellow flex-1" style="padding: 0.1rem 0.5rem;">
                                <input type="text" name="category" value="{{ old('category', 'General') }}" required placeholder="e.g. Office Supplies" style="font-weight: bold;">
                            </div>
                        @else
                            <div class="form-value-box-yellow flex-1" style="font-weight: bold; min-height: 35px;" id="tr_category_display">
                                {{ $selectedTR ? $selectedTR->category : '' }}
                            </div>
                            <input type="hidden" name="category" id="hidden_category" value="{{ $selectedTR ? $selectedTR->category : '' }}">
                        @endif
                    </div>
                    <div class="form-group-custom" style="align-items: flex-start;">
                        <span class="form-label-custom" style="margin-top: 0.4rem;">Company:</span>
                        @if($isNonTravel)
                            <div class="form-value-box flex-1" style="padding: 0.1rem 0.5rem;">
                                <textarea name="company" rows="2" required placeholder="Company Name" style="resize: none;">{{ old('company', 'PT Teknologi Cerdas Berdaulat Indonesia') }}</textarea>
                            </div>
                        @else
                            <div class="form-value-box flex-1" style="min-height: 45px; line-height: 1.3;" id="tr_company_display">
                                {{ $selectedTR ? $selectedTR->company : '' }}
                            </div>
                            <input type="hidden" name="company" id="hidden_company" value="{{ $selectedTR ? $selectedTR->company : '' }}">
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cash Reimbursement Table -->
            <h3 style="font-weight: 700; font-size: 0.95rem; text-transform: uppercase; margin-top: 1.5rem;">Cash Reimbursement Table</h3>
            <table class="paper-table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width: 20%;">Date</th>
                        <th style="width: 55%;">Details of Cash Reimbursement</th>
                        <th style="width: 25%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="dashed-row">
                        <td><input type="date" name="items[0][date]" required value="{{ date('Y-m-d') }}"></td>
                        <td><input type="text" name="items[0][details]" required placeholder="Details of expense..."></td>
                        <td style="border: none; padding: 0.3rem 0;">
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <span style="font-family: var(--font-paper); font-weight: bold; white-space: nowrap;">Rp</span>
                                <input type="text" class="item-amount-display" placeholder="0" style="text-align: right; width: 100%;" inputmode="numeric" autocomplete="off">
                                <input type="hidden" name="items[0][amount]" class="item-amount-raw" value="">
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                <tr style="border-top: 2px solid var(--border-color); font-weight: bold;">
                    <td colspan="2" style="text-align: right; font-family: var(--font-main); font-weight: 700; font-size: 0.95rem;">Total:</td>
                    <td style="border: none; padding: 0.5rem 0;">
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <span style="font-family: var(--font-paper); font-weight: bold; white-space: nowrap;">Rp</span>
                            <input type="text" id="total_display" value="" readonly style="text-align: right; font-weight: bold; border: none; outline: none; background: transparent; width: 100%;">
                        </div>
                    </td>
                </tr>
            </tfoot>
            </table>

            <!-- Add/Remove Row Buttons -->
            <div class="btn-group" style="margin-bottom: 2rem;">
                <button type="button" class="btn btn-secondary" onclick="addItemRow()" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">+ Add Row</button>
                <button type="button" class="btn btn-danger" onclick="removeItemRow()" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">- Remove Row</button>
            </div>

            <!-- NOTE -->
            <div class="paper-textarea-section">
                <label class="paper-textarea-label">NOTE :</label>
                <textarea name="note" class="paper-textarea-box" placeholder="Enter additional notes here...">{{ old('note') }}</textarea>
            </div>

            <!-- Transfer To bank details -->
            <div style="width: 100%; max-width: 400px; margin-bottom: 2rem;">
                <h4 style="font-weight: bold; font-size: 0.95rem; margin-bottom: 0.5rem; text-transform: uppercase;">Bank Account Details</h4>
                <div style="border: 1px solid var(--border-color); padding: 0.75rem; background-color: transparent;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0.3rem 0; font-weight: bold; font-size: 0.9rem; border: none; width: 130px;">Transfer To</td>
                            <td style="padding: 0.3rem 0; border: none;">
                                <input type="text" name="transfer_to" required placeholder="Account Holder Name" value="{{ old('transfer_to', Auth::user()->name) }}" style="border-bottom: 1px solid var(--border-color) !important; padding: 0.1rem 0;">
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.3rem 0; font-weight: bold; font-size: 0.9rem; border: none;">Bank</td>
                            <td style="padding: 0.3rem 0; border: none;">
                                <input type="text" name="bank" required placeholder="e.g. BCA, Mandiri" value="{{ old('bank') }}" style="border-bottom: 1px solid var(--border-color) !important; padding: 0.1rem 0;">
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.3rem 0; font-weight: bold; font-size: 0.9rem; border: none;">Account Number</td>
                            <td style="padding: 0.3rem 0; border: none;">
                                <input type="text" name="account_number" required placeholder="Enter bank account number" value="{{ old('account_number') }}" style="border-bottom: 1px solid var(--border-color) !important; padding: 0.1rem 0;">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Signature block grid layout (3 columns) -->
            <div class="signature-grid-3">
                <!-- Column 1: Requested & Acknowledged -->
                <div class="signature-card">
                    <div class="signature-title">Requested by</div>
                    <div class="signature-body" style="font-style: italic; color: #7f8c8d;">
                        (Draft)
                    </div>
                    <div class="signature-name-field">
                        {{ Auth::user()->name }}
                    </div>
                    <div class="signature-date">
                        Signed Date: {{ date('d-M-Y') }}
                    </div>
                    
                    <div class="signature-title" style="border-top: 1px solid var(--border-color);">Acknowledged by</div>
                    <div class="signature-body" style="color: #bdc3c7;">
                        XXXX
                    </div>
                    <div class="signature-name-field">
                        XXXX
                    </div>
                    <div class="signature-date">
                        Signed Date:
                    </div>
                </div>

                <!-- Column 2: Approved & Checked (Middle Column) -->
                <div class="signature-card">
                    <div class="signature-title">Approved by</div>
                    <div class="signature-body highlight">
                        <!-- Yellow Highlight Block -->
                    </div>
                    <div class="signature-name-field" style="color: #bdc3c7;">
                        &nbsp;
                    </div>
                    <div class="signature-date">
                        Signed Date:
                    </div>
                    
                    <div class="signature-title" style="border-top: 1px solid var(--border-color);">Checked by</div>
                    <div class="signature-body" style="color: #7f8c8d;">
                        FA Manager
                    </div>
                    <div class="signature-name-field">
                        FA Manager
                    </div>
                    <div class="signature-date">
                        Signed Date:
                    </div>
                </div>

                <!-- Column 3: Approved & Checked (Right Column) -->
                <div class="signature-card">
                    <div class="signature-title">Approved by</div>
                    <div class="signature-body" style="color: #bdc3c7;">
                        Pantro Pander
                    </div>
                    <div class="signature-name-field">
                        Pantro Pander
                    </div>
                    <div class="signature-date">
                        Signed Date:
                    </div>
                    
                    <div class="signature-title" style="border-top: 1px solid var(--border-color);">Checked by</div>
                    <div class="signature-body" style="color: #7f8c8d;">
                        Tung Sen
                    </div>
                    <div class="signature-name-field">
                        Tung Sen
                    </div>
                    <div class="signature-date">
                        Signed Date:
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Submission Actions -->
        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" name="action" value="draft" class="btn btn-secondary" style="padding: 0.8rem 2rem; font-size: 1rem;">
                Save Draft
            </button>
            <button type="submit" name="action" value="submit" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem;">
                Submit Reimbursement (CRF)
            </button>
        </div>
    </div>
</form>

<script>
    let itemRowIndex = 1;

    function updateFormFromTR(selectElement) {
        const form = document.getElementById('crfForm');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        
        if (!selectedOption.value) {
            form.style.display = 'none';
            return;
        }

        const reqNo = selectedOption.getAttribute('data-reqno');
        const category = selectedOption.getAttribute('data-category');
        const company = selectedOption.getAttribute('data-company');

        // Update displays
        document.getElementById('hidden_tr_id').value = selectedOption.value;
        document.getElementById('tr_req_no_display').innerText = reqNo;
        document.getElementById('tr_category_display').innerText = category;
        document.getElementById('tr_company_display').innerText = company;

        // Update hidden inputs for submission
        document.getElementById('hidden_category').value = category;
        document.getElementById('hidden_company').value = company;

        // Display the form sheet
        form.style.display = 'block';
    }

    function addItemRow() {
        const tableBody = document.querySelector('#itemsTable tbody');
        const newRow = document.createElement('tr');
        newRow.className = 'dashed-row';
        
        const todayStr = new Date().toISOString().split('T')[0];

        newRow.innerHTML = `
            <td><input type="date" name="items[${itemRowIndex}][date]" required value="${todayStr}"></td>
            <td><input type="text" name="items[${itemRowIndex}][details]" required placeholder="Details of expense..."></td>
            <td style="border: none; padding: 0.3rem 0;">
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <span style="font-family: var(--font-paper); font-weight: bold; white-space: nowrap;">Rp</span>
                    <input type="text" class="item-amount-display" placeholder="0" style="text-align: right; width: 100%;" inputmode="numeric" autocomplete="off">
                    <input type="hidden" name="items[${itemRowIndex}][amount]" class="item-amount-raw" value="">
                </div>
            </td>
        `;
        tableBody.appendChild(newRow);
        // Initialize rupiah formatter for the new row
        const displays = newRow.querySelectorAll('.item-amount-display');
        const raws = newRow.querySelectorAll('.item-amount-raw');
        if (displays.length && raws.length) {
            initRupiahInput(displays[0], raws[0], recalculateTotal);
        }
        itemRowIndex++;
    }

    function removeItemRow() {
        const rows = document.querySelectorAll('#itemsTable tbody tr');
        if (rows.length > 1) {
            rows[rows.length - 1].remove();
            itemRowIndex--;
            recalculateTotal();
        } else {
            alert('At least one reimbursement item is required.');
        }
    }

    function recalculateTotal() {
        let total = 0;
        const rawInputs = document.querySelectorAll('.item-amount-raw');
        rawInputs.forEach(input => {
            const val = parseInt(input.value, 10);
            if (!isNaN(val) && val > 0) total += val;
        });
        document.getElementById('total_display').value = total > 0 ? rupiahFormat(total) : '0';
    }

    // Run trigger on page load
    document.addEventListener("DOMContentLoaded", function() {
        // Init TRF dropdown if travel mode
        const select = document.getElementById('tr_select');
        if (select && select.value) {
            updateFormFromTR(select);
        }

        // Init all existing amount display inputs
        document.querySelectorAll('.item-amount-display').forEach(function(display) {
            const row = display.closest('td').closest('tr') || display.closest('div').closest('td').closest('tr');
            const raw = display.closest('div').querySelector('.item-amount-raw');
            if (raw) initRupiahInput(display, raw, recalculateTotal);
        });
    });
</script>
@endsection
