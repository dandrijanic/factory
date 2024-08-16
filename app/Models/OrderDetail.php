<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderDetail
 *
 * @property int $id
 * @property int $order_id
 * @property array|null $billing_details
 * @property array|null $shipping_details
 * @property array|null $price_modifiers
 *
 * @property Order $order
 *
 * @package App\Models
 */
class OrderDetail extends Model
{
	protected $table = 'order_details';
	public $timestamps = false;

	protected $casts = [
		'order_id' => 'int',
		'billing_details' => 'json',
		'shipping_details' => 'json',
		'price_modifiers' => 'json'
	];

	protected $fillable = [
		'billing_details',
		'shipping_details',
		'price_modifiers'
	];

	public function order()
	{
		return $this->belongsTo(Order::class);
	}
}
