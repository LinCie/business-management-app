<?php

namespace App\Http\Controllers;

use App\Models\Purchase; // Ensure you import the Purchase model
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\InventoryHistory;
use App\Models\InboundRequest;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    // Method to show all purchases
    public function index()
    {
        $purchases = Purchase::all(); // Retrieve all purchase records
        return view('purchases.index', compact('purchases')); // Pass data to the view
    }

    // You can add other methods here for creating, updating, deleting purchases as needed
	// Show form to create a new purchase
    public function create()
	{
	    $warehouses = Warehouse::all();
		$products = Product::all();
		return view('purchases.create', compact('products', 'warehouses'));
	}


    // Store the new purchase
	public function store(Request $request)
	{
		$request->validate([
			'supplier_name' => 'required',
			'purchase_date' => 'required|date',
			'warehouse_id' => 'required|exists:warehouses,id',
			'products.*.product_id' => 'required|exists:products,id',
			'products.*.quantity' => 'required|numeric|min:1',
			'products.*.buying_price' => 'required|numeric|min:0',
		]);

		$totalAmount = 0;
		foreach ($request->products as $productData) {
			$totalAmount += $productData['quantity'] * $productData['buying_price'];
		}

		$purchase = Purchase::create([
			'supplier_name' => $request->supplier_name,
			'purchase_date' => $request->purchase_date,
			'warehouse_id' => $request->warehouse_id,
			'total_amount' => $totalAmount,
			'status' => 'Planned',
		]);

		foreach ($request->products as $productData) {
			$purchase->products()->attach($productData['product_id'], [
				'quantity' => $productData['quantity'],
				'buying_price' => $productData['buying_price'],
				'total_cost' => $productData['quantity'] * $productData['buying_price']
			]);
		}

		return redirect()->route('purchases.index')->with('success', 'Purchase created successfully.');
	}


	public function show($id)
	{
		$purchase = Purchase::with(['products', 'warehouse', 'inboundRequests'])->findOrFail($id);
		return view('purchases.show', compact('purchase'));
	}

	
	// Show form to edit a specific purchase
	public function edit($id)
	{
		$purchase = Purchase::with('products')->findOrFail($id);
		$warehouses = Warehouse::all();
		$products = Product::all(); // Fetch all available products

		return view('purchases.edit', compact('purchase', 'warehouses', 'products'));
	}


	public function update(Request $request, $id)
	{
		$request->validate([
			'supplier_name' => 'required',
			'purchase_date' => 'required|date',
			'warehouse_id' => 'required|exists:warehouses,id',
			'products.*.product_id' => 'required|exists:products,id',
			'products.*.quantity' => 'required|numeric|min:1',
			'products.*.buying_price' => 'required|numeric|min:0',
		]);

		$purchase = Purchase::findOrFail($id);
		$purchase->update([
			'supplier_name' => $request->supplier_name,
			'purchase_date' => $request->purchase_date,
			'warehouse_id' => $request->warehouse_id,
			'notes' => $request->notes,
		]);

		$totalAmount = 0;

		$productQuantities = [];
		foreach ($request->products as $product) {
			$quantity = $product['quantity'];
			$buyingPrice = $product['buying_price'];
			$totalCost = $quantity * $buyingPrice;

			$productQuantities[$product['product_id']] = [
				'quantity' => $quantity,
				'buying_price' => $buyingPrice,
				'total_cost' => $totalCost,
			];

			$totalAmount += $totalCost;
		}

		// Sync products with updated pivot data
		$purchase->products()->sync($productQuantities);

		// Update total amount
		$purchase->total_amount = $totalAmount;
		$purchase->save();

		return redirect()->route('purchases.index')
			->with('success', 'Purchase updated successfully.');
	}


	
	// Delete a specific purchase
	public function destroy($id)
	{
		$purchase = Purchase::findOrFail($id);
		$purchase->delete();

		return redirect()->route('purchases.index')
						 ->with('success', 'Purchase deleted successfully.');
	}
	
	
	public function updateStatus(Request $request, $id)
	{
		$request->validate([
			'status' => 'required',
		]);

		$purchase = Purchase::with('products')->findOrFail($id);
		$oldStatus = $purchase->status;
		$purchase->status = $request->status;
		$purchase->save();

		// Only create an inbound request if status changes from Planned to In Transit
		if ($oldStatus === 'Planned' && $purchase->status === 'In Transit') {
			// Manually build the requested quantities as an associative array with string keys
			$requestedQuantities = [];
			foreach ($purchase->products as $product) {
				$requestedQuantities[$product->id] = $product->pivot->quantity;
			}
			
			InboundRequest::create([
				'purchase_order_id' => $purchase->id,
				'warehouse_id' => $purchase->warehouse_id,
				'requested_quantities' => $requestedQuantities, // Convert associative array to JSON
				'received_quantities' => [], // Start with an empty JSON array for received quantities
				'status' => 'In Transit',
				'notes' => 'Inbound request created upon status change to In Transit',
			]);
		}

		return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully.');
	}



	
	
	public function transferToWarehouse($purchaseId)
	{
		$purchase = Purchase::find($purchaseId);
		$warehouse = Warehouse::find($purchase->warehouse_id); // Get the assigned warehouse

		if ($warehouse && $purchase) {
			// Transfer logic (moving quantities to the warehouse)
			foreach ($purchase->products as $product) {
				$warehouse->products()->attach($product->id, ['quantity' => $product->pivot->quantity]);
			
				// Log the transfer in inventory history
				InventoryHistory::create([
					'product_id' => $product->id,
					'warehouse_id' => $warehouse->id,
					'quantity' => $product->pivot->quantity,
					'transaction_type' => 'Purchase',
					'notes' => 'Transferred from purchase order ' . $purchase->id,
				]);
			}

			// Mark purchase as transferred
			$purchase->is_transferred = true;
			$purchase->save();

			return redirect()->route('purchases.index')->with('success', 'Products transferred to warehouse successfully.');
		} else {
			return redirect()->route('purchases.index')->with('error', 'Warehouse not assigned or Purchase not found.');
		}
	}


	
	public function storeTransfer(Request $request, $id)
	{
		$request->validate([
			'warehouse_id' => 'required|exists:warehouses,id',
			'products' => 'required|array',
			'products.*' => 'required|integer|min:0',
		]);

		$purchase = Purchase::findOrFail($id);
		$warehouseId = $request->warehouse_id;

		foreach ($request->products as $productId => $quantity) {
			if ($quantity > 0) {
				$purchaseProduct = $purchase->products()->find($productId);
				if ($purchaseProduct && $purchaseProduct->pivot->quantity >= $quantity) {
					// Reduce quantity from purchase
					$purchaseProduct->pivot->quantity -= $quantity;
					$purchaseProduct->pivot->save();

					// Add quantity to warehouse inventory
					$productInWarehouse = $purchaseProduct->warehouses()->where('warehouse_id', $warehouseId)->first();
					if ($productInWarehouse) {
						// Update quantity if product already exists in the warehouse
						$productInWarehouse->pivot->quantity += $quantity;
						$productInWarehouse->pivot->save();
					} else {
						// Create new entry if product doesn't exist in the warehouse
						$purchaseProduct->warehouses()->attach($warehouseId, ['quantity' => $quantity]);
					}
				}
			}
		}

		return redirect()->route('inventory.index')->with('success', 'Products transferred to warehouse successfully.');
	}


}