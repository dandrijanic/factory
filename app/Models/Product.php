<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class Product
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $price
 * @property string $sku
 * @property Carbon $published_at
 * @property bool $published
 *
 * @property Collection|ContractList[] $contract_lists
 * @property Collection|Order[] $orders
 * @property Collection|PriceList[] $price_lists
 * @property Collection|Category[] $categories
 *
 * @package App\Models
 */
class Product extends Model
{
    use HasFactory;

	protected $table = 'products';
	public $timestamps = false;

	protected $casts = [
		'price' => 'int',
		'published_at' => 'datetime',
		'published' => 'bool'
	];

	protected $fillable = [
		'title',
		'description',
		'price',
		'sku',
		'published_at',
		'published'
	];

	public function contractLists()
	{
		return $this->hasMany(ContractList::class, 'sku', 'sku');
	}

	public function orders()
	{
		return $this->belongsToMany(Order::class, 'order_products', 'sku')
					->withPivot('product_id', 'title', 'description', 'price', 'price_modifiers')
					->withTimestamps();
	}

	public function priceLists()
	{
		return $this->hasMany(PriceList::class, 'sku', 'sku');
	}

	public function categories()
	{
		return $this->belongsToMany(Category::class, 'product_category');
	}

    public function getPriceAttribute()
    {
        $userId = Auth::id();

        $contractPrice = $this->contractLists()
            ->where('user_id', $userId)
            ->first();

        if ($contractPrice) {
            return $contractPrice->price;
        }

        $priceListPrice = $this->priceLists()->first();

        if ($priceListPrice) {
            return $priceListPrice->price;
        }

        return $this->attributes['price'];
    }

    public function scopeWithConditionalPrice($query)
    {
        $query->fromSub(function ($subQuery) {
            return $subQuery->from('products')
                ->select('products.*')
                ->leftJoin('contract_lists as contracts', function ($join) {
                    $join->on('products.sku', '=', 'contracts.sku')
                        ->where('contracts.user_id', Auth::id());
                })
                ->leftJoin('price_lists as prices', 'products.sku', '=', 'prices.sku')
                ->selectRaw("COALESCE(contracts.price, prices.price, products.price) AS conditional_price");
        }, 't');
    }

    public function scopeFilterByPriceMin(Builder $query, int $minPrice): Builder
    {
        return $query->withConditionalPrice()->having('conditional_price', '>=', $minPrice);
    }

    public function scopeFilterByPriceMax(Builder $query, int $maxPrice): Builder
    {
        return $query->withConditionalPrice()->having('conditional_price', '<=', $maxPrice);
    }

    public function scopeFilterByTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'LIKE', "%{$title}%");
    }

    public function scopeFilterByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('id', $categoryId);
        });
    }

    public function scopeSortBy(Builder $query, string $sortBy = 'id', string $direction = 'asc'): Builder
    {
        return $query->orderBy($sortBy, $direction);
    }

    public function scopeSortByPrice(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->withConditionalPrice()->orderBy('conditional_price', $direction);
    }

    public function scopeSortByTitle(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('title', $direction);
    }
}
