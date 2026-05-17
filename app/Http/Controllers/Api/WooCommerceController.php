<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\WooCommerceService;
use Illuminate\Support\Facades\Log;
use App\Jobs\SyncWooCommerceProduct;
use Automattic\WooCommerce\HttpClient\HttpClientException;

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

            $products = is_array($response) ? $response : json_decode(json_encode($response), true);

            return response()->json([
                'status' => 'success',
                'fetched' => count($products),
                'products' => $products,
            ]);
        } catch (HttpClientException $e) {
            return response()->json(
                [
                    'status' => 'failure',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ],
                500,
            );
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


            // dispatch product creation to background queue
            SyncWooCommerceProduct::dispatch('create', $data);

            return response()->json([
                'success' => true,
                'message' => 'Product queued for creation',
            ], 202);
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

            // dispatch product update to background queue
            SyncWooCommerceProduct::dispatch('update', $data, $id);

            return response()->json([
                'success' => true,
                'message' => 'Product queued for update',
            ], 202);
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
            $this->service->deleteProduct($id);

            Log::info('WooCommerce Product Deleted', [
                'product_id' => $id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product permanently deleted from WooCommerce.',
            ]);
        } catch (HttpClientException $e) {
            return response()->json(
                [
                    'status' => 'failure',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ],
                500,
            );
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
