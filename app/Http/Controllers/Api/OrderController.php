<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Traits\StoreTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use StoreTrait;

    private $user;

    public function __construct()
    {
        $this->user = Auth::guard('api')->user();
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|numeric',
            'products' => 'required'
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 500);
        }

        $productArr = json_decode($request->products, true);

        self::checkProduct($productArr);

        $order = Order::create([
            'user_id' => $this->user->id,
            'store_id' => $request->store_id
        ]);

        foreach ($productArr as $product) {
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $product['id'],
                'quantity' => $product['quantity'],
                'total' => self::getProductPrice($product['id']) * $product['quantity']
            ]);
        }


    }

    /**
     * @throws Exception
     */
    private static function checkProduct($products)
    {
        foreach ($products as $product) {
            $productExist = Product::find($product['id']);
            if (!$productExist) throw new Exception('Product does not exist');
        }
    }

    private static function getProductPrice($productId)
    {
        return Product::find($productId)->price;
    }
}
