<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WooCommerceService
{
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerSecret;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('woocommerce.url'), '/');
        $this->consumerKey = config('woocommerce.consumer_key');
        $this->consumerSecret = config('woocommerce.consumer_secret');
    }

    protected function apiUrl(string $path): string
    {
        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    protected function request()
    {
        return Http::withBasicAuth(
            $this->consumerKey,
            $this->consumerSecret
        )->acceptJson();
    }

    public function getProducts($params = [])
    {
        return $this->request()->get($this->apiUrl('products'), $params);
    }

    public function createProduct($data)
    {
        return $this->request()->post($this->apiUrl('products'), $data);
    }

    public function updateProduct($id, $data)
    {
        return $this->request()->put($this->apiUrl("products/{$id}"), $data);
    }

    public function deleteProduct($id)
    {
        return $this->request()->delete($this->apiUrl("products/{$id}"), [
            'force' => true,
        ]);
    }
}
