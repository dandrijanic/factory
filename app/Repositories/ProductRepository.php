<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(protected Product $model) {}

    public function getFiltered(array $filters, ?int $categoryId = null): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (isset($categoryId)) {
            $query->filterByCategory($categoryId);
        }

        if (isset($filters['title'])) {
            $query->filterByTitle($filters['title']);
        }

        if (isset($filters['price_min'])) {
            $query->filterByPriceMin($filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $query->filterByPriceMax($filters['price_max']);
        }

        $direction = isset($filters['direction']) ? strtolower($filters['direction']) : 'asc';

        if (!isset($filters['sort_by'])) {
            $query->sortBy('id', $direction);
        }

        if (isset($filters['sort_by']) && 'price' == strtolower($filters['sort_by'])) {
            $query->sortByPrice($direction);
        }

        if (isset($filters['sort_by']) && 'title' == strtolower($filters['sort_by'])) {
            $query->sortByTitle($direction);
        }

        $perPage = isset($filters['per_page']) ? $filters['per_page'] : 10;
        $page = isset($filters['page']) ? $filters['page'] : 1;

        return $query->with('categories')
            ->where('published', true)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById(int $productId): ?Product
    {
        return $this->model->with('categories')->findOrFail($productId);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->with('categories')->where('sku', $sku)->first();
    }

    public function create(array $product): Product
    {
        return $this->model->create($product);
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->model->findOrFail($id);
        $product->update($data);
        return $product;
    }

    public function delete($productId): bool
    {
        return $this->model->findOrFail($productId)->delete($productId);
    }
}
