<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PriceList
 *
 * @property int $id
 * @property string $title
 * @property int $price
 * @property string $sku
 * 
 * @property Product $product
 *
 * @package App\Models
 */
class PriceList extends Model
{
    use HasFactory;

	protected $table = 'price_lists';
	public $timestamps = false;

	protected $casts = [
		'price' => 'int'
	];

	protected $fillable = [
		'title',
		'price',
		'sku'
	];

	public function product()
	{
		return $this->belongsTo(Product::class, 'sku', 'sku');
	}
}
