<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Imports\MenuItemImport;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
                                'modifiers' => function($q) {
                                    $q->where('is_active', true)
                                      ->orderBy('price');
                                }
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

    // ── Modifier Pricing Helpers ──────────────────────────

    /**
     * GET /menu/items/{id}/modifiers
     *
     * Returns only the active modifiers belonging to groups
     * that are actually attached to this menu item.
     * Previously returned ALL modifiers in the system — fixed.
     */
    public function itemModifiers($id)
    {
        $item = MenuItem::with([
            'modifierGroups.modifiers' => fn($q) => $q->where('is_active', true)->orderBy('price')
        ])->findOrFail($id);

        // Flatten all active modifiers from all attached groups
        $modifiers = $item->modifierGroups
            ->flatMap(fn($group) => $group->modifiers)
            ->values();

        return response()->json($modifiers);
    }

    /**
     * POST /menu/items/upload-image
     * Upload image for menu item
     */
    public function uploadItemImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/menu_items'), $filename);
            
            return response()->json([
                'success' => true,
                'filename' => $filename,
                'url' => '/storage/menu_items/' . $filename
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    /**
     * POST /menu/items/bulk-import
     * Bulk import menu items from Excel file
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->storeAs('imports', 'menu_items_' . time() . '.' . $file->getClientOriginalExtension());

            $fullPath = Storage::path($filePath);
            $import = new MenuItemImport($fullPath);
            $result = $import->import();

            // Clean up the file
            Storage::delete($filePath);

            return response()->json($result);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    /**
     * GET /menu/items/import-template
     * Download import template
     */
    public function downloadImportTemplate()
    {
        $csvContent = "Category,Name,Description,Price,Cost Price,SKU,Type,Is Available,Is Popular,Is Instant,Prep Time,Sort Order,Icon\n";
        $csvContent .= "Burgers,Classic Burger,Juicy beef patty with fresh vegetables,12.99,8.50,BURG001,food,true,false,false,15,1,🍔\n";
        $csvContent .= "Burgers,Cheese Burger,Beef patty with melted cheese,14.99,10.00,BURG002,food,true,false,false,15,2,🍔\n";
        $csvContent .= "Beverages,Cola,Refreshing cola drink,2.99,1.00,BEV001,beverage,true,false,false,2,1,🥤\n";
        $csvContent .= "Beverages,Orange Juice,Fresh orange juice,3.99,2.00,BEV002,beverage,true,false,false,2,2,🧃\n";
        $csvContent .= "Desserts,Chocolate Cake,Rich chocolate cake,6.99,4.00,DES001,dessert,true,false,false,5,1,🍰\n";

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="menu_items_import_template.csv"');
    }
}