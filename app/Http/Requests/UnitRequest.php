<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')->ignore($this->route('unit')),
            ],
            'symbol' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama satuan wajib diisi.',
            'name.max' => 'Nama satuan maksimal 255 karakter.',
            'name.unique' => 'Nama satuan sudah digunakan.',
            'symbol.required' => 'Simbol satuan wajib diisi.',
            'symbol.max' => 'Simbol satuan maksimal 20 karakter.',
        ];
    }
}