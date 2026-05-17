<?php

namespace Tests\Feature;

use App\Jobs\SyncWooCommerceProduct;
use App\Services\WooCommerceService;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class WooCommerceApiTest extends TestCase
{
    public function test_it_lists_products_from_woocommerce(): void
    {
        $service = Mockery::mock(WooCommerceService::class);
        $service->shouldReceive('getProducts')
            ->once()
            ->with(['search' => 'shirt', 'per_page' => '2'])
            ->andReturn([
                ['id' => 1, 'name' => 'Shirt'],
                ['id' => 2, 'name' => 'Hoodie'],
            ]);

        $this->app->instance(WooCommerceService::class, $service);

        $response = $this->getJson('/api/woocommerce/products?search=shirt&per_page=2');

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'fetched' => 2,
            ])
            ->assertJsonCount(2, 'products');
    }

    public function test_it_queues_product_creation(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/woocommerce/products', [
            'name' => 'Sample Product',
            'sku' => 'SKU-001',
            'price' => 19.99,
            'description' => 'Product description',
            'short_description' => 'Short description',
            'quantity' => 10,
            'weight' => '1.2',
            'woocommerce_category_id' => [3, 5],
        ]);

        $response->assertAccepted()
            ->assertJson([
                'success' => true,
                'message' => 'Product queued for creation',
            ]);

        Queue::assertPushed(SyncWooCommerceProduct::class, function (SyncWooCommerceProduct $job): bool {
            return $this->jobProperty($job, 'operation') === 'create'
                && $this->jobProperty($job, 'data')['name'] === 'Sample Product'
                && $this->jobProperty($job, 'data')['sku'] === 'SKU-001'
                && $this->jobProperty($job, 'data')['regular_price'] === '19.99'
                && $this->jobProperty($job, 'data')['stock_quantity'] === 10
                && $this->jobProperty($job, 'data')['categories'] === [['id' => 3], ['id' => 5]]
                && $this->jobProperty($job, 'id') === null;
        });
    }

    public function test_it_queues_product_update(): void
    {
        Queue::fake();

        $response = $this->putJson('/api/woocommerce/products/42', [
            'name' => 'Updated Product',
            'price' => 24.5,
            'quantity' => 7,
            'woocommerce_category_id' => [8],
        ]);

        $response->assertAccepted()
            ->assertJson([
                'success' => true,
                'message' => 'Product queued for update',
            ]);

        Queue::assertPushed(SyncWooCommerceProduct::class, function (SyncWooCommerceProduct $job): bool {
            return $this->jobProperty($job, 'operation') === 'update'
                && $this->jobProperty($job, 'id') === '42'
                && $this->jobProperty($job, 'data')['name'] === 'Updated Product'
                && $this->jobProperty($job, 'data')['regular_price'] === '24.5'
                && $this->jobProperty($job, 'data')['stock_quantity'] === 7
                && $this->jobProperty($job, 'data')['categories'] === [['id' => 8]];
        });
    }

    public function test_it_deletes_a_product_from_woocommerce(): void
    {
        $service = Mockery::mock(WooCommerceService::class);
        $service->shouldReceive('deleteProduct')
            ->once()
            ->with('99')
            ->andReturn((object) []);

        $this->app->instance(WooCommerceService::class, $service);

        $response = $this->deleteJson('/api/woocommerce/products/99');

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Product permanently deleted from WooCommerce.',
            ]);
    }

    private function jobProperty(SyncWooCommerceProduct $job, string $property): mixed
    {
        $reflection = new \ReflectionClass($job);
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($job);
    }
}
