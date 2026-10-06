<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:product.view', only: ['index']),
            new Middleware('permission:product.create', only: ['create', 'store']),
            new Middleware('permission:product.update', only: ['edit', 'update']),
            new Middleware('permission:product.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $categoryId = $request->integer('category_id');

        $products = Product::query()
            ->with(['category:id,name', 'unit:id,name,symbol']) // cegah N+1
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($categoryId > 0, fn($query) => $query->where('category_id', $categoryId))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('products.index', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function create(): View
    {
        return view('products.form', [
            'product' => new Product(),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'units' => Unit::orderBy('name')->get(['id', 'name', 'symbol']),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        // 'stock' tidak ada di validated() maupun $fillable, jadi selalu mulai dari 0.
        Product::create($request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', 'Barang berhasil ditambahkan. Stok awal 0, tambahkan lewat Barang Masuk.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'units' => Unit::orderBy('name')->get(['id', 'name', 'symbol']),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->stock > 0) {
            return redirect()
                ->route('products.index')
                ->with('error', 'Barang tidak dapat dihapus karena masih memiliki stok.');
        }

        if ($product->stockMovements()->exists()) {
            return redirect()
                ->route('products.index')
                ->with('error', 'Barang tidak dapat dihapus karena sudah memiliki riwayat kartu stok.');
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Barang berhasil dihapus.');
    }
}
