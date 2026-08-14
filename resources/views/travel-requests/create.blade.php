@extends('layouts.app')

@section('title', 'Create Travel Request')

@section('content')
<div class="document-actions-bar" style="margin-left: auto; margin-right: auto;">
    <a href="{{ route('travel-requests.index') }}" class="btn btn-secondary">&larr; Back to List</a>
    <h2 style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 700; color: var(--primary-light);">
        Filling Travel Request Form
    </h2>
</div>

<form action="{{ route('travel-requests.store') }}" method="POST" id="trfForm">
    @csrf

    <div class="paper-container">
        <div class="paper-sheet">
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
                        <div class="form-value-box" style="flex-grow: 1; background-color: #f7f7f7; color: #7f8c8d; font-style: italic;">
                            XXX/WM-YBAR (Auto-generated)
                        </div>
                    </div>
                    <div class="form-group-custom">
                        <span class="form-label-custom">Date:</span>
                        <div class="form-value-box" style="flex-grow: 1; padding: 0.1rem 0.5rem;">
                            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Right Header Column -->
                <div>
                    <div class="form-group-custom" style="margin-bottom: 1rem;">
                        <span class="form-label-custom">Category:</span>
                        <div class="form-value-box-yellow" style="flex-grow: 1; padding: 0.1rem 0.5rem;">
                            <select name="category" required style="font-weight: bold; background: transparent; border: none; width: 100%;">
                                <option value="Technology" {{ old('category') === 'Technology' ? 'selected' : '' }}>Technology</option>
                                <option value="Commercial" {{ old('category') === 'Commercial' ? 'selected' : '' }}>Commercial</option>
                                <option value="Others" {{ old('category') === 'Others' ? 'selected' : '' }}>Others</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group-custom" style="align-items: flex-start;">
                        <span class="form-label-custom" style="margin-top: 0.4rem;">Company:</span>
                        <div class="form-value-box" style="flex-grow: 1; padding: 0.4rem 0.75rem; background-color: #F8FAFC; color: #1A2A3A; font-weight: 600;">
                            PT Teknologi Cerdas Berdaulat Indonesia
                            <input type="hidden" name="company" value="PT Teknologi Cerdas Berdaulat Indonesia">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Destination Table Section -->
            <h3 style="font-weight: 700; font-size: 0.95rem; text-transform: uppercase; margin-top: 1.5rem;">Destination Table</h3>
            <table class="paper-table" id="destinationsTable">
                <thead>
                    <tr>
                        <th style="width: 50%;">Destination</th>
                        <th style="width: 25%;">From</th>
                        <th style="width: 25%;">To</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="dashed-row">
                        <td><input type="text" name="destinations[0][destination]" required placeholder="Enter destination details..."></td>
                        <td><input type="date" name="destinations[0][from]" required></td>
                        <td><input type="date" name="destinations[0][to]" required></td>
                    </tr>
                </tbody>
            </table>

            <!-- Add/Remove Row Buttons (Hidden on print) -->
            <div class="btn-group" style="margin-bottom: 2rem;">
                <button type="button" class="btn btn-secondary" onclick="addDestinationRow()" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">+ Add Row</button>
                <button type="button" class="btn btn-danger" onclick="removeDestinationRow()" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">- Remove Row</button>
            </div>

            <!-- Justification -->
            <div class="paper-textarea-section">
                <label class="paper-textarea-label">Justification</label>
                <textarea name="justification" class="paper-textarea-box" required placeholder="Enter detailed justification for travel request...">{{ old('justification') }}</textarea>
            </div>

            <!-- Benefit -->
            <div class="paper-textarea-section">
                <label class="paper-textarea-label">Benefit</label>
                <textarea name="benefit" class="paper-textarea-box" required placeholder="Enter expected benefits of the travel...">{{ old('benefit') }}</textarea>
            </div>

            <!-- Supporting Data Checkbox List (2 kolom: kiri item 1&2, kanan item 3&4) -->
            <div class="supporting-section">
                <div class="supporting-title">Supporting Datas <em>(check if applicable)</em>:</div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 2rem;">
                    <!-- Baris 1 Kiri -->
                    <div class="supporting-row">
                        <div style="flex: 1; border-bottom: 1px dotted #000000; display: flex; align-items: center; padding-bottom: 2px;">
                            <input type="text" name="supporting_label_1" value="Invitation" placeholder="Supporting Data 1" style="font-family: var(--font-main); font-size: 0.95rem; border: none; outline: none; background: transparent; width: 100%;">
                        </div>
                        <input type="checkbox" name="supporting_value_1" value="1" style="width: 20px; height: 20px; cursor: pointer; margin-left: 0.5rem;" checked>
                    </div>

                    <!-- Baris 1 Kanan -->
                    <div class="supporting-row">
                        <div style="flex: 1; border-bottom: 1px dotted #000000; display: flex; align-items: center; padding-bottom: 2px;">
                            <input type="text" name="supporting_label_3" value="" placeholder="Supporting Data 3" style="font-family: var(--font-main); font-size: 0.95rem; border: none; outline: none; background: transparent; width: 100%;">
                        </div>
                        <input type="checkbox" name="supporting_value_3" value="1" style="width: 20px; height: 20px; cursor: pointer; margin-left: 0.5rem;">
                    </div>

                    <!-- Baris 2 Kiri -->
                    <div class="supporting-row">
                        <div style="flex: 1; border-bottom: 1px dotted #000000; display: flex; align-items: center; padding-bottom: 2px;">
                            <input type="text" name="supporting_label_2" value="Travel Invitation Letter" placeholder="Supporting Data 2" style="font-family: var(--font-main); font-size: 0.95rem; border: none; outline: none; background: transparent; width: 100%;">
                        </div>
                        <input type="checkbox" name="supporting_value_2" value="1" style="width: 20px; height: 20px; cursor: pointer; margin-left: 0.5rem;" checked>
                    </div>

                    <!-- Baris 2 Kanan -->
                    <div class="supporting-row">
                        <div style="flex: 1; border-bottom: 1px dotted #000000; display: flex; align-items: center; padding-bottom: 2px;">
                            <input type="text" name="supporting_label_4" value="" placeholder="Supporting Data 4" style="font-family: var(--font-main); font-size: 0.95rem; border: none; outline: none; background: transparent; width: 100%;">
                        </div>
                        <input type="checkbox" name="supporting_value_4" value="1" style="width: 20px; height: 20px; cursor: pointer; margin-left: 0.5rem;">
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

                    <!-- Acknowledged by -->
                    <div class="signature-card">
                        <div class="signature-title">Acknowledged by</div>
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
                </div>

                <!-- Column 2: Approved by -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- Approved by (1) -->
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
                    </div>

                    <!-- Approved by (2) -->
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Submission Actions (Outside the paper block) -->
        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" name="action" value="draft" class="btn btn-secondary" style="padding: 0.8rem 2rem; font-size: 1rem;">
                Save Draft
            </button>
            <button type="submit" name="action" value="submit" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem;">
                Submit Travel Request (TRF)
            </button>
        </div>
    </div>
</form>

<script>
    let destinationRowIndex = 1;

    function addDestinationRow() {
        const tableBody = document.querySelector('#destinationsTable tbody');
        const newRow = document.createElement('tr');
        newRow.className = 'dashed-row';
        newRow.innerHTML = `
            <td><input type="text" name="destinations[${destinationRowIndex}][destination]" required placeholder="Enter destination details..."></td>
            <td><input type="date" name="destinations[${destinationRowIndex}][from]" required></td>
            <td><input type="date" name="destinations[${destinationRowIndex}][to]" required></td>
        `;
        tableBody.appendChild(newRow);
        destinationRowIndex++;
    }

    function removeDestinationRow() {
        const rows = document.querySelectorAll('#destinationsTable tbody tr');
        if (rows.length > 1) {
            rows[rows.length - 1].remove();
            destinationRowIndex--;
        } else {
            alert('At least one destination row is required.');
        }
    }
</script>
@endsection
