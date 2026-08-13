<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTravelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // We will handle authorization via Policies
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'company' => ['required', 'string', 'max:255'],
            'justification' => ['required', 'string'],
            'benefit' => ['required', 'string'],
            'supporting_invitation' => ['nullable', 'boolean'],
            'supporting_custom' => ['nullable', 'boolean'],
            
            'supporting_label_1' => ['nullable', 'string', 'max:255'],
            'supporting_label_2' => ['nullable', 'string', 'max:255'],
            'supporting_label_3' => ['nullable', 'string', 'max:255'],
            'supporting_label_4' => ['nullable', 'string', 'max:255'],
            'supporting_value_1' => ['nullable', 'boolean'],
            'supporting_value_2' => ['nullable', 'boolean'],
            'supporting_value_3' => ['nullable', 'boolean'],
            'supporting_value_4' => ['nullable', 'boolean'],

            'destinations' => ['required', 'array', 'min:1'],
            'destinations.*.destination' => ['required', 'string', 'max:255'],
            'destinations.*.from' => ['required', 'date'],
            'destinations.*.to' => ['required', 'date'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $destinations = $this->input('destinations', []);
            foreach ($destinations as $index => $dest) {
                $from = $dest['from'] ?? null;
                $to = $dest['to'] ?? null;
                if ($from && $to) {
                    if (strtotime($to) < strtotime($from)) {
                        $validator->errors()->add("destinations.{$index}.to", "Tanggal To tidak boleh lebih awal dari tanggal From.");
                    }
                }
            }
        });
    }
}
