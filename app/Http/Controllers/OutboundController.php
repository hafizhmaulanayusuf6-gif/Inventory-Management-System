<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\StoreOutboundRequest;
use App\Models\Outbound;
use App\Models\Product;
use App\Services\OutboundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class OutboundController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:outbound.view', only: ['index', 'show']),
            new Middleware('permission:outbound.create', only: ['create', 'store']),
        ];
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $search = trim($filters['search'] ?? '');
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        $outbounds = Outbound::query()
            ->with('user:id,name') // cegah N+1
            ->withCount('items')
            ->withSum('items as total_qty', 'qty')
            ->when($search !== '', fn ($query) => $query->where('destination', 'like', "%{$search}%"))
            ->when($from, fn ($query, $value) => $query->whereDate('date', '>=', $value))
            ->when($to, fn ($query, $value) => $query->whereDate('date', '<=', $value))
            ->latest('date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('outbounds.index', compact('outbounds', 'search', 'from', 'to'));
    }

    public function create(): View
    {
        return view('outbounds.create', [
            // Hanya barang yang punya stok yang bisa dikeluarkan.
            'products' => Product::with('unit:id,symbol')
                ->where('stock', '>', 0)
                ->orderBy('name')
                ->get(['id', 'sku', 'name', 'stock', 'unit_id']),
        ]);
    }

    public function store(StoreOutboundRequest $request, OutboundService $service): RedirectResponse
    {
        try {
            $outbound = $service->create($request->validated(), $request->user()->id);
        } catch (InsufficientStockException $e) {
            // Terjadi jika stok berubah (misalnya dipakai user lain) setelah validasi.
            // Transaksi sudah di-rollback otomatis, tidak ada data yang tersimpan.
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('outbounds.show', $outbound)
            ->with('success', 'Barang keluar berhasil dicatat. Stok telah diperbarui.');
    }

    public function show(Outbound $outbound): View
    {
        $outbound->load(['user', 'items.product.unit']);

        return view('outbounds.show', compact('outbound'));
    }
}