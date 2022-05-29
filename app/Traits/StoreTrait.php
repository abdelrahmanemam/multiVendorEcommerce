<?php

namespace App\Traits;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait StoreTrait {

    public function merchantStore()
    {
        return Store::where(['merchant_id' => Auth::guard('merchant')->id()])
            ->first();
    }

}
