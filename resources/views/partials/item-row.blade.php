@php
    $row = is_array($row ?? null) ? $row : [];
    $selectedProduct = (int) ($row['product_id'] ?? 0);
    $qtyValue = $row['qty'] ?? 1;
    $productError = $errors->first("items.{$index}.product_id");
    $qtyError = $errors->first("items.{$index}.qty");
@endphp

<tr class="item-row">
    <td class="row-number text-muted"></td>
    <td>
        <select name="items[{{ $index }}][product_id]"
                class="form-select item-product {{ $productError ? 'is-invalid' : '' }}">
            <option value="">-- Pilih barang --</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}"
                        data-unit="{{ $product->unit->symbol }}"
                        data-stock="{{ $product->stock }}"
                        @selected($selectedProduct === $product->id)>
                    {{ $product->sku }} - {{ $product->name }}@if ($checkStock) (stok: {{ number_format($product->stock, 0, ',', '.') }})@endif
                </option>
            @endforeach
        </select>
        @if ($productError)
            <div class="text-danger small mt-1">{{ $productError }}</div>
        @endif
    </td>
    <td>
        <div class="input-group">
            <input type="number" name="items[{{ $index }}][qty]" min="1" step="1"
                   value="{{ $qtyValue }}"
                   class="form-control item-qty {{ $qtyError ? 'is-invalid' : '' }}">
            <span class="input-group-text item-unit">-</span>
        </div>
        <small class="text-muted item-stock"></small>
        @if ($qtyError)
            <div class="text-danger small mt-1">{{ $qtyError }}</div>
        @endif
    </td>
    <td>
        <button type="button" class="btn btn-icon btn-outline-danger btn-remove-item" title="Hapus baris">
            <i class="bx bx-trash"></i>
        </button>
    </td>
</tr>