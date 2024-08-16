<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Repositories\OrderRepositoryInterface;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Http\Request;
use App\Services\PriceModifierBase;
use App\Services\PriceModifierDiscount;
use App\Services\PriceModifierDiscountAboveThreshold;
use App\Services\PriceModifierVAT;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository,
    ) {}

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate();

        // TODO: extract price calculation to a service
        $price = 0;

        $priceModifierBase = new PriceModifierBase();

        foreach ($request->products as $productData) {
            $product = $this->productRepository->findById($productData['product_id']);

            $productPriceModifierDisscount = new PriceModifierDiscount($priceModifierBase, 10);
            $itemPrice = $productPriceModifierDisscount->calculate($productData['price']);

            $price += $itemPrice * $productData['quantity'];

            $orderItems[] = [
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => $productData['quantity'],
                'subtotal_price' => $itemPrice,
            ];
        }

        $priceModifierDiscountAboveTreshold = new PriceModifierDiscountAboveThreshold($priceModifierBase, 100, 15);
        $priceModifierVAT = new PriceModifierVAT($priceModifierDiscountAboveTreshold, 25);

        $totalPrice = $priceModifierVAT->calculate($price);

        // TODO: store the order into the database
        $order = $this->orderRepository->create();

        return response()->json('');
    }
}
