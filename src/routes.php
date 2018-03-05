<?php

Route::get('/cart', 'Larrock\ComponentCart\CartController@getIndex')->name('cart.index');
Route::post('/cart/order', 'Larrock\ComponentCart\CartController@createOrder')->name('cart.create.order');
Route::get('/cart/oferta', 'Larrock\ComponentCart\CartController@oferta')->name('cart.oferta');
Route::post('/user/removeOrder/{id}', 'Larrock\ComponentCart\CartController@removeOrder')->name('cart.remove.order');
Route::post('/ajax/cartAdd', 'Larrock\ComponentCart\CartController@cartAdd')->name('cart.add');
Route::post('/ajax/cartRemove', 'Larrock\ComponentCart\CartController@cartRemove')->name('cart.remove');
Route::post('/ajax/cartQty', 'Larrock\ComponentCart\CartController@cartQty')->name('cart.qty');

Route::group(['prefix' => 'admin'], function(){
    Route::delete('/cart/removeItem', 'Larrock\ComponentCart\AdminCartController@removeItem')->name('cart.removeItem');
    Route::resource('cart', 'Larrock\ComponentCart\AdminCartController', ['names' => [
        'index' => 'admin.cart.index',
        'edit' => 'admin.cart.edit',
    ]]);
    Route::post('/ajax/cartAdd', 'Larrock\ComponentCart\AdminCartController@cartAdd')->name('admin.cart.add');
    Route::put('/cart/qtyItem/{id}', 'Larrock\ComponentCart\AdminCartController@editQtyItem')->name('cart.editQtyItem');
    Route::post('/cart/getTovarForCreate', 'Larrock\ComponentCart\AdminCartController@getTovarForCreate')->name('cart.getTovarForCreate');
    Route::post('/cart/createOrder', 'Larrock\ComponentCart\AdminCartController@createOrder')->name('cart.createOrder');
    Route::post('/cart/sendNotify', 'Larrock\ComponentCart\AdminCartController@sendNotify')->name('cart.sendNotify');
    Route::any('/cart/getTovar', 'Larrock\ComponentCart\AdminCartController@getTovar')->name('admin.cart.get.tovar');
});

Breadcrumbs::register('admin.'. LarrockCart::getName() .'.index', function($breadcrumbs){
    $breadcrumbs->push(LarrockCart::getTitle(), '/admin/'. LarrockCart::getName());
});