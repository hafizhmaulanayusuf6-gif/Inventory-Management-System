<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SupplierController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:supplier.view', only: ['index']),
            new Middleware('permission:supplier.create', only: ['create', 'store']),
            new Middleware('permission:supplier.update', only: ['edit', 'update']),
            new Middleware('permission:supplier.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();

        $suppliers = Supplier::query()
            ->withCount('inbounds')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function create(): View
    {
        return view('suppliers.form', ['supplier' => new Supplier()]);
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->inbounds()->exists()) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', 'Supplier tidak dapat dihapus karena sudah memiliki transaksi barang masuk.');
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}