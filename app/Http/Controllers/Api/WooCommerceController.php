<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\WooCommerceService;
use Illuminate\Support\Facades\Log;

class WooCommerceController extends Controller
{
    protected $service;

    public function __construct(WooCommerceService $service)
    {
        $this->service = $service;
    }
    public function index(Request $request)
    {
        try {
            $params = [];

            if ($request->search) {
                $params['search'] = $request->search;
            }

            if ($request->per_page) {
                $params['per_page'] = $request->per_page;
            }

            $response = $this->service->getProducts($params);

            if (!$response->successful()) {
                return response()->json(
                    [
                        'status' => 'failure',
                        'message' => 'Failed to fetch products',
                    ],
                    500,
                );
            }

            $products = $response->json();

            return response()->json([
                'status' => 'success',
                'fetched' => count($products),
                'products' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'failure',
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }
    public function store(StoreProductRequest $request)
    {
        try {
            $data = [
                'name' => $request->name,
                'sku' => $request->sku,
                'regular_price' => (string) $request->price,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'manage_stock' => true,
                'stock_quantity' => $request->quantity,
                'weight' => $request->weight,
                'categories' => collect($request->woocommerce_category_id)
                    ->map(function ($id) {
                        return ['id' => $id];
                    })
                    ->toArray(),
            ];

            $response = $this->service->createProduct($data);

            if (!$response->successful()) {
                $error = $response->json();
                $isPermissionError = $response->status() === 401 && is_array($error) && ($error['code'] ?? null) === 'woocommerce_rest_cannot_create';

                Log::error('WooCommerce Create Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'response' => $error,
                ]);

                return response()->json(
                    [
                        'status' => 'failure',
                        'message' => $isPermissionError ? 'WooCommerce credentials do not have permission to create products. Regenerate the API key with Read/Write access.' : 'Failed to create product',
                        'error' => $error ?? [
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ],
                    ],
                    500,
                );
            }

            Log::info('WooCommerce Product Created', [
                'response' => $response->json(),
            ]);

            return response()->json(
                [
                    'status' => 'success',
                    'woocommerce_product_id' => $response->json()['id'],
                    'message' => 'Product created successfully',
                ],
                201,
            );
        } catch (\Exception $e) {
            Log::error('WooCommerce Exception', [
                'message' => $e->getMessage(),
            ]);

            return response()->json(
                [
                    'status' => 'failure',
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }
    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $data = [];

            if ($request->filled('price')) {
                $data['regular_price'] = (string) $request->price;
            }

            if ($request->filled('name')) {
                $data['name'] = $request->name;
            }

            if ($request->filled('sku')) {
                $data['sku'] = $request->sku;
            }

            if ($request->has('description')) {
                $data['description'] = $request->description;
            }

            if ($request->has('short_description')) {
                $data['short_description'] = $request->short_description;
            }

            if ($request->filled('quantity')) {
                $data['stock_quantity'] = $request->quantity;
            }

            if ($request->filled('weight')) {
                $data['weight'] = $request->weight;
            }

            if ($request->filled('woocommerce_category_id')) {
                $data['categories'] = collect($request->woocommerce_category_id)
                    ->map(function ($categoryId) {
                        return ['id' => $categoryId];
                    })
                    ->toArray();
            }

            $response = $this->service->updateProduct($id, $data);

            if (!$response->successful()) {
                return response()->json(
                    [
                        'status' => 'failure',
                        'message' => 'Failed to update product',
                        'error' => $response->json(),
                    ],
                    500,
                );
            }

            Log::info('WooCommerce Product Updated', [
                'product_id' => $id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product updated successfully on WooCommerce',
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'failure',
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }
    public function destroy($id)
    {
        try {
            $response = $this->service->deleteProduct($id);

            if (!$response->successful()) {
                return response()->json(
                    [
                        'status' => 'failure',
                        'message' => 'Failed to delete product',
                    ],
                    500,
                );
            }

            Log::info('WooCommerce Product Deleted', [
                'product_id' => $id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product permanently deleted from WooCommerce.',
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'failure',
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }
}
