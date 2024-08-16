<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PriceModifier
 * 
 * @property int $id
 * @property string $type
 * @property int $amount
 *
 * @package App\Models
 */
class PriceModifier extends Model
{
	protected $table = 'price_modifiers';
	public $timestamps = false;

	protected $casts = [
		'amount' => 'int'
	];

	protected $fillable = [
		'type',
		'amount'
	];
}
