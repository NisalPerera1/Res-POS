<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // ── Full Menu (POS use) ───────────────────────────────

    public function fullMenu()
    {
        $categories = Category::with([
            'menuItems' => function ($q) {
                $q->where('is_available', true)
                  ->orderBy('sort_order')
                  ->with([
                      'modifierGroups' => function ($q) {
                          $q->where('is_active', true)
                            ->orderBy('sort_order')
                            ->with([
                                'modifiers' => fn($q) => $q->where('is_active', true)->orderBy('price')
                            ]);
                      }
                  ]);
            }
        ])
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

        return response()->json($categories);
    }

    // ── Categories ────────────────────────────────────────

    public function categories()
    {
        return response()->json(
            Category::withCount('menuItems')->orderBy('sort_order')->get()
        );
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'icon'  => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
        ]);
        return response()->json(Category::create($request->all()), 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroyCategory($id)
    {
        $category = Category::findOrFail($id);
        if ($category->menuItems()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with items. Remove items first.'
            ], 422);
        }
        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }

    // ── Menu Items ────────────────────────────────────────

    public function items(Request $request)
    {
        $query = MenuItem::with([
            'category',
            'modifierGroups' => function ($q) {
                $q->orderBy('sort_order')
                  ->with(['modifiers' => fn($q) => $q->where('is_active', true)->orderBy('price')]);
            }
        ]);

        if ($request->category_id) $query->where('category_id', $request->category_id);
        if ($request->search)      $query->where('name', 'like', "%{$request->search}%");
        if ($request->available)   $query->where('is_available', true);

        return response()->json($query->orderBy('sort_order')->get());
    }

    public function showItem($id)
    {
        return response()->json(
            MenuItem::with([
                'category',
                'modifierGroups.modifiers' => fn($q) => $q->where('is_active', true)->orderBy('price')
            ])->findOrFail($id)
        );
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:100',
            'category_id'          => 'required|exists:categories,id',
            'price'                => 'required|numeric|min:0',
            'cost_price'           => 'nullable|numeric|min:0',
            'description'          => 'nullable|string|max:500',
            'icon'                 => 'nullable|string|max:10',
            'type'                 => 'nullable|in:food,beverage,dessert,other',
            'prep_time'            => 'nullable|integer|min:0',
            'is_available'         => 'boolean',
            'is_popular'           => 'boolean',
            'is_instant'           => 'boolean',
            'modifier_group_ids'   => 'nullable|array',
            'modifier_group_ids.*' => 'exists:modifier_groups,id',
        ]);

        $item = MenuItem::create($request->except('modifier_group_ids'));

        if ($request->filled('modifier_group_ids')) {
            $item->modifierGroups()->sync($request->modifier_group_ids);
        }

        return response()->json(
            $item->load(['category', 'modifierGroups.modifiers']),
            201
        );
    }

    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'name'                 => 'sometimes|string|max:100',
            'category_id'          => 'sometimes|exists:categories,id',
            'price'                => 'sometimes|numeric|min:0',
            'cost_price'           => 'nullable|numeric|min:0',
            'description'          => 'nullable|string|max:500',
            'icon'                 => 'nullable|string|max:10',
            'type'                 => 'nullable|in:food,beverage,dessert,other',
            'prep_time'            => 'nullable|integer|min:0',
            'is_available'         => 'boolean',
            'is_popular'           => 'boolean',
            'is_instant'           => 'boolean',
            'modifier_group_ids'   => 'nullable|array',
            'modifier_group_ids.*' => 'exists:modifier_groups,id',
        ]);

        $item = MenuItem::findOrFail($id);
        $item->update($request->except('modifier_group_ids'));

        // Sync modifier groups — empty array removes all
        if ($request->has('modifier_group_ids')) {
            $item->modifierGroups()->sync($request->modifier_group_ids ?? []);
        }

        return response()->json(
            $item->load(['category', 'modifierGroups.modifiers'])
        );
    }

    public function destroyItem($id)
    {
        MenuItem::findOrFail($id)->delete();
        return response()->json(['message' => 'Item deleted']);
    }

    public function toggleAvailability($id)
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['is_available' => !$item->is_available]);
        return response()->json($item);
    }

    // ── Modifier Groups ───────────────────────────────────

    public function modifierGroups()
    {
        return response()->json(
            ModifierGroup::with(['modifiers' => fn($q) => $q->orderBy('price')])
                ->orderBy('sort_order')
                ->get()
        );
    }

    public function showModifierGroup($id)
    {
        return response()->json(
            ModifierGroup::with(['modifiers' => fn($q) => $q->orderBy('price')])
                ->findOrFail($id)
        );
    }

    public function storeModifierGroup(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'is_required' => 'boolean',
            'min_select'  => 'integer|min:0',
            'max_select'  => 'integer|min:1',
            'sort_order'  => 'integer|min:0',
        ]);

        $group = ModifierGroup::create($request->all());
        return response()->json($group->load('modifiers'), 201);
    }

    public function updateModifierGroup(Request $request, $id)
    {
        $request->validate([
            'name'        => 'sometimes|string|max:100',
            'is_required' => 'boolean',
            'min_select'  => 'integer|min:0',
            'max_select'  => 'integer|min:1',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $group = ModifierGroup::findOrFail($id);
        $group->update($request->all());
        return response()->json($group->load('modifiers'));
    }

    public function destroyModifierGroup($id)
    {
        $group = ModifierGroup::findOrFail($id);
        // Detach from all menu items first
        $group->menuItems()->detach();
        $group->modifiers()->delete();
        $group->delete();
        return response()->json(['message' => 'Modifier group deleted']);
    }

    // ── Modifiers ─────────────────────────────────────────

    public function storeModifier(Request $request)
    {
        $request->validate([
            'modifier_group_id' => 'required|exists:modifier_groups,id',
            'name'              => 'required|string|max:100',
            'price'             => 'nullable|numeric|min:0',
            'is_active'         => 'boolean',
        ]);

        $group    = ModifierGroup::findOrFail($request->modifier_group_id);
        $modifier = Modifier::create([
            'modifier_group_id' => $request->modifier_group_id,
            'group_name'        => $group->name,
            'name'              => $request->name,
            'price'             => $request->price ?? 0,
            'is_active'         => $request->boolean('is_active', true),
        ]);

        return response()->json($modifier, 201);
    }

    public function updateModifier(Request $request, $id)
    {
        $request->validate([
            'name'      => 'sometimes|string|max:100',
            'price'     => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $modifier = Modifier::findOrFail($id);
        $modifier->update($request->all());
        return response()->json($modifier);
    }

    public function destroyModifier($id)
    {
        Modifier::findOrFail($id)->delete();
        return response()->json(['message' => 'Modifier deleted']);
    }
}