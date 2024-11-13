<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    // Allow mass assignment for these fields
	protected $fillable = [
		'supplier_name',
		'purchase_date',
		'warehouse_id',
		'status', // new field for status tracking
		'total_amount'
	];
	
	
	public function products()
	{
		return $this->belongsToMany(Product::class, 'purchase_product')
					->withPivot('quantity', 'buying_price', 'total_cost')
					->withTimestamps();
	}
	
	
	public function warehouse()
	{
		return $this->belongsTo(Warehouse::class);
	}
	
	
	public function inboundRequests()
	{
		return $this->hasMany(InboundRequest::class, 'purchase_order_id');
	}

}