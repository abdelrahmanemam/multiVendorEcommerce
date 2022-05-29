<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StoreProduct;
use App\Traits\StoreTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use StoreTrait;

    public function create(Request $request)
    {
        $merchantStore = $this->merchantStore();

        if (!$merchantStore) return response('Merchant must create store first', 500);

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 500);
        }

        $product = Product::create(['price' => $request->price]);

        if (!$product) return response('error', 500);

        $productData = self::arrangeProductData($request);

        foreach ($productData['name'] as $key => $item)
            StoreProduct::create([
                'store_id' => $merchantStore->id,
                'product_id' => $product->id,
                'name' => $item,
                'description' => $productData['description'][$key] ?? null,
                'lang' => $key,
            ]);

        return response('success', 200);
    }

    private static function arrangeProductData(Request $request): array
    {
        $details['name']['en'] = $request->name;
        $details['description']['en'] = $request->description;

        foreach ($request->except(['_token', 'price', 'name', 'description']) as $key => $item) {
            $arr = explode('_', $key);
            $details[$arr[0]][$arr[1]] = $item;
        }

        return $details;
    }
}
