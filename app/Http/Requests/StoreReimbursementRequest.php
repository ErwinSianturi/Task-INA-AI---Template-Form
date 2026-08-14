<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReimbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Enforce fixed company name & sanitize account number to raw digits
        $rawAccount = preg_replace('/\D/', '', $this->input('account_number', ''));

        $this->merge([
            'company' => 'PT Teknologi Cerdas Berdaulat Indonesia',
            'account_number' => $rawAccount,
        ]);
    }

    public function rules(): array
    {
        $isSubmitting = $this->input('action') === 'submit';
        $bankList = config('banks', []);
        $validBankNames = array_map(fn($b) => $b['name'], $bankList);

        $rules = [
            'travel_request_id' => ['nullable', 'exists:travel_requests,id'],
            'reimbursement_type' => ['required', Rule::in(['travel', 'non_travel'])],
            'category' => ['required', Rule::in(['Technology', 'Commercial', 'Others'])],
            'date' => ['required', 'date'],
            'company' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'bank' => ['required', 'string', Rule::in($validBankNames)],
            'account_number' => ['required', 'string', 'regex:/^[0-9]+$/'],
            'transfer_to' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.date' => ['required', 'date'],
            'items.*.details' => ['required', 'string'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],

            'attachments' => [$isSubmitting ? 'required_without:existing_attachments' : 'nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'receipt_dates' => ['nullable', 'array'],
            'receipt_dates.*' => ['nullable', 'date'],
        ];

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $bankName = $this->input('bank');
            $accountNumber = $this->input('account_number');

            if ($bankName && $accountNumber) {
                $bankList = config('banks', []);
                $selectedBank = null;

                foreach ($bankList as $bank) {
                    if ($bank['name'] === $bankName || $bank['code'] === $bankName) {
                        $selectedBank = $bank;
                        break;
                    }
                }

                if ($selectedBank && isset($selectedBank['length'])) {
                    $validLengths = (array) $selectedBank['length'];
                    $actualLength = strlen($accountNumber);

                    if (!in_array($actualLength, $validLengths)) {
                        $expectedStr = implode(' atau ', $validLengths);
                        $validator->errors()->add(
                            'account_number',
                            "Nomor rekening untuk {$selectedBank['name']} harus terdiri dari {$expectedStr} digit angka (Panjang saat ini: {$actualLength} digit)."
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'bank.in' => 'Bank harus dipilih dari daftar bank resmi yang tersedia.',
            'account_number.regex' => 'Nomor rekening hanya boleh berisi angka murni tanpa spasi, huruf, atau karakter khusus.',
            'attachments.required_without' => 'Setidaknya satu Invoice / Receipt (bukti pembayaran) wajib di-upload sebelum CRF disubmit.',
            'attachments.*.mimes' => 'Format file receipt harus berupa JPG, JPEG, PNG, atau PDF.',
            'attachments.*.max' => 'Ukuran file receipt tidak boleh lebih dari 5MB.',
            'category.in' => 'Category hanya boleh Technology, Commercial, atau Others.',
        ];
    }
}
