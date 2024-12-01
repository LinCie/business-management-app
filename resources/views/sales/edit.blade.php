@extends('layouts.app')

@section('content')
    <h1>Edit Sales Order</h1>

    <form action="{{ route('sales.update', $sale->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="customer_id">Select Customer</label>
            <select name="customer_id" id="customer_id" class="form-control" required>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="form-group">
            <label for="sale_date">Sale Date</label>
            <input type="date" name="sale_date" class="form-control" value="{{ $sale->sale_date }}" required {{ $sale->status != 'Planned' ? 'readonly' : '' }}>
        </div>
        
        <div class="form-group">
            <label for="warehouse_id">Select Warehouse</label>
            <select name="warehouse_id" class="form-control {{ $sale->status != 'Planned' ? 'readonly-select' : '' }}" required {{ $sale->status != 'Planned' ? 'readonly' : '' }}>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ $sale->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="customer_notes">Customer Notes</label>
            <textarea name="customer_notes" class="form-control">{{ $sale->customer_notes }}</textarea>
        </div>

        <div class="form-group">
            <label for="admin_notes">Admin Notes</label>
            <textarea name="admin_notes" class="form-control">{{ $sale->admin_notes }}</textarea>
        </div>

        <h3>Products</h3>
        <div id="product-section">
            @foreach ($sale->products as $index => $product)
                <div class="form-group">
                    <label for="products[{{ $index }}][product_id]">Product</label>
                    <select name="products[{{ $index }}][product_id]" class="form-control {{ $sale->status != 'Planned' ? 'readonly-select' : '' }}" required {{ $sale->status != 'Planned' ? 'readonly' : '' }}>
                        @foreach ($products as $availableProduct)
                            <option value="{{ $availableProduct->id }}" {{ $product->id == $availableProduct->id ? 'selected' : '' }}>
                                {{ $availableProduct->name }} - ${{ $availableProduct->price }}
                            </option>
                        @endforeach
                    </select>
                    <label for="products[{{ $index }}][quantity]">Quantity</label>
                    <input type="number" name="products[{{ $index }}][quantity]" class="form-control" value="{{ $product->pivot->quantity }}" required {{ $sale->status != 'Planned' ? 'readonly' : '' }}>
                    <label for="products[{{ $index }}][price]">Price</label>
                    <input type="number" step="0.01" name="products[{{ $index }}][price]" class="form-control" value="{{ $product->pivot->price }}" required {{ $sale->status != 'Planned' ? 'readonly' : '' }}>
                    <label for="products[{{ $index }}][note]">Note</label>
                    <textarea name="products[{{ $index }}][note]" class="form-control" {{ $sale->status != 'Planned' ? 'readonly' : '' }}>{{ $product->pivot->note }}</textarea>
                </div>
            @endforeach
        </div>

        <button type="button" id="add-product" class="btn btn-secondary mb-3">Add Another Product</button>

        <h3>Expedition Details</h3>
        <div class="form-group">
            <label for="expedition_id">Expedition</label>
            <select name="expedition_id" class="form-control {{ $sale->status != 'Planned' ? 'readonly-select' : '' }}" required {{ $sale->status != 'Planned' ? 'readonly' : '' }}>
                @foreach($expeditions as $expedition)
                    <option value="{{ $expedition->id }}" {{ $sale->expedition_id == $expedition->id ? 'selected' : '' }}>
                        {{ $expedition->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="estimated_shipping_fee">Estimated Shipping Fee</label>
            <input type="number" name="estimated_shipping_fee" class="form-control" value="{{ $sale->estimated_shipping_fee ?? 0 }}" required {{ $sale->status != 'Planned' ? 'readonly' : '' }}>
        </div>

        <h3>Complaint Details (if any)</h3>
        <div class="form-group">
            <label for="complaint_details">Complaint Details</label>
            <textarea name="complaint_details" class="form-control">{{ $sale->complaint_details }}</textarea>
        </div>

		<!-- Status Display -->
		<h3>Status Display</h3>
        <div class="form-group">
            <label for="status">Sales Status</label>
            <input type="text" class="form-control" name="status" value="{{ $sale->status }}" readonly>
        </div>

        @if ($sale->status != 'Planned' && $sale->status != 'Unpaid')
            @if (count($outboundRequests) > 0)
                @foreach($outboundRequests as $outboundRequest)
                    <div class="form-group">
                        <label for="status_outbound">Outbound Status</label>
                        <input type="text" class="form-control" value="{{ $outboundRequest->status }}" readonly>
                    </div>

                    <!-- Customer Received Quantities Section -->
                    <h3>Customer Received Quantities</h3>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Shipped Quantity</th>
                                <th>Received Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($outboundRequest->requested_quantities as $productId => $quantity)
                                <tr>
                                    <td>{{ $outboundRequest->sales->products->find($productId)->name }}</td>
                                    <td>{{ $quantity }}</td>
                                    <td>
                                        <input type="number" name="received_quantities[{{ $outboundRequest->id }}][{{ $productId }}]" 
                                            value="{{ $outboundRequest->received_quantities[$productId] ?? 0 }}" 
                                            class="form-control" min="0" {{ $outboundRequest->status != 'In Transit' ? 'readonly' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($outboundRequest->status == 'In Transit')
                        <button name="submit" type="submit" class="btn btn-primary" value="Update Received Quantities">Update Received Quantities</button>
                    @endif
                @endforeach
            @endif
		@endif

        <!-- Action Buttons -->
        <h3>Actions</h3>
        <div class="mt-3">
            @switch($sale->status)
                @case('Planned')
                    <a href="{{ route('sales.updateStatus', ['sale' => $sale->id, 'status' => 'Unpaid']) }}" class="btn btn-primary mb-3">Request Outbound</a>
                    @break

                @case('Unpaid')
                    @if($outboundRequests[count($outboundRequests)-1]->status == 'Pending Confirmation')
                        <a href="{{ route('sales.updateStatus', ['sale' => $sale->id, 'status' => 'Pending Shipment']) }}" class="btn btn-success mb-3">Mark as Paid</a>
                    @else
                        <h4>Waiting Outbound Request Confirmation before can be Paid</h4>
                    @endif
                    @break

                @case('In Transit')
                    @break

                @case('Customer Complaint')
                    <a href="{{ route('sales.updateStatus', ['sale' => $sale->id, 'status' => 'Completed']) }}" class="btn btn-success mb-3">Resolve Complaint & Complete</a>
                    @break

                @case('Received - Pending Verification')
                    <a href="{{ route('sales.updateStatus', ['sale' => $sale->id, 'status' => 'Completed']) }}" class="btn btn-success mb-3">Complete Order</a>
                    @break
            @endswitch
        </div>

        <button type="submit" class="btn btn-primary mb-3">Update Sale</button>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary mb-3">Cancel</a>
    </form>
    
    <script>
        let productIndex = {{ count($sale->products) }};
        document.getElementById('add-product').addEventListener('click', function () {
            const productSection = document.getElementById('product-section');
            const newProduct = `
                <div class="form-group">
                    <label for="products[${productIndex}][product_id]">Product</label>
                    <select name="products[${productIndex}][product_id]" class="form-control" required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} - ${{ $product->price }}</option>
                        @endforeach
                    </select>
                    <label for="products[${productIndex}][quantity]">Quantity</label>
                    <input type="number" name="products[${productIndex}][quantity]" class="form-control" required>
                    <label for="products[${productIndex}][price]">Price</label>
                    <input type="number" step="0.01" name="products[${productIndex}][price]" class="form-control" required>
                    <label for="products[${productIndex}][note]">Note</label>
                    <textarea name="products[${productIndex}][note]" class="form-control"></textarea>
                </div>
            `;
            productSection.insertAdjacentHTML('beforeend', newProduct);
            productIndex++;
        });
    </script>
	<script>
    // JavaScript to prevent selection changes on readonly-select elements
    document.querySelectorAll('.readonly-select').forEach(function(select) {
        select.addEventListener('mousedown', function(event) {
            event.preventDefault();
        });
        select.addEventListener('click', function(event) {
            event.preventDefault();
        });
        select.addEventListener('change', function(event) {
            event.preventDefault();
        });
    });
	</script>
@endsection
