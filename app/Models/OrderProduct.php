<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderProduct
 * 
 * @property int $order_id
 * @property int $product_id
 * @property string $title
 * @property string $description
 * @property int $price
 * @property array $price_modifiers
 * @property string $sku
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Order $order
 * @property Product $product
 *
 * @package App\Models
 */
class OrderProduct extends Model
{
	protected $table = 'order_products';
	public $incrementing = false;

	protected $casts = [
		'order_id' => 'int',
		'product_id' => 'int',
		'price' => 'int',
		'price_modifiers' => 'json'
	];

	protected $fillable = [
		'order_id',
		'product_id',
		'title',
		'description',
		'price',
		'price_modifiers',
		'sku'
	];

	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function product()
	{
		return $this->belongsTo(Product::class, 'sku', 'sku');
	}
}
