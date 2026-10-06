<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // SKU diseragamkan: tanpa spasi tepi dan huruf kapital.
        if ($this->has('sku') && is_string($this->sku)) {
            $this->merge(['sku' => strtoupper(trim($this->sku))]);
        }
    }

    public function rules(): array
    {
        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'min_stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'SKU wajib diisi.',
            'sku.max' => 'SKU maksimal 50 karakter.',
            'sku.unique' => 'SKU sudah digunakan barang lain.',
            'name.required' => 'Nama barang wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'unit_id.required' => 'Satuan wajib dipilih.',
            'unit_id.exists' => 'Satuan tidak valid.',
            'min_stock.required' => 'Stok minimum wajib diisi.',
            'min_stock.integer' => 'Stok minimum harus berupa bilangan bulat.',
            'min_stock.min' => 'Stok minimum tidak boleh negatif.',
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh negatif.',
        ];
    }
}