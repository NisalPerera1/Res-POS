<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Modifier;
use Illuminate\Http\Request;

class MenuController extends Controller
{

    public function fullMenu()
    {
        $categories = Category::with(['menuItems.modifiers'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    // ---- Categories ----

    public function categories()
    {
        return response()->json(Category::orderBy('sort_order')->get());
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $category = Category::create($request->all());
        return response()->json($category, 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroyCategory($id)
    {
        Category::findOrFail($id)->delete();
        return response()->json(['message' => 'Category deleted']);
    }

    // ---- Menu Items ----

    public function items(Request $request)
    {
        $query = MenuItem::with(['category', 'modifiers']);

        if ($request->category_id) $query->where('category_id', $request->category_id);
        if ($request->search)      $query->where('name', 'like', "%{$request->search}%");
        if ($request->available)   $query->where('is_available', true);

        return response()->json($query->orderBy('sort_order')->get());
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
        ]);

        $item = MenuItem::create($request->except('modifiers'));

        if ($request->modifiers) {
            $item->modifiers()->sync($request->modifiers);
        }

        return response()->json($item->load(['category', 'modifiers']), 201);
    }

    public function updateItem(Request $request, $id)
    {
        $item = MenuItem::findOrFail($id);
        $item->update($request->except('modifiers'));

        if ($request->has('modifiers')) {
            $item->modifiers()->sync($request->modifiers);
        }

        return response()->json($item->load(['category', 'modifiers']));
    }

    public function toggleAvailability($id)
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['is_available' => !$item->is_available]);
        return response()->json($item);
    }

    public function destroyItem($id)
    {
        MenuItem::findOrFail($id)->delete();
        return response()->json(['message' => 'Menu item deleted']);
    }

    // ---- Modifiers ----

    public function modifiers()
    {
        return response()->json(Modifier::where('is_active', true)->get());
    }

    public function storeModifier(Request $request)
    {
        $request->validate([
            'group_name' => 'required|string',
            'name'       => 'required|string',
            'price'      => 'numeric|min:0',
        ]);
        return response()->json(Modifier::create($request->all()), 201);
    }
}