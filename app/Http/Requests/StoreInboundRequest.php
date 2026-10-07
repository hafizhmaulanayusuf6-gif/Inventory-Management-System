<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInboundRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi ditangani middleware permission di controller.
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->reference_no)) {
            $reference = strtoupper(trim($this->reference_no));

            $this->merge(['reference_no' => $reference === '' ? null : $reference]);
        }
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'reference_no' => ['nullable', 'string', 'max:50', 'unique:inbounds,reference_no'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:1000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'date.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier tidak valid.',
            'reference_no.max' => 'Nomor referensi maksimal 50 karakter.',
            'reference_no.unique' => 'Nomor referensi sudah pernah digunakan.',
            'items.required' => 'Minimal satu barang harus diisi.',
            'items.min' => 'Minimal satu barang harus diisi.',
            'items.max' => 'Maksimal 100 baris barang per dokumen.',
            'items.*.product_id.required' => 'Barang wajib dipilih.',
            'items.*.product_id.distinct' => 'Barang tidak boleh dipilih lebih dari sekali.',
            'items.*.product_id.exists' => 'Barang tidak valid.',
            'items.*.qty.required' => 'Jumlah wajib diisi.',
            'items.*.qty.integer' => 'Jumlah harus berupa bilangan bulat.',
            'items.*.qty.min' => 'Jumlah minimal 1.',
            'items.*.qty.max' => 'Jumlah terlalu besar.',
        ];
    }
}