<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Category;
use App\Models\ContractList;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\User;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

final class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $productRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->productRepository = app(ProductRepository::class);
    }

    public function test_fetch_paginated_products(): void
    {
        Product::factory(10)->create(['published' => true]);
        $result = $this->productRepository->getFiltered(['per_page' => 5]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    public function test_filter_by_category(): void
    {
        Product::factory(10)->create(['published' => true]);
        $products = Product::factory(5)->create(['published' => true]);
        $category = Category::factory()->create();
        $category->products()->attach($products);

        $result = $this->productRepository->getFiltered(['per_page' => 100], $category->id);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    public function test_filter_by_title(): void
    {
        Product::factory(15)->create(['published' => true]);
        Product::factory()->create(['published' => true, 'title' => 'product title']);
        Product::factory()->create(['published' => true, 'title' => 'someproduct title']);
        Product::factory()->create(['published' => true, 'title' => 'title prod']);

        $result = $this->productRepository->getFiltered(['title' => 'prod']);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(3, $result->items());
    }

    public function test_correct_price_is_returned(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user);

        $product = Product::factory()->create(['published' => true, 'price' => 900]);
        PriceList::factory()->create([
            'sku' => $product->sku,
            'price' => 800,
        ]);

        PriceList::factory()->create([
            'sku' => $product->sku,
            'price' => 700,
        ]);

        ContractList::factory()->create([
            'user_id' => $user->id,
            'sku' => $product->sku,
            'price' => 600,
        ]);

        ContractList::factory()->create([
            'user_id' => $user2->id,
            'sku' => $product->sku,
            'price' => 500,
        ]);

        $result = $this->productRepository->findById($product->id);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals($product->price, $result->price);
    }

    public function test_filter_by_price_min(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user);

        Product::factory(5)->create(['published' => true, 'price' => 10]);

        $product1 = Product::factory()->create(['published' => true, 'price' => 650]);
        PriceList::factory()->create([
            'sku' => $product1->sku,
            'price' => 500,
        ]);

        $product2 = Product::factory()->create(['published' => true, 'price' => 150]);
        PriceList::factory()->create([
            'sku' => $product2->sku,
            'price' => 70,
        ]);

        $product3 = Product::factory()->create(['published' => true, 'price' => 650]);
        ContractList::factory()->create([
            'user_id' => $user->id,
            'sku' => $product3->sku,
            'price' => 500,
        ]);

        $product4 = Product::factory()->create(['published' => true, 'price' => 150]);
        ContractList::factory()->create([
            'user_id' => $user->id,
            'sku' => $product4->sku,
            'price' => 70,
        ]);

        ContractList::factory()->create([
            'user_id' => $user2->id,
            'sku' => $product4->sku,
            'price' => 500,
        ]);


        $result = $this->productRepository->getFiltered(['price_min' => 100, 'per_page' => 100]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
    }

    public function test_filter_by_price_max(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user);

        Product::factory(5)->create(['published' => true, 'price' => 1000]);

        $product1 = Product::factory()->create(['published' => true, 'price' => 650]);
        PriceList::factory()->create([
            'sku' => $product1->sku,
            'price' => 200,
        ]);

        $product2 = Product::factory()->create(['published' => true, 'price' => 150]);
        PriceList::factory()->create([
            'sku' => $product2->sku,
            'price' => 700,
        ]);

        $product3 = Product::factory()->create(['published' => true, 'price' => 650]);
        ContractList::factory()->create([
            'user_id' => $user->id,
            'sku' => $product3->sku,
            'price' => 500,
        ]);

        $product4 = Product::factory()->create(['published' => true, 'price' => 150]);
        ContractList::factory()->create([
            'user_id' => $user->id,
            'sku' => $product4->sku,
            'price' => 700,
        ]);

        ContractList::factory()->create([
            'user_id' => $user2->id,
            'sku' => $product4->sku,
            'price' => 500,
        ]);

        $result = $this->productRepository->getFiltered(['price_max' => 500, 'per_page' => 100]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
    }

    public function test_sort_by_price(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user);

        $products = Product::factory(5)->create(['published' => true, 'price' => 1000]);

        $product1 = Product::factory()->create(['published' => true, 'price' => 650]);
        $priceList1 = PriceList::factory()->create([
            'sku' => $product1->sku,
            'price' => 200,
        ]);

        $product2 = Product::factory()->create(['published' => true, 'price' => 150]);
        $priceList2 = PriceList::factory()->create([
            'sku' => $product2->sku,
            'price' => 700,
        ]);

        $contractList1 = ContractList::factory()->create([
            'user_id' => $user->id,
            'sku' => $product2->sku,
            'price' => 500,
        ]);

        $product3 = Product::factory()->create(['published' => true, 'price' => 650]);
        $contractList2 = ContractList::factory()->create([
            'user_id' => $user->id,
            'sku' => $product3->sku,
            'price' => 600,
        ]);

        ContractList::factory()->create([
            'user_id' => $user2->id,
            'sku' => $product2->sku,
            'price' => 500,
        ]);

        $result = $this->productRepository->getFiltered(['sort_by' => 'price', 'per_page' => 100]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(8, $result->items());

        $products = array_merge($products->toArray(), [$priceList1, $contractList1, $contractList2]);
        $sortedPrices = collect($products)->pluck('price')->toArray();
        sort($sortedPrices);

        $prices = collect($result->items())->pluck('price')->toArray();
        $this->assertEquals($sortedPrices, $prices, 'Products are not sorted by price in ascending order');
    }

    public function test_sort_by_title(): void
    {
        // TODO: test sorting by title
        $this->markTestSkipped();
    }

    public function test_find_by_id(): void
    {
        $id = 12;

        Product::factory(10)->create(['published' => true]);
        Product::factory()->create(['published' => true, 'id' => $id]);

        $result = $this->productRepository->findById($id);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals($id, $result->id);
    }

    public function test_find_by_sku(): void
    {
        $sku = 'example-sku';

        Product::factory(10)->create(['published' => true]);
        Product::factory()->create(['published' => true, 'sku' => $sku]);

        $result = $this->productRepository->findBySku($sku);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals($sku, $result->sku);
    }

    public function test_create_product(): void
    {
        $data = [
            'title' => 'Test Product',
            'description' => 'This is a test product.',
            'price' => 999,
            'sku' => 'TEST001',
            'published' => true,
            'published_at' => now(),
        ];
        $result = $this->productRepository->create($data);
        $this->assertInstanceOf(Product::class, $result);
        // TODO: maybe implement this?

        $this->assertDatabaseHas('products', [
            'id' => $result->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'sku' => $data['sku'],
            'published' => $data['published'],
            'published_at' => $data['published_at'],
        ]);
    }

    public function test_update_product(): void
    {
        $product = Product::factory()->create();

        $newData = [
            'title' => 'Updated Test Product',
            'description' => 'This is an updated test product.',
            'price' => 1999,
            'sku' => 'UPDATED001',
            'published' => true,
        ];

        $result = $this->productRepository->update($product->id, $newData);
        $this->assertInstanceOf(Product::class, $result);

        $updatedProduct = Product::find($product->id);
        $this->assertEquals($newData['title'], $updatedProduct->title);
        $this->assertEquals($newData['description'], $updatedProduct->description);
        $this->assertEquals($newData['price'], $updatedProduct->price);
        $this->assertEquals($newData['sku'], $updatedProduct->sku);
        $this->assertEquals($newData['published'], $updatedProduct->published);
    }

    public function test_delete_product(): void
    {
        $product = Product::factory()->create();

        $result = $this->productRepository->delete($product->id);

        $this->assertTrue($result);
        $this->assertNull(Product::find($product->id));
    }
}
