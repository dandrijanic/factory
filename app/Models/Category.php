<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Category
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 * @property int|null $parent_id
 *
 * @property Category|null $category
 * @property Collection|Category[] $categories
 * @property Collection|Product[] $products
 *
 * @package App\Models
 */
class Category extends Model
{
    use HasFactory;

	protected $table = 'categories';
	public $timestamps = false;

	protected $casts = [
		'parent_id' => 'int'
	];

	protected $fillable = [
		'title',
		'description',
		'parent_id'
	];

	public function parent()
	{
		return $this->belongsTo(Category::class, 'parent_id');
	}

	public function children()
	{
		return $this->hasMany(Category::class, 'parent_id');
	}

	public function products()
	{
		return $this->belongsToMany(Product::class, 'product_category');
	}
}
