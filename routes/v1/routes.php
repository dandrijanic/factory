<?php

use Illuminate\Support\Facades\Route;

Route::as('products')->group(
	base_path('routes/v1/products.php'),
);

Route::as('orders')->group(
	base_path('routes/v1/orders.php'),
);
