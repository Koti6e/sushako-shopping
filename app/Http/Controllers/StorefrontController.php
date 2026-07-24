<?php

namespace App\Http\Controllers;

use App\Support\ProductCatalog;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function home()
    {
        return view('welcome', [
            'banners' => ProductCatalog::banners(),
            'categories' => ProductCatalog::categories(),
            'products' => ProductCatalog::products()->take(8),
            'product' => ProductCatalog::featuredProduct(),
            'reviews' => ProductCatalog::reviews(),
            'departments' => ProductCatalog::departments(),
            'localProducts' => ProductCatalog::localProducts(),
        ]);
    }

    public function shop()
    {
        return view('shop.index', [
            'products' => ProductCatalog::products(),
            'categories' => ProductCatalog::categories(),
            'departments' => ProductCatalog::departments(),
            'categoryNavigation' => ProductCatalog::categoryNavigation(),
            'filterOptions' => ProductCatalog::filterOptions(),
            'title' => 'Shop - Sushako Shopping',
            'heading' => 'Shop All Products',
            'lede' => 'Explore the Sushako catalog with electronics, fashion, home, grocery and beauty essentials.',
        ]);
    }

    public function department(string $slug)
    {
        $department = ProductCatalog::department($slug);
        abort_unless($department, 404);

        return view('shop.index', [
            'products' => ProductCatalog::productsForDepartment($slug),
            'categories' => ProductCatalog::categories(),
            'departments' => ProductCatalog::departments(),
            'categoryNavigation' => ProductCatalog::categoryNavigation($slug),
            'filterOptions' => ProductCatalog::filterOptions($slug),
            'title' => $department['name'].' - Sushako Shopping',
            'heading' => $department['name'],
            'lede' => $department['description'],
            'activeDepartment' => $department,
        ]);
    }

    public function category(string $slug)
    {
        return $this->department($slug);
    }

    public function search(Request $request)
    {
        $query = $request->string('q')->toString();

        return view('shop.index', [
            'products' => ProductCatalog::search($query),
            'categories' => ProductCatalog::categories(),
            'departments' => ProductCatalog::departments(),
            'categoryNavigation' => ProductCatalog::categoryNavigation(),
            'filterOptions' => ProductCatalog::filterOptions(),
            'title' => 'Search - Sushako Shopping',
            'heading' => $query ? 'Search results for "'.$query.'"' : 'Search Products',
            'lede' => 'Search across the full Sushako catalog.',
            'query' => $query,
        ]);
    }

    public function product(string $slug)
    {
        $product = ProductCatalog::productBySlug($slug);
        abort_unless($product, 404);

        return view('products.show', [
            'product' => $product,
            'relatedProducts' => ProductCatalog::products(),
        ]);
    }
}
