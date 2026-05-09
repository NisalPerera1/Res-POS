<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class QrMenuController extends Controller
{
    /**
     * QR entrypoint (public).
     * GET /menu?table={id}
     */
    public function show(Request $request)
    {
        $tableId = $request->query('table');

        if (! $tableId) {
            return response()->json(['message' => 'Missing required query param: table'], 422);
        }

        $table = Table::findOrFail($tableId);

        // Reuse your existing POS menu builder to keep behavior consistent.
        $menuResponse = app(\App\Http\Controllers\MenuController::class)->fullMenu();
        $menu = $menuResponse->getData(true);

        return response()->json([
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
                'section' => $table->section,
                'capacity' => $table->capacity,
                'status' => $table->status,
            ],
            'menu' => $menu,
            'order_api' => [
                'create' => url('/api/qr/orders'),
            ],
        ]);
    }
}

