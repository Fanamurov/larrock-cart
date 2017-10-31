<?php

use Larrock\ComponentCart\AdminCartController;
use Larrock\ComponentCart\CartController;
use Larrock\ComponentCatalog\CatalogController;
use Larrock\ComponentUsers\UserController;

$middlewares = ['web', 'GetSeo'];
if(file_exists(base_path(). '/vendor/fanamurov/larrock-menu')){
    $middlewares[] = 'AddMenuFront';
}
if(file_exists(base_path(). '/vendor/fanamurov/larrock-blocks')){
    $middlewares[] = 'AddBlocksTemplate';
}
if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')){
    $middlewares[] = 'DiscountsShare';
}

Route::group(['middleware' => $middlewares], function(){
    Route::get('/cart', [
        'as' => 'cart.index', 'uses' => CartController::class .'@getIndex'
    ]);
    Route::post('/cart/short', [
        'as' => 'cart.sendOrder', 'uses' => CartController::class .'@sendOrderShort'
    ]);
    Route::post('/cart/full', [
        'as' => 'cart.sendOrderFull', 'uses' => CartController::class .'@sendOrderFull'
    ]);
    Route::get('/cart/success', [
        'as' => 'cart.success', 'uses' => UserController::class .'@cabinet'
    ]);
    Route::get('/cart/fail', [
        'as' => 'cart.fail', 'uses' => UserController::class .'@cabinet'
    ]);
    Route::get('/cart/oferta', [
        'as' => 'cart.oferta', 'uses' => CartController::class .'@oferta'
    ]);
    Route::post('/user/removeOrder/{id}', [
        'as' => 'user.removeOrder', 'uses' => CartController::class .'@removeOrder'
    ]);

    Route::post('/ajax/cartAdd', [
        'as' => 'ajax.cartAdd', 'uses' => CartController::class .'@cartAdd'
    ]);
    Route::post('/ajax/cartRemove', [
        'as' => 'ajax.cartRemove', 'uses' => CartController::class .'@cartRemove'
    ]);
    Route::post('/ajax/cartQty', [
        'as' => 'ajax.cartQty', 'uses' => CartController::class .'@cartQty'
    ]);
    Route::any('/ajax/getTovar', [
        'as' => 'ajax.getTovar', 'uses' => CatalogController::class .'@getTovar'
    ]);
});

Route::group(['prefix' => 'admin', 'middleware'=> ['web', 'level:2', 'LarrockAdminMenu', 'SaveAdminPluginsData', 'SiteSearchAdmin']], function(){
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