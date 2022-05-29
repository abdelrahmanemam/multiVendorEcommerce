<?php

use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$router->group(['namespace' => 'Api'], function () use ($router) {

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->post('register', [UserController::class, 'register']);
        $router->post('login', [UserController::class, 'login']);
    });

    $router->group(['prefix' => 'merchant'], function () use ($router) {
        $router->post('register', [MerchantController::class, 'register']);
        $router->post('login', [MerchantController::class, 'login']);
    });

    $router->group(['middleware' => 'auth:merchant'], function () use ($router) {

        $router->group(['prefix' => 'merchant'], function () use ($router) {
            $router->post('create-store', [MerchantController::class, 'createStore']);
            $router->post('include-vat', [MerchantController::class, 'includeVat']);
            $router->post('exclude-vat', [MerchantController::class, 'excludeVat']);
            $router->post('include-shipping', [MerchantController::class, 'includeShipping']);
            $router->post('exclude-shipping', [MerchantController::class, 'excludeShipping']);
        });

        $router->group(['prefix' => 'product'], function () use ($router) {
            $router->post('create', [ProductController::class, 'create']);
        });
    });

    $router->group(['middleware' => 'auth:api'], function () use ($router) {

        $router->group(['prefix' => 'order'], function () use ($router) {
            $router->post('create', [OrderController::class, 'create']);
        });
    });

});
