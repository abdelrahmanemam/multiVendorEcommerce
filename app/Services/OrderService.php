<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Exception;

class OrderService
{
    /**
     * @throws Exception
     */
    public static function checkProduct($products)
    {
        foreach ($products as $product) {
            $productExist = Product::find($product['id']);
            if (!$productExist) throw new Exception('Product does not exist');
        }
    }

    public static function getProductPrice($productId)
    {
        return Product::find($productId)->price;
    }

    public static function calculateVat($storeId)
    {
        return Store::find($storeId)->vat;
    }

    public static function calculateShipping($storeId)
    {
        return Store::find($storeId)->shipping;
    }
}
