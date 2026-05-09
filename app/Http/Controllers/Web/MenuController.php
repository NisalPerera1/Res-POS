<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;

class MenuController extends Controller
{
    public function index()
    {
        $categories = Category::with([
            'menuItems' => function ($q) {
                $q->where('is_available', true)
                  ->orderBy('sort_order');
            }
        ])
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

        $featuredItems = MenuItem::where('is_available', true)
            ->where('is_popular', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        return view('menu.index', compact('categories', 'featuredItems'));
    }

    public function show($id)
    {
        $item = MenuItem::with([
            'category',
            'modifierGroups.modifiers' => fn($q) => $q->where('is_active', true)->orderBy('price')
        ])->findOrFail($id);

        $relatedItems = MenuItem::where('category_id', $item->category_id)
            ->where('id', '!=', $item->id)
            ->where('is_available', true)
            ->take(4)
            ->get();

        return view('menu.show', compact('item', 'relatedItems'));
    }
}
