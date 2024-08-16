<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Repositories\ProductRepository;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Response;
use Tests\TestCase;

final class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private ProductRepositoryInterface $productRepository;

    public function setUp(): void
    {
        parent::setUp();

        Product::factory(50)->create();

        $this->productRepository = app(ProductRepository::class);
    }

    public function test_paginated_product_listing_default()
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertSuccessful();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'title',
                    'description',
                    'price',
                    'sku',
                    'published',
                    'published_at',
                    'categories' => [],
                ]
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]
        ]);

        $products = Product::all()->where('published', true);

        $this->assertCount(10, $response->json('data'));

        // TODO: improve this test

        $totalPages = ceil(count($products) / 10);

        $this->assertEquals($totalPages, $response->json('meta.last_page'));
        $this->assertEquals(count($products), $response->json('meta.total'));
    }

    public function test_paginated_product_listing_custom()
    {
        $response = $this->getJson('/api/v1/products?per_page=5&page=3');

        $response->assertSuccessful();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'title',
                    'description',
                    'price',
                    'sku',
                    'published',
                    'published_at',
                    'categories' => [],
                ]
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]
        ]);

        $products = Product::where('published', true)->with('categories')->skip(10)->take(5)->get();

        $this->assertCount(5, $response->json('data'));

        $totalProducts = Product::where('published', true)->count();
        $totalPages = ceil($totalProducts / 5);

        // TODO: improve this test

        $this->assertEquals($totalPages, $response->json('meta.last_page'));
        $this->assertEquals($totalProducts, $response->json('meta.total'));
    }

    public function test_product_filtering()
    {
       $product1 = Product::factory()->create([
           'published' => true,
           'title' => 'Laptop',
           'price' => 2000,
       ]);
       $product2 = Product::factory()->create([
           'published' => true,
           'title' => 'Desktop Computer',
           'price' => 3000,
       ]);
       $product3 = Product::factory()->create([
           'published' => true,
           'price' => 20000,
       ]);
       $category = Category::factory()->create();
       $category->products()->attach($product1);
       $category->products()->attach($product3);

       $response = $this->getJson(sprintf('/api/v1/products?price_min=1000&price_max=5000&title=laptop&category_id=%d', $category->id));

       $response->assertStatus(200);
       $response->assertJsonStructure([
           'data' => [
               [
                   'id',
                   'title',
                   'description',
                   'price',
                   'sku',
                   'published',
                   'published_at',
                   'categories' => [],
               ]
           ],
           'meta' => [
               'current_page',
               'last_page',
               'per_page',
               'total',
           ]
       ]);

       $response->assertJson([
               'data' => [
                   ['id' => $product1->id],
               ],
           ])
           ->assertJsonMissing([
               'data' => [
                   ['id' => $product2->id],
               ],
           ]);
    }

    public function test_retrieval_of_products_in_valid_category_with_results()
    {
        Product::factory(10)->create(['published' => true]);
        $products = Product::factory(5)->create(['published' => true]);
        $category = Category::factory()->create();
        $category->products()->attach($products);
        $categoryId = $category->id;

        $response = $this->getJson("/api/v1/categories/{$categoryId}/products?per_page=10");
        $response->assertSuccessful();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'title',
                    'description',
                    'price',
                    'sku',
                    'published',
                    'published_at',
                    'categories' => [],
                ]
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]
        ]);

        $this->assertCount(5, $response->json('data'));
        // TODO: improve this test
    }

    public function test_product_listing_with_price_modifiers()
    {
        $this->markTestSkipped();
        // TODO: implement this test

        /** @var Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();

        $contractListProducts = ContractList::factory(3)->create(['user_id' => $user->id]);
        $priceListProducts = PriceList::factory(3)->create()->each(function ($priceList) use ($user) {
            $priceList->products()->syncWithoutDetaching([Product::factory()->create(['price' => 100])->id], ['price' => 90]);
        });

        $response = $this->actingAs($user)->getJson('/api/v1/products');
        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['title', 'description', 'price', 'sku']
                ]
            ]);
        foreach ($response->json('data') as $product) {
            $contractListPrice = ContractList::where(['user_id' => $user->id, 'sku' => $product['sku']])->value('price');
            $priceListPrice = PriceList::join('price_list_product', 'price_lists.id', '=', 'price_list_product.price_list_id')
                ->where(['price_list_product.product_id' => Product::where('sku', $product['sku'])->value('id'), 'user_id' => $user->id])
                ->value('price_list_product.price');
            if ($contractListPrice) {
                $this->assertEquals($contractListPrice, $product['price']);
            } elseif ($priceListPrice) {
                $this->assertEquals($priceListPrice, $product['price']);
            } else {
                $this->assertEquals(Product::where('sku', $product['sku'])->value('price'), $product['price']);
            }
        }
    }

    public function test_sort_products()
    {
        $this->markTestSkipped();
        // TODO: implement this test

        $product1 = Product::factory()->create(['title' => 'Product 1', 'price' => 100]);
        $product2 = Product::factory()->create(['title' => 'Product 2', 'price' => 50]);

        $response = $this->getJson('/api/v1/products?sort_by=price&order=desc');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
                'data' => [
                    ['id' => $product1->id],
                    ['id' => $product2->id],
                ],
            ]);

        $response = $this->getJson('/api/v1/products?sort_by=title&order=asc');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                ['id' => $product2->id],
                ['id' => $product1->id],
            ],
        ]);
    }

    public function test_product_listing_with_invalid_per_page()
    {
        // TODO: improve this test
        $response = $this->getJson('/api/v1/products?per_page=foo');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_negative_page_number()
    {
        // TODO: improve this test
        $response = $this->getJson('/api/v1/products?page=-1');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_non_existent_page_number()
    {
        // TODO: implement this test

        Product::factory(10)->create();

        $response = $this->getJson('/api/v1/products?page=20');
    }

    public function test_invalid_per_page_value()
    {
        $this->markTestSkipped();
        // TODO: implement this test

        $response = $this->getJson('/api/v1/products?per_page=foo');
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_paginated_product_listing_with_negative_per_page_value()
    {
        $this->markTestSkipped();
        // TODO: implement this test

        $response = $this->getJson('/api/v1/products?per_page=-10');
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_paginated_product_listing_with_large_per_page_value()
    {
        $this->markTestSkipped();
        // TODO: implement this test

        Product::factory(100)->create();
        $response = $this->getJson('/api/v1/products?per_page=200');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(100, 'data');
    }

    public function test_retrieval_of_pproducts_in_multiple_categories_with_results()
    {
        $this->markTestSkipped();
        // TODO: implement this test

        $categories = Category::factory(3)->create();
        $products1 = Product::factory(5)->create(['price' => 10.99]);
        $products2 = Product::factory(10)->create(['price' => 15.99]);
        $products3 = Product::factory(15)->create(['price' => 20.99]);
        $categories[0]->products()->attach($products1);
        $categories[1]->products()->attach($products2);
        $categories[2]->products()->attach($products3);

        foreach ($categories as $category) {
            $response = $this->getJson("/api/v1/products?category_id={$category->id}");

            $response->assertStatus(Response::HTTP_OK);
            $response->assertJsonCount($category->products()->count(), 'data');
        }
    }

    public function test_retrieval_of_products_in_valid_category_with_no_results()
    {
        $this->markTestSkipped();
        // TODO: implement this test

        $category = Category::factory()->create();
        $response = $this->getJson("/api/categories/{$category->id}/products");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(0, 'data');
    }

    public function test_retrieval_of_products_in_multilevel_category_with_results()
    {
        $this->markTestSkipped();
        // TODO: implement this test

        $category = Category::factory()->create();
        $subcategory1 = Category::factory()->create(['parent_id' => $category->id]);
        $subcategory2 = Category::factory()->create(['parent_id' => $category->id]);
        $product1 = Product::factory()->create()->categories()->sync([$subcategory1->id]);
        $product2 = Product::factory()->create()->categories()->sync([$subcategory1->id, $subcategory2->id]);

        $response = $this->getJson("/api/v1/products?category_id={$category->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(2, 'data');
    }

    public function test_sorting_and_filtering_of_paginated_product_listing_by_category()
    {
        $this->markTestSkipped();
        // TODO: implement this test

        $category1 = Category::factory()->create();
        $product1 = Product::factory()->create(['title' => 'Product 1', 'price' => 20]);
        $product2 = Product::factory()->create(['title' => 'Product 2', 'price' => 30]);
        $category2 = Category::factory()->create();
        $product3 = Product::factory()->create(['title' => 'Product 3', 'price' => 15]);

        $product1->categories()->sync([$category1->id]);
        $product2->categories()->sync([$category1->id]);
        $product3->categories()->sync([$category2->id]);

        $response = $this->getJson("/api/v1/products?category_id={$category1->id}&sort_by=price&direction=desc&price_min=25");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Product 1', $response->json()['data'][0]['title']);
    }

    public function test_single_product(): void
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        $category->products()->attach($product);

        $response = $this->get("/api/v1/products/{$product->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'price',
                'sku',
                'published_at',
                'categories' => [
                    '*' => ['id', 'title', 'description'],
                ],
                // 'price_lists' => [
                //     '*' => ['id', 'title', 'price', 'sku'],
                // ],
            ],
        ]);
    }
}
