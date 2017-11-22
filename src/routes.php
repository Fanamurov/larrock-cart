<?php

Route::get('/cart', 'Larrock\ComponentCart\CartController@getIndex')->name('cart.index');
Route::post('/cart/order', 'Larrock\ComponentCart\CartController@createOrder')->name('cart.create.order');
Route::get('/cart/success', 'Larrock\ComponentUsers\UsersController@cabinet')->name('cart.success');
Route::get('/cart/fail','Larrock\ComponentUsers\UsersController@cabinet')->name('cart.fail');
Route::get('/cart/oferta', 'Larrock\ComponentCart\CartController@oferta')->name('cart.oferta');
Route::post('/user/removeOrder/{id}', 'Larrock\ComponentCart\CartController@removeOrder')->name('cart.remove.order');
Route::post('/ajax/cartAdd', 'Larrock\ComponentCart\CartController@cartAdd')->name('cart.add');
Route::post('/ajax/cartRemove', 'Larrock\ComponentCart\CartController@cartRemove')->name('cart.remove');
Route::post('/ajax/cartQty', 'Larrock\ComponentCart\CartController@cartQty')->name('cart.qty');
Route::any('/ajax/getTovar', 'Larrock\ComponentCatalog\CatalogController@getTovar')->name('catalog.get.tovar');

Route::group(['prefix' => 'admin', 'middleware'=> ['web', 'level:2', 'LarrockAdminMenu', 'SaveAdminPluginsData', 'SiteSearchAdmin']], function(){
    Route::delete('/cart/removeItem', 'Larrock\ComponentCart\AdminCartController@removeItem')->name('cart.removeItem');
    Route::resource('cart', 'Larrock\ComponentCart\AdminCartController', ['names' => [
        'index' => 'admin.cart.index',
        'edit' => 'admin.cart.edit',
    ]]);
    Route::put('/cart/qtyItem/{id}', 'Larrock\ComponentCart\AdminCartController@editQtyItem')->name('cart.editQtyItem');
    Route::get('/cart/check/{id}', 'Larrock\ComponentCart\AdminCartController@docCheck')->name('cart.check');
    Route::get('/cart/delivery/{id}', 'Larrock\ComponentCart\AdminCartController@docDelivery')->name('cart.delivery');
});