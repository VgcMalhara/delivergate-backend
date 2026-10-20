<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Http\Resources\InventoryItemResource;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the inventory items.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', InventoryItem::class);
        $query = InventoryItem::query();
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return InventoryItemResource::collection($query->get());
    }

    /**
     * Store a newly created inventory item in the database.
     */
    public function store(StoreInventoryItemRequest $request)
    {
        Gate::authorize('create', InventoryItem::class);
        $item = InventoryItem::create($request->validated());
        return new InventoryItemResource($item);
    }

    /**
     * Display the specified inventory item.
     */
    public function show(InventoryItem $item)
    {
        Gate::authorize('view', $item);
        return new InventoryItemResource($item);
    }

    /**
     * Update the specified inventory item in the database.
     */
    public function update(UpdateInventoryItemRequest $request, InventoryItem $item)
    {
        Gate::authorize('update', $item);
        $item->update($request->validated());
        return new InventoryItemResource($item);
    }

    /**
     * Remove the specified inventory item from the database (Soft Delete).
     */
    public function destroy(InventoryItem $item)
    {
        Gate::authorize('delete', $item);
        $item->delete();
        return response()->json(['message' => 'Item deleted successfully']);
    }
}
