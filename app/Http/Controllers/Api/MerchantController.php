<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Store;
use App\Traits\StoreTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MerchantController extends Controller
{
    use StoreTrait;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:merchants',
            'password' => 'required|confirmed'
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $request['password'] = bcrypt($request->password);

        $merchant = Merchant::create($request->all());

        $token = $merchant->createToken('Merchant Token')->accessToken;

        return response(['merchant' => $merchant, 'token' => $token]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $merchant = Merchant::where('email', $request->email)->first();

        if (!$merchant || !Hash::check($request->password, $merchant->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Auth::login($merchant);

        $tokenResult = $merchant->createToken('Merchant Token');

        return response()->json(['merchant' => $merchant, 'token' => $tokenResult->accessToken]);
    }

    public function createStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        if($this->merchantStore()) return response('Merchant has store already', 500);

        $store = Store::create(['name' => $request->name, 'merchant_id' => \auth()->id()]);

        if (!$store) return response('error', 500);

        return response('success', 200);
    }

    public function includeVat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 500);
        }

        $update = $this->merchantStore()
            ->update(['vat' => $request->amount]);

        if ($update)
            return response('success', 200);
        else
            return response('error', 500);
    }

    public function excludeVat()
    {
        $update = $this->merchantStore()
            ->update(['vat' => 0]);

        if ($update)
            return response('success', 200);
        else
            return response('error', 500);
    }

    public function includeShipping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 500);
        }

        $update = $this->merchantStore()
            ->update(['shipping' => $request->amount]);

        if ($update)
            return response('success', 200);
        else
            return response('error', 500);
    }

    public function excludeShipping()
    {
        $update = $this->merchantStore()
            ->update(['shipping' => 0]);

        if ($update)
            return response('success', 200);
        else
            return response('error', 500);
    }
}
