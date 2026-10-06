<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class UnitController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:unit.view', only: ['index']),
            new Middleware('permission:unit.create', only: ['create', 'store']),
            new Middleware('permission:unit.update', only: ['edit', 'update']),
            new Middleware('permission:unit.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();

        $units = Unit::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('symbol', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('units.index', compact('units', 'search'));
    }

    public function create(): View
    {
        return view('units.form', ['unit' => new Unit()]);
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Unit $unit): View
    {
        return view('units.form', compact('unit'));
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->products()->exists()) {
            return redirect()
                ->route('units.index')
                ->with('error', 'Satuan tidak dapat dihapus karena masih dipakai oleh barang.');
        }

        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }
}