@extends('layouts.app')

@section('title', 'Create Cash Reimbursement Form (CRF)')

@section('content')
<div class="document-actions-bar" style="margin-left: auto; margin-right: auto; max-width: 850px;">
    <a href="{{ route('reimbursements.index') }}" class="btn btn-secondary">&larr; Back to List</a>
    <h2 style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 700; color: var(--primary-light);">
        Filling Cash Reimbursement Form (CRF)
    </h2>
</div>

@if(!$isNonTravel)
<!-- Select Approved TRF first -->
<div class="dashboard-card" style="max-width: 850px; margin: 0 auto 1.5rem auto; border-top: 4px solid var(--accent);">
    <label for="tr_select" style="font-weight: 700; font-family: var(--font-heading); display: block; margin-bottom: 0.5rem; color: var(--primary);">
        ✈️ Select Approved Travel Request Form (TRF):
    </label>
    <select id="tr_select" class="input-control" style="font-weight: 600; padding: 0.6rem 0.85rem;" onchange="updateFormFromTR(this)">
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
    <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.4rem;">
        💡 Note: Only approved travel requests without an existing verified/pending reimbursement are listed.
    </p>
</div>
@endif

<form action="{{ route('reimbursements.store') }}" method="POST" enctype="multipart/form-data" id="crfForm" style="display: {{ ($isNonTravel || $selectedTR || old('travel_request_id')) ? 'block' : 'none' }};">
    @csrf
    
    <input type="hidden" name="reimbursement_type" value="{{ $isNonTravel ? 'non_travel' : 'travel' }}">
    <input type="hidden" name="travel_request_id" id="hidden_tr_id" value="{{ $selectedTR ? $selectedTR->id : old('travel_request_id') }}">

    <div class="paper-container">
        <div class="paper-sheet">
            <!-- Header Block -->
            <div class="form-header-block">
                Cash Reimbursement Form
            </div>

            <!-- Fields Grid Header (Request No, Date, Category, Company) -->
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
                        <div class="form-value-box-yellow flex-1" style="padding: 0.1rem 0.5rem;">
                            <select name="category" id="category_select" required style="font-weight: bold; background: transparent; border: none; width: 100%;">
                                <option value="Technology" {{ (old('category', $selectedTR->category ?? '') === 'Technology') ? 'selected' : '' }}>Technology</option>
                                <option value="Commercial" {{ (old('category', $selectedTR->category ?? '') === 'Commercial') ? 'selected' : '' }}>Commercial</option>
                                <option value="Others" {{ (old('category', $selectedTR->category ?? '') === 'Others') ? 'selected' : '' }}>Others</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group-custom" style="align-items: flex-start;">
                        <span class="form-label-custom" style="margin-top: 0.4rem;">Company:</span>
                        <div class="form-value-box flex-1" style="padding: 0.4rem 0.75rem; background-color: #F8FAFC; color: #1A2A3A; font-weight: 600; line-height: 1.3;">
                            PT Teknologi Cerdas Berdaulat Indonesia
                            <input type="hidden" name="company" value="PT Teknologi Cerdas Berdaulat Indonesia">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cash Reimbursement Table -->
            <div style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <h3 style="font-weight: 700; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary);">
                        Cash Reimbursement Table
                    </h3>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">* Nominal dalam Rupiah (Rp)</span>
                </div>

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
                                    <input type="text" class="item-amount-display" placeholder="0" style="text-align: right; width: 100%; font-family: var(--font-paper);" inputmode="numeric" autocomplete="off">
                                    <input type="hidden" name="items[0][amount]" class="item-amount-raw" value="">
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid var(--border-color); font-weight: bold; background-color: rgba(230, 161, 0, 0.04);">
                            <td colspan="2" style="text-align: right; font-family: var(--font-main); font-weight: 700; font-size: 0.95rem; padding: 0.75rem;">Total:</td>
                            <td style="border: none; padding: 0.5rem 0;">
                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                    <span style="font-family: var(--font-paper); font-weight: bold; white-space: nowrap;">Rp</span>
                                    <input type="text" id="total_display" value="" readonly style="text-align: right; font-weight: bold; font-family: var(--font-paper); font-size: 1.1rem; border: none; outline: none; background: transparent; width: 100%; color: var(--primary);">
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Add/Remove Row Buttons -->
                <div class="btn-group" style="margin-top: 0.75rem;">
                    <button type="button" class="btn btn-secondary" onclick="addItemRow()" style="padding: 0.4rem 0.85rem; font-size: 0.8rem;">+ Add Item Row</button>
                    <button type="button" class="btn btn-danger" onclick="removeItemRow()" style="padding: 0.4rem 0.85rem; font-size: 0.8rem;">- Remove Row</button>
                </div>
            </div>

            <!-- INVOICE / RECEIPT UPLOAD SECTION -->
            <div style="margin-bottom: 2rem; border: 2px dashed #CBD5E1; border-radius: 8px; padding: 1.25rem; background-color: #F8FAFC;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <h4 style="font-weight: bold; font-size: 0.95rem; text-transform: uppercase; color: var(--primary); font-family: var(--font-heading);">
                        📄 Upload Invoice / Receipt (Bukti Pembayaran) <span style="color: red;">*</span>
                    </h4>
                    <span style="font-size: 0.75rem; background: #FEF3C7; color: #92400E; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 3px;">Wajib Min 1 File</span>
                </div>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 1rem;">
                    Format file: <strong>JPG, JPEG, PNG, PDF</strong> (Maks 5MB per file). Anda dapat mengunggah lebih dari satu bukti transaksi.
                </p>

                <div id="receiptsContainer" style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div class="receipt-upload-row" style="display: flex; gap: 1rem; align-items: center; background: white; padding: 0.75rem; border: 1px solid var(--border-soft); border-radius: 6px; box-shadow: var(--shadow-sm);">
                        <div style="flex: 2;">
                            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 0.2rem;">File Receipt / Invoice:</label>
                            <input type="file" name="attachments[]" accept=".jpg,.jpeg,.png,.pdf" class="input-control" onchange="previewReceiptFile(this)" style="font-size: 0.85rem;">
                        </div>
                        <div style="flex: 1;">
                            <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 0.2rem;">Tanggal Receipt:</label>
                            <input type="date" name="receipt_dates[]" value="{{ date('Y-m-d') }}" class="input-control" style="font-size: 0.85rem;">
                        </div>
                        <div class="receipt-preview-box" style="width: 50px; height: 50px; border: 1px dashed #CBD5E1; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #94A3B8; overflow: hidden; background: #FAFBFD;">
                            Preview
                        </div>
                        <button type="button" class="btn btn-danger" onclick="removeReceiptRow(this)" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">Hapus</button>
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="addReceiptRow()" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                        + Tambah Receipt Lainnya
                    </button>
                </div>
            </div>

            <!-- NOTE -->
            <div class="paper-textarea-section" style="margin-bottom: 2rem;">
                <label class="paper-textarea-label">NOTE :</label>
                <textarea name="note" class="paper-textarea-box" placeholder="Catatan tambahan mengenai reimbursement ini...">{{ old('note') }}</textarea>
            </div>

            <!-- BANK ACCOUNT DETAILS (SEARCHABLE COMBOBOX & FORMATTED REKENING) -->
            <div style="width: 100%; max-width: 500px; margin-bottom: 2.5rem;">
                <h4 style="font-weight: bold; font-size: 0.95rem; margin-bottom: 0.75rem; text-transform: uppercase; color: var(--primary); font-family: var(--font-heading);">
                    🏦 Bank Account Details
                </h4>
                <div style="border: 1px solid var(--border-color); padding: 1.25rem; background-color: #FAFBFD; border-radius: 6px; box-shadow: var(--shadow-sm);">
                    <!-- Transfer To -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">Transfer To <span style="color:red">*</span>:</label>
                        <input type="text" name="transfer_to" required placeholder="Account Holder Name" value="{{ old('transfer_to', Auth::user()->name) }}" class="input-control">
                    </div>

                    <!-- Searchable Bank Combobox -->
                    <div style="margin-bottom: 1.25rem; position: relative;">
                        <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">Pilih Bank <span style="color:red">*</span>:</label>
                        <input type="hidden" name="bank" id="bank_hidden_input" value="{{ old('bank', 'Bank Central Asia (BCA)') }}" required>
                        
                        <div class="bank-combobox-wrapper" style="position: relative;">
                            <input type="text" id="bank_search_input" placeholder="🔍 Cari bank... (contoh: BCA, Mandiri, BRI)" 
                                   value="{{ old('bank', 'Bank Central Asia (BCA)') }}" 
                                   class="input-control" autocomplete="off" style="font-weight: 600; padding-right: 2rem;">
                            <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: #888;">▼</span>

                            <div id="bank_dropdown_list" style="display: none; position: absolute; top: 100%; left: 0; right: 0; max-height: 220px; overflow-y: auto; background: white; border: 1px solid var(--border-input); border-radius: 6px; box-shadow: var(--shadow-lg); z-index: 500; margin-top: 4px;">
                                @foreach(config('banks', []) as $bank)
                                    <div class="bank-option-item" 
                                         data-code="{{ $bank['code'] }}" 
                                         data-name="{{ $bank['name'] }}" 
                                         data-lengths="{{ implode(',', (array)$bank['length']) }}"
                                         style="padding: 0.6rem 0.85rem; cursor: pointer; font-size: 0.85rem; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center; transition: background 0.15s;">
                                        <span><strong>{{ $bank['name'] }}</strong></span>
                                        <span style="font-size: 0.75rem; background: #F1F5F9; padding: 0.15rem 0.45rem; border-radius: 3px; color: var(--text-muted); font-weight: 600;">
                                            {{ implode('/', (array)$bank['length']) }} digit
                                        </span>
                                    </div>
                                @endforeach
                                <div id="bank_no_result" style="display: none; padding: 0.85rem; font-size: 0.85rem; color: var(--text-muted); text-align: center;">
                                    Bank tidak ditemukan
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Number (Digit Validation & Display Space Formatting) -->
                    <div>
                        <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">
                            Nomor Rekening <span style="color:red">*</span>:
                        </label>
                        <input type="text" id="account_number_display" placeholder="Contoh: 12345 67890" 
                               class="input-control" style="font-family: var(--font-paper); font-size: 1.05rem; font-weight: bold; letter-spacing: 1px;" 
                               inputmode="numeric" autocomplete="off" value="{{ old('account_number') }}">
                        <input type="hidden" name="account_number" id="account_number_raw" value="{{ old('account_number') }}">

                        <!-- Realtime Digit Length Indicator Badge -->
                        <div id="account_digit_info" style="margin-top: 0.45rem; font-size: 0.78rem; color: var(--text-muted); display: flex; justify-content: space-between; align-items: center;">
                            <span id="bank_rule_text">Aturan Bank: -</span>
                            <span id="digit_counter_badge" style="font-weight: bold; padding: 0.15rem 0.45rem; border-radius: 4px; background: #E2E8F0; color: #475569;">
                                0 digit
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signature Block Grid Layout (Exact 3 Columns x 2 Rows = 6 Boxes matching Gambar 2) -->
            <div class="signature-grid-3">
                <!-- Column 1 (Left Column): Requested by & Acknowledged by -->
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <!-- Box 1: Requested by -->
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
                    </div>

                    <!-- Box 2: Acknowledged by -->
                    <div class="signature-card">
                        <div class="signature-title">Acknowledged by</div>
                        <div class="signature-body">
                            &nbsp;
                        </div>
                        <div class="signature-name-field">
                            &nbsp;
                        </div>
                        <div class="signature-date">
                            Signed Date:
                        </div>
                    </div>
                </div>

                <!-- Column 2 (Middle Column): Approved by (Billy/Apriliansyah) & Checked by (FA Manager) -->
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <!-- Box 3: Approved by (Billy Gunawan / Apriliansyah) -->
                    <div class="signature-card">
                        <div class="signature-title">Approved by</div>
                        <div class="signature-body">
                            &nbsp;
                        </div>
                        <div class="signature-name-field">
                            <span id="approver_requirement_text">Billy Gunawan</span>
                        </div>
                        <div class="signature-date">
                            Signed Date:
                        </div>
                    </div>

                    <!-- Box 4: Checked by (FA Manager) -->
                    <div class="signature-card">
                        <div class="signature-title">Checked by</div>
                        <div class="signature-body">
                            &nbsp;
                        </div>
                        <div class="signature-name-field">
                            FA Manager
                        </div>
                        <div class="signature-date">
                            Signed Date:
                        </div>
                    </div>
                </div>

                <!-- Column 3 (Right Column): Approved by (Pantro Pander) & Checked by (Tung Sen) -->
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <!-- Box 5: Approved by (Pantro Pander) -->
                    <div class="signature-card">
                        <div class="signature-title">Approved by</div>
                        <div class="signature-body">
                            &nbsp;
                        </div>
                        <div class="signature-name-field">
                            Pantro Pander
                        </div>
                        <div class="signature-date">
                            Signed Date:
                        </div>
                    </div>

                    <!-- Box 6: Checked by (Tung Sen) -->
                    <div class="signature-card">
                        <div class="signature-title">Checked by</div>
                        <div class="signature-body">
                            &nbsp;
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
        </div>

        <!-- Form Submission Actions -->
        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
            <button type="submit" name="action" value="draft" class="btn btn-secondary" style="padding: 0.75rem 1.75rem; font-size: 0.95rem;">
                Save Draft
            </button>
            <button type="submit" name="action" value="submit" class="btn btn-primary" style="padding: 0.75rem 1.75rem; font-size: 0.95rem;">
                Submit Reimbursement (CRF)
            </button>
        </div>
    </div>
</form>

<script>
    let itemRowIndex = 1;
    const bankConfigData = @json(config('banks', []));

    function updateFormFromTR(selectElement) {
        const form = document.getElementById('crfForm');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        
        if (!selectedOption.value) {
            form.style.display = 'none';
            return;
        }

        const reqNo = selectedOption.getAttribute('data-reqno');
        const category = selectedOption.getAttribute('data-category');

        document.getElementById('hidden_tr_id').value = selectedOption.value;
        document.getElementById('tr_req_no_display').innerText = reqNo;
        
        const catSelect = document.getElementById('category_select');
        if (catSelect && category) {
            catSelect.value = category;
            updateApproverRequirementText(category);
        }

        form.style.display = 'block';
    }

    function updateApproverRequirementText(category) {
        const textElem = document.getElementById('approver_requirement_text');
        if (!textElem) return;
        if (category === 'Technology') {
            textElem.innerText = 'Billy Gunawan';
        } else if (category === 'Commercial') {
            textElem.innerText = 'Apriliansyah';
        } else {
            textElem.innerText = 'Billy Gunawan OR Apriliansyah';
        }
    }

    function initBankCombobox() {
        const searchInput = document.getElementById('bank_search_input');
        const hiddenInput = document.getElementById('bank_hidden_input');
        const dropdownList = document.getElementById('bank_dropdown_list');
        const optionItems = document.querySelectorAll('.bank-option-item');
        const noResult = document.getElementById('bank_no_result');

        if (!searchInput || !dropdownList) return;

        searchInput.addEventListener('focus', function() {
            dropdownList.style.display = 'block';
            filterBankOptions(this.value);
        });

        searchInput.addEventListener('input', function() {
            dropdownList.style.display = 'block';
            filterBankOptions(this.value);
        });

        function filterBankOptions(query) {
            const q = query.toLowerCase().trim();
            let hasMatch = false;

            optionItems.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                const code = item.getAttribute('data-code').toLowerCase();
                if (name.includes(q) || code.includes(q)) {
                    item.style.display = 'flex';
                    hasMatch = true;
                } else {
                    item.style.display = 'none';
                }
            });

            noResult.style.display = hasMatch ? 'none' : 'block';
        }

        optionItems.forEach(item => {
            item.addEventListener('click', function() {
                const bankName = this.getAttribute('data-name');
                searchInput.value = bankName;
                hiddenInput.value = bankName;
                dropdownList.style.display = 'none';
                updateBankRuleIndicator(bankName);
            });
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !dropdownList.contains(e.target)) {
                dropdownList.style.display = 'none';
            }
        });
    }

    function initAccountNumberFormatter() {
        const displayInput = document.getElementById('account_number_display');
        const rawInput = document.getElementById('account_number_raw');

        if (!displayInput || !rawInput) return;

        function formatDisplay(raw) {
            return raw.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
        }

        function handleInput() {
            const raw = displayInput.value.replace(/\D/g, '');
            rawInput.value = raw;
            displayInput.value = formatDisplay(raw);
            validateAccountDigitLength(raw);
        }

        displayInput.addEventListener('input', handleInput);
        displayInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const raw = pastedText.replace(/\D/g, '');
            rawInput.value = raw;
            displayInput.value = formatDisplay(raw);
            validateAccountDigitLength(raw);
        });

        if (rawInput.value) {
            displayInput.value = formatDisplay(rawInput.value.replace(/\D/g, ''));
            validateAccountDigitLength(rawInput.value.replace(/\D/g, ''));
        }
    }

    function updateBankRuleIndicator(bankName) {
        const ruleText = document.getElementById('bank_rule_text');
        if (!ruleText) return;

        let selectedBank = null;
        Object.values(bankConfigData).forEach(b => {
            if (b.name === bankName || b.code === bankName) selectedBank = b;
        });

        if (selectedBank) {
            const lengths = Array.isArray(selectedBank.length) ? selectedBank.length.join('/') : selectedBank.length;
            ruleText.innerText = `Aturan ${selectedBank.name}: ${lengths} digit`;
        } else {
            ruleText.innerText = `Aturan Bank: -`;
        }

        const rawVal = document.getElementById('account_number_raw').value;
        validateAccountDigitLength(rawVal);
    }

    function validateAccountDigitLength(rawDigits) {
        const badge = document.getElementById('digit_counter_badge');
        const bankName = document.getElementById('bank_hidden_input').value;
        if (!badge) return;

        const count = rawDigits.length;

        let selectedBank = null;
        Object.values(bankConfigData).forEach(b => {
            if (b.name === bankName || b.code === bankName) selectedBank = b;
        });

        if (selectedBank && selectedBank.length) {
            const validLengths = Array.isArray(selectedBank.length) ? selectedBank.length : [selectedBank.length];
            if (validLengths.includes(count)) {
                badge.style.background = '#ECFDF5';
                badge.style.color = '#059669';
                badge.innerText = `✓ ${count} digit (Valid)`;
            } else {
                badge.style.background = '#FEF2F2';
                badge.style.color = '#DC2626';
                badge.innerText = `${count} digit (Wajib ${validLengths.join('/')} digit)`;
            }
        } else {
            badge.style.background = '#E2E8F0';
            badge.style.color = '#475569';
            badge.innerText = `${count} digit`;
        }
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
                    <input type="text" class="item-amount-display" placeholder="0" style="text-align: right; width: 100%; font-family: var(--font-paper);" inputmode="numeric" autocomplete="off">
                    <input type="hidden" name="items[${itemRowIndex}][amount]" class="item-amount-raw" value="">
                </div>
            </td>
        `;
        tableBody.appendChild(newRow);
        
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

    function addReceiptRow() {
        const container = document.getElementById('receiptsContainer');
        const todayStr = new Date().toISOString().split('T')[0];

        const row = document.createElement('div');
        row.className = 'receipt-upload-row';
        row.style.cssText = 'display: flex; gap: 1rem; align-items: center; background: white; padding: 0.75rem; border: 1px solid var(--border-soft); border-radius: 6px; box-shadow: var(--shadow-sm);';
        row.innerHTML = `
            <div style="flex: 2;">
                <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 0.2rem;">File Receipt / Invoice:</label>
                <input type="file" name="attachments[]" accept=".jpg,.jpeg,.png,.pdf" class="input-control" onchange="previewReceiptFile(this)" style="font-size: 0.85rem;">
            </div>
            <div style="flex: 1;">
                <label style="font-weight: 600; font-size: 0.8rem; display: block; margin-bottom: 0.2rem;">Tanggal Receipt:</label>
                <input type="date" name="receipt_dates[]" value="${todayStr}" class="input-control" style="font-size: 0.85rem;">
            </div>
            <div class="receipt-preview-box" style="width: 50px; height: 50px; border: 1px dashed #CBD5E1; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #94A3B8; overflow: hidden; background: #FAFBFD;">
                Preview
            </div>
            <button type="button" class="btn btn-danger" onclick="removeReceiptRow(this)" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">Hapus</button>
        `;
        container.appendChild(row);
    }

    function removeReceiptRow(btn) {
        const rows = document.querySelectorAll('.receipt-upload-row');
        if (rows.length > 1) {
            btn.closest('.receipt-upload-row').remove();
        } else {
            alert('At least one receipt upload input is required.');
        }
    }

    function previewReceiptFile(input) {
        const row = input.closest('.receipt-upload-row');
        const previewBox = row.querySelector('.receipt-preview-box');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewBox.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
                reader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
                previewBox.innerHTML = `<span style="font-weight:bold; color:#E74C3C;">PDF</span>`;
            } else {
                previewBox.innerHTML = `File`;
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const select = document.getElementById('tr_select');
        if (select && select.value) {
            updateFormFromTR(select);
        }

        const catSelect = document.getElementById('category_select');
        if (catSelect) {
            updateApproverRequirementText(catSelect.value);
            catSelect.addEventListener('change', function() {
                updateApproverRequirementText(this.value);
            });
        }

        document.querySelectorAll('.item-amount-display').forEach(function(display) {
            const raw = display.closest('div').querySelector('.item-amount-raw');
            if (raw) initRupiahInput(display, raw, recalculateTotal);
        });

        initBankCombobox();
        initAccountNumberFormatter();

        const currentBank = document.getElementById('bank_hidden_input').value;
        if (currentBank) updateBankRuleIndicator(currentBank);
    });
</script>
@endsection
