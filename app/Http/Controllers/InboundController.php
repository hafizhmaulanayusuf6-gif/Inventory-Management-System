<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInboundRequest;
use App\Models\Inbound;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\InboundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class InboundController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:inbound.view', only: ['index', 'show']),
            new Middleware('permission:inbound.create', only: ['create', 'store']),
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

        $inbounds = Inbound::query()
            ->with(['supplier:id,name', 'user:id,name']) // cegah N+1
            ->withCount('items')
            ->withSum('items as total_qty', 'qty')
            ->when($search !== '', fn ($query) => $query->where('reference_no', 'like', "%{$search}%"))
            ->when($from, fn ($query, $value) => $query->whereDate('date', '>=', $value))
            ->when($to, fn ($query, $value) => $query->whereDate('date', '<=', $value))
            ->latest('date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('inbounds.index', compact('inbounds', 'search', 'from', 'to'));
    }

    public function create(): View
    {
        return view('inbounds.create', [
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'products' => Product::with('unit:id,symbol')
                ->orderBy('name')
                ->get(['id', 'sku', 'name', 'stock', 'unit_id']),
        ]);
    }

    public function store(StoreInboundRequest $request, InboundService $service): RedirectResponse
    {
        $inbound = $service->create($request->validated(), $request->user()->id);

        return redirect()
            ->route('inbounds.show', $inbound)
            ->with('success', "Barang masuk {$inbound->reference_no} berhasil dicatat. Stok telah diperbarui.");
    }

    public function show(Inbound $inbound): View
    {
        $inbound->load(['supplier', 'user', 'items.product.unit']);

        return view('inbounds.show', compact('inbound'));
    }
}