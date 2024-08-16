<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatingOrder(): void
    {
        $this->markTestSkipped();
        $response = $this->put('/api/v1/order');

        $response->assertStatus(Response::HTTP_CREATED);
    }
}
