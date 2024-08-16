<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ContractList
 * 
 * @property int $id
 * @property int $user_id
 * @property int $price
 * @property string $sku
 * 
 * @property Product $product
 * @property User $user
 *
 * @package App\Models
 */
class ContractList extends Model
{
    use HasFactory;

	protected $table = 'contract_lists';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'price' => 'int'
	];

	protected $fillable = [
		'user_id',
		'price',
		'sku'
	];

	public function product()
	{
		return $this->belongsTo(Product::class, 'sku', 'sku');
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
