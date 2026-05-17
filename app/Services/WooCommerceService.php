<?php

namespace App\Services;

use Automattic\WooCommerce\Client;

class WooCommerceService
{
    protected ?Client $client = null;

    protected function client(): Client
    {
        if ($this->client === null) {
            $this->client = new Client(
                rtrim((string) config('woocommerce.url'), '/'),
                (string) config('woocommerce.consumer_key'),
                (string) config('woocommerce.consumer_secret'),
                config('woocommerce.options', []),
            );
        }

        return $this->client;
    }

    public function getProducts($params = [])
    {
        return $this->client()->get('products', $params);
    }

    public function createProduct($data)
    {
        return $this->client()->post('products', $data);
    }

    public function updateProduct($id, $data)
    {
        return $this->client()->put("products/{$id}", $data);
    }

    public function deleteProduct($id)
    {
        return $this->client()->delete("products/{$id}", [
            'force' => true,
        ]);
    }
}
