<?php

namespace App\Jobs;

use Automattic\WooCommerce\HttpClient\HttpClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WooCommerceService;
use Illuminate\Support\Facades\Log;

class SyncWooCommerceProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $operation;
    protected array $data;
    protected $id;

    public function __construct(string $operation, array $data, $id = null)
    {
        $this->operation = $operation;
        $this->data = $data;
        $this->id = $id;
    }

    public function handle()
    {
        $service = app(WooCommerceService::class);

        try {
            if ($this->operation === 'create') {
                $response = $service->createProduct($this->data);
                $payload = json_decode(json_encode($response), true);

                Log::info('Queued WooCommerce product created', [
                    'response' => $payload,
                ]);
                return;
            }

            if ($this->operation === 'update') {
                $response = $service->updateProduct($this->id, $this->data);
                $payload = json_decode(json_encode($response), true);

                Log::info('Queued WooCommerce product updated', [
                    'product_id' => $this->id,
                    'response' => $payload,
                ]);
                return;
            }
        } catch (HttpClientException $e) {
            Log::error('Queued WooCommerce exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'response' => $e->getResponse()->getBody(),
            ]);
        } catch (\Exception $e) {
            Log::error('Queued WooCommerce exception', ['message' => $e->getMessage()]);
        }
    }
}
