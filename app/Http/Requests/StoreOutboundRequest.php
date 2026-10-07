<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOutboundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'destination' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:1000000'],
        ];
    }

    /**
     * Cek stok awal agar error tampil per baris. Hanya untuk UX;
     * pengecekan final yang aman dari race condition ada di StockService.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $items = $this->input('items', []);

                $products = Product::query()
                    ->whereIn('id', collect($items)->pluck('product_id'))
                    ->get(['id', 'name', 'stock'])
                    ->keyBy('id');

                foreach ($items as $index => $item) {
                    $product = $products->get((int) $item['product_id']);

                    if ($product && (int) $item['qty'] > $product->stock) {
                        $validator->errors()->add(
                            "items.{$index}.qty",
                            "Stok {$product->name} tidak cukup (tersedia {$product->stock})."
                        );
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'date.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'destination.required' => 'Tujuan pengiriman wajib diisi.',
            'destination.max' => 'Tujuan maksimal 255 karakter.',
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