@php
    $rows = empty($rows) ? [['product_id' => '', 'qty' => 1]] : $rows;
    $nextIndex = collect(array_keys($rows))->map(fn ($key) => (int) $key)->max() + 1;
@endphp

<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>Barang</th>
                <th style="width: 240px;">Jumlah</th>
                <th style="width: 60px;"></th>
            </tr>
        </thead>
        <tbody id="item-rows">
            @foreach ($rows as $index => $row)
                @include('partials.item-row', [
                    'index' => $index,
                    'row' => $row,
                    'products' => $products,
                    'checkStock' => $checkStock,
                ])
            @endforeach
        </tbody>
    </table>
</div>

<div class="px-4 pb-3">
    <button type="button" id="btn-add-item" class="btn btn-outline-primary btn-sm">
        <i class="bx bx-plus me-1"></i> Tambah Barang
    </button>
</div>

{{-- Cetakan baris baru. __INDEX__ diganti angka unik oleh JavaScript. --}}
<script type="text/html" id="item-row-template">
    @include('partials.item-row', [
        'index' => '__INDEX__',
        'row' => [],
        'products' => $products,
        'checkStock' => $checkStock,
    ])
</script>

@push('scripts')
    <script>
        $(function () {
            const checkStock = @json($checkStock);
            const $rows = $('#item-rows');
            const template = $('#item-row-template').html();
            let nextIndex = {{ $nextIndex }};

            // Perbarui satuan dan info stok pada satu baris.
            function syncRow($tr) {
                const $option = $tr.find('.item-product option:selected');
                const hasProduct = $option.val() !== '';
                const $qty = $tr.find('.item-qty');
                const $info = $tr.find('.item-stock');

                $tr.find('.item-unit').text(hasProduct ? $option.data('unit') : '-');

                if (checkStock && hasProduct) {
                    const stock = parseInt($option.data('stock'), 10);
                    $qty.attr('max', stock);
                    $info.text('Stok tersedia: ' + stock);
                } else {
                    $qty.removeAttr('max');
                    $info.text('');
                }
            }

            // Nomor baris, tombol hapus, dan cegah barang yang sama dipilih dua kali.
            function syncAll() {
                const chosen = $rows.find('.item-product')
                    .map(function () { return $(this).val(); })
                    .get()
                    .filter(Boolean);

                $rows.find('.item-product').each(function () {
                    const own = $(this).val();

                    $(this).find('option').each(function () {
                        const value = $(this).val();
                        $(this).prop('disabled', value !== '' && value !== own && chosen.includes(value));
                    });
                });

                $rows.find('.item-row').each(function (i) {
                    $(this).find('.row-number').text(i + 1);
                });

                $rows.find('.btn-remove-item').prop('disabled', $rows.find('.item-row').length === 1);
            }

            $('#btn-add-item').on('click', function () {
                $rows.append(template.replace(/__INDEX__/g, nextIndex++));

                const $tr = $rows.find('.item-row').last();
                syncRow($tr);
                syncAll();
                $tr.find('.item-product').trigger('focus');
            });

            $rows.on('change', '.item-product', function () {
                syncRow($(this).closest('tr'));
                syncAll();
            });

            $rows.on('click', '.btn-remove-item', function () {
                if ($rows.find('.item-row').length > 1) {
                    $(this).closest('tr').remove();
                    syncAll();
                }
            });

            // Cegah klik ganda yang bisa membuat dokumen tercatat dua kali.
            $('#transaction-form').on('submit', function () {
                $(this).find('button[type="submit"]').prop('disabled', true);
            });

            // Tombol Back browser: aktifkan lagi tombol simpan.
            $(window).on('pageshow', function () {
                $('#transaction-form').find('button[type="submit"]').prop('disabled', false);
            });

            $rows.find('.item-row').each(function () { syncRow($(this)); });
            syncAll();
        });
    </script>
@endpush