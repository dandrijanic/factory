<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProductFilterRequest;
use App\Http\Resources\ProductResource;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function __construct(
        protected ProductRepository $repository,
    ) {}

    public function index(ProductFilterRequest $request, ?int $categoryId = null): AnonymousResourceCollection
    {
        $filters = $request->validated();

        // TODO: improve caching strategy
        foreach (array_keys($filters) as $filter) {
            if (request()->has($filter))
                $filters[$filter] = request()->input($filter);
        }

        $cacheKey = sprintf('products_%s', md5(json_encode($filters)));

        if (Cache::has($cacheKey)) {
            return ProductResource::collection(Cache::get($cacheKey));
        }

        $products = $this->repository->getFiltered($filters, $categoryId);

        Cache::put($cacheKey, $products, now()->addMinutes(60));

        return ProductResource::collection($products);
    }

    public function store(Request $request): JsonResponse
    {
        // TODO: improve ProductController@store()
        $productDetails = $request->only([
            'title',
            'description',
            'price',
            'sku',
            'published',
            'published_at',
        ]);

        $result = $this->repository->create($productDetails);

        // TODO: implement logging

        return response()->json(['data' => $result], Response::HTTP_CREATED);
    }

    public function show(int $productId): ProductResource
    {
        // TODO: improve ProductController@show()
        $product = $this->repository->findById($productId);

        return ProductResource::make($product);
    }

    public function update(Request $request): JsonResponse
    {
        // TODO: improve ProductController@update()
        $productDetails = $request->only([
            'title',
            'description',
            'price',
            'sku',
            'published',
            'published_at',
        ]);

        $result = $this->repository->create($productDetails);

        // TODO: implement logging

        return response()->json(['data' => $result]);
    }

    public function delete(int $productId): bool
    {
        // TODO: improve ProductController@delete()

        // TODO: implement logging
        return $this->repository->delete($productId);
    }
}
