<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReimbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'travel_request_id' => ['nullable', 'exists:travel_requests,id'],
            'reimbursement_type' => ['required', 'in:travel,non_travel'],
            'category' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'company' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'bank' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'transfer_to' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.date' => ['required', 'date'],
            'items.*.details' => ['required', 'string'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
