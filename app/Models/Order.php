<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 * 
 * @property int $id
 * @property int $user_id
 * @property int $total
 * @property string $status
 * @property string $sku
 * 
 * @property Collection|Product[] $products
 *
 * @package App\Models
 */
class Order extends Model
{
	protected $table = 'orders';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'total' => 'int'
	];

	protected $fillable = [
		'user_id',
		'total',
		'status',
		'sku'
	];

	public function products()
	{
		return $this->belongsToMany(Product::class, 'order_products', 'order_id', 'sku')
					->withPivot('product_id', 'title', 'description', 'price', 'price_modifiers')
					->withTimestamps();
	}
}
