<?php
namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Order;
use App\Events\TableStatusUpdated;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::with(['currentOrder.items.menuItem'])
            ->orderBy('sort_order')
            ->get();

        return response()->json($tables);
    }

    public function show($id)
    {
        $table = Table::with(['currentOrder.items.menuItem', 'currentOrder.payments'])
            ->findOrFail($id);

        return response()->json($table);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:50',
            'section'  => 'nullable|string|max:50',
            'capacity' => 'integer|min:1|max:20',
        ]);

        $table = Table::create($request->all());
        return response()->json($table, 201);
    }

    public function update(Request $request, $id)
    {
        $table = Table::findOrFail($id);
        $table->update($request->all());
        return response()->json($table);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:free,occupied,reserved,cleaning',
        ]);

        $table = Table::findOrFail($id);
        $table->update(['status' => $request->status]);

        broadcast(new TableStatusUpdated($table))->toOthers();

        return response()->json($table);
    }

    /**
     * Update customer information for a table
     */
    public function updateCustomer(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:500',
        ]);

        $table = Table::findOrFail($id);
        $table->update($request->only(['customer_name', 'notes']));

        return response()->json($table);
    }

    /**
     * Transfer order from one table to another
     */
    public function transfer(Request $request, $id)
    {
        $request->validate([
            'target_table_id' => 'required|exists:tables,id',
        ]);

        $source = Table::findOrFail($id);
        $target = Table::findOrFail($request->target_table_id);

        if (!$target->isFree()) {
            return response()->json(['message' => 'Target table is not free'], 422);
        }

        if ($source->current_order_id) {
            $order = Order::findOrFail($source->current_order_id);
            $order->update(['table_id' => $target->id]);

            $target->update([
                'status'           => 'occupied',
                'current_order_id' => $order->id,
            ]);

            $source->update([
                'status'           => 'free',
                'current_order_id' => null,
            ]);

            broadcast(new TableStatusUpdated($source))->toOthers();
            broadcast(new TableStatusUpdated($target))->toOthers();
        }

        return response()->json(['message' => 'Table transferred successfully']);
    }

    public function destroy($id)
    {
        $table = Table::findOrFail($id);

        if ($table->status !== 'free') {
            return response()->json(['message' => 'Cannot delete an occupied table'], 422);
        }

        $table->delete();
        return response()->json(['message' => 'Table deleted']);
    }
}