<?php

use Larrock\ComponentCart\AdminCartController;
use Larrock\ComponentCart\CartController;
use Larrock\ComponentCatalog\CatalogController;
use Larrock\ComponentUsers\UserController;

Route::group(['middleware' => ['web', 'AddMenuFront', 'GetSeo', 'AddBlocksTemplate']], function(){
    Route::get('/cart', [
        'as' => 'cart.index', 'uses' => CartController::class .'@getIndex'
    ]);
    Route::post('/cart/short', [
        'as' => 'cart.sendOrder', 'uses' => CartController::class .'@sendOrderShort'
    ]);
    Route::post('/cart/full', [
        'as' => 'cart.sendOrderFull', 'uses' => CartController::class .'r@sendOrderFull'
    ]);
    Route::get('/cart/success', [
        'as' => 'cart.success', 'uses' => UserController::class .'@cabinet'
    ]);
    Route::get('/cart/fail', [
        'as' => 'cart.fail', 'uses' => UserController::class .'@cabinet'
    ]);

    Route::post('/ajax/cartAdd', [
        'as' => 'ajax.cartAdd', 'uses' => CartController::class .'Ajax@cartAdd'
    ]);
    Route::post('/ajax/cartRemove', [
        'as' => 'ajax.cartRemove', 'uses' => CartController::class .'Ajax@cartRemove'
    ]);
    Route::post('/ajax/cartQty', [
        'as' => 'ajax.cartQty', 'uses' => CartController::class .'Ajax@cartQty'
    ]);
    Route::any('/ajax/getTovar', [
        'as' => 'ajax.getTovar', 'uses' => CatalogController::class .'Ajax@getTovar'
    ]);
});

Route::group(['prefix' => 'admin', 'middleware'=> ['web', 'level:2', 'LarrockAdminMenu', 'SaveAdminPluginsData']], function(){
    Route::delete('/cart/removeItem', [
        'as' => 'cart.removeItem', 'uses' => AdminCartController::class .'@removeItem'
    ]);
    Route::resource('cart', AdminCartController::class, ['names' => [
        'index' => 'admin.cart.index',
        'edit' => 'admin.cart.edit',
    ]]);
    Route::put('/cart/qtyItem/{id}', [
        'as' => 'cart.editQtyItem', 'uses' => AdminCartController::class .'@editQtyItem'
    ]);
    Route::get('/cart/check/{id}', [
        'as' => 'cart.check', 'uses' => AdminCartController::class .'@docCheck'
    ]);
    Route::get('/cart/delivery/{id}', [
        'as' => 'cart.delivery', 'uses' => AdminCartController::class .'@docDelivery'
    ]);
});