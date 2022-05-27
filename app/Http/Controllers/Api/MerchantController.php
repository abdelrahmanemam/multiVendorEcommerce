<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MerchantController extends Controller
{
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

        $token = $merchant->createToken('API Token')->accessToken;

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
}
