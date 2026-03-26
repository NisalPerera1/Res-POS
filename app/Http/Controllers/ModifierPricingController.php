<?php
namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModifierPricingController extends Controller
{
    /**
     * GET /menu/items/{item}/modifier-pricing
     *
     * Returns all modifier pivot rows for this item so the frontend
     * can pre-populate the pricing matrix.
     */
    public function index(MenuItem $item)
    {
        $rows = DB::table('menu_item_modifier')
            ->where('menu_item_id', $item->id)
            ->select('modifier_id', 'pricing_type', 'custom_price', 'increment_price')
            ->get();

        return response()->json($rows);
    }

    /**
     * PATCH /menu/items/{item}/modifier-pricing
     *
     * Bulk-upserts pricing overrides for a menu item.
     * Only processes modifiers that belong to groups actually attached to this item.
     */
    public function update(Request $request, MenuItem $item)
    {
        $validated = $request->validate([
            'pricing'                       => 'required|array',
            'pricing.*.modifier_id'         => 'required|integer|exists:modifiers,id',
            'pricing.*.pricing_type'        => 'required|in:absolute,increment',
            'pricing.*.custom_price'        => 'nullable|numeric|min:0',
            'pricing.*.increment_price'     => 'nullable|numeric|min:0',
        ]);

        // Only allow modifiers that belong to groups attached to this item
        $validModifierIds = DB::table('menu_item_modifier_group as mimg')
            ->join('modifiers as m', 'm.modifier_group_id', '=', 'mimg.modifier_group_id')
            ->where('mimg.menu_item_id', $item->id)
            ->where('m.is_active', true)
            ->pluck('m.id')
            ->toArray();

        DB::transaction(function () use ($item, $validated, $validModifierIds) {
            foreach ($validated['pricing'] as $row) {
                // Skip modifiers not actually attached to this item
                if (!in_array($row['modifier_id'], $validModifierIds)) {
                    continue;
                }

                // Check if record exists
                $exists = DB::table('menu_item_modifier')
                    ->where('menu_item_id', $item->id)
                    ->where('modifier_id', $row['modifier_id'])
                    ->exists();

                if ($exists) {
                    // Update existing record
                    DB::table('menu_item_modifier')
                        ->where('menu_item_id', $item->id)
                        ->where('modifier_id', $row['modifier_id'])
                        ->update([
                            'pricing_type'    => $row['pricing_type'],
                            'custom_price'    => $row['custom_price']    ?? null,
                            'increment_price' => $row['increment_price'] ?? null,
                        ]);
                } else {
                    // Insert new record
                    DB::table('menu_item_modifier')
                        ->insert([
                            'menu_item_id'   => $item->id,
                            'modifier_id'    => $row['modifier_id'],
                            'pricing_type'    => $row['pricing_type'],
                            'custom_price'    => $row['custom_price']    ?? null,
                            'increment_price' => $row['increment_price'] ?? null,
                        ]);
                }
            }
        });

        return response()->json([
            'message' => 'Modifier pricing updated successfully',
        ]);
    }
}