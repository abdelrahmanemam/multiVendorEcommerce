<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Services\OrderService;
use App\Traits\StoreTrait;
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

        OrderService::checkProduct($productArr);

        $order = Order::create([
            'user_id' => $this->user->id,
            'store_id' => $request->store_id
        ]);

        $orderTotal = 0;

        foreach ($productArr as $product) {

            $productTotal = OrderService::getProductPrice($product['id']) * $product['quantity'];

            $orderTotal += $productTotal;

            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $product['id'],
                'quantity' => $product['quantity'],
                'total' => $productTotal
            ]);
        }

        $orderTotal += OrderService::calculateShipping($request->store_id) + ($orderTotal * OrderService::calculateVat($request->store_id) / 100);
        $order->update(['total' => $orderTotal]);

        return response("Order added to your cart, total = $orderTotal L.E", 200);
    }

    public function cartTotal()
    {
        $order = Order::where('user_id', $this->user->id)
            ->where('status', 0)
            ->first();

        if (!$order) return response('Add order first', 404);

        return response("Total cart is: $order->total L.E", 200);
    }
}
