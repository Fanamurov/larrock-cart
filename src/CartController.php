<?php

namespace Larrock\ComponentCart;

use App\Http\Controllers\Controller;
use Auth;
use Cart;
use Illuminate\Http\Request;
use Larrock\ComponentDiscount\Helpers\DiscountHelper;
use Mail;
use Session;
use Validator;
use Larrock\ComponentCatalog\Facades\LarrockCatalog;
use Larrock\ComponentUsers\Facades\LarrockUsers;
use Larrock\ComponentCart\Facades\LarrockCart;

class CartController extends Controller
{
    public function __construct()
    {
        LarrockCart::shareConfig();
    }

    public function getIndex()
    {
        if(Cart::instance('main')->count() === 0){
            Session::push('message.danger', 'Ваша корзина пуста');
            return redirect('/');
        }

        $cart = Cart::instance('main')->content();
        foreach($cart as $key => $item){
            //Проверяем наличие товара
            if($get_tovar = LarrockCatalog::getModel()->whereId($item->id)->first()){
                /*if((int)$get_tovar->nalichie < 1){
                    Cart::remove($item->rowid);
                    Alert::add('message', $get_tovar->title .' уже нет в наличии, товар удален из корзины')->flash();
                    if(Cart::count(TRUE) < 1){
                        return back()->withInput();
                    }else{
                        return redirect('/cart')->withInput();
                    }
                }
                if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
                    $discountHelper = new DiscountHelper();
                }*/
                //Важный момент. Проверяем наличие, только у товаров, где оно указано > 0. Не проверяем товары на заказ
                /*if((int)$get_tovar->nalichie > 0 && (int)$get_tovar->nalichie < (int)$item->qty){
                    $options = $item->options->toArray();
                    $get_discount = $discountHelper->apply_discountsByTovar($get_tovar);
                    $cost_discount = $get_discount->cost;
                    Cart::remove($item->rowid);
                    Cart::associate('Catalog', 'App\Models')->add($get_tovar->id, $get_tovar->title, $get_tovar->nalichie, $cost_discount, $options);
                    Alert::add('error', 'Не весь товар в наличии, количество товара откорретировано в меньшу сторону')->flash();
                }*/
            }else{
                Cart::instance('main')->remove($item->rowid);
                if(Cart::instance('main')->count(TRUE) < 1){
                    return back()->withInput();
                }
                return redirect('/cart')->withInput();
            }
        }
        $seo = ['title' => 'Корзина товаров. Оформление заявки'];

        if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
            $discountHelper = new DiscountHelper();
            $discount = $discountHelper->check();
            $discount_motivate = $discountHelper->motivate_cart_discount(Cart::total());
        }

        return view('larrock::front.cart.table', compact('cart', 'seo', 'discount', 'discount_motivate' , ['cart', 'seo', 'discount', 'discount_motivate']));
    }

    /**
     * Полный цикл оформления покупки
     * @param Request $request
     *
     * @return CartController|\Illuminate\Http\RedirectResponse
     */
    public function sendOrderFull(Request $request)
    {
        $validator_rules = [
            'oferta' => 'accepted'
        ];

        if( !$request->has('without_registry')){
            $validator_rules['email'] = 'required|email';
        }

        $app = LarrockCart::getRows();
        $except_rows = ['order_id', 'status_order', 'status_pay'];

        if( !$request->has('without_registry')){
            $except_rows[] = 'email';
        }

        foreach ($app as $row){
            if(strpos($row->valid, 'required') && !in_array($row->name, $except_rows)){
                $validator_rules[$row->name] = $row->valid;
            }
        }

        $validator = Validator::make($request->all(), $validator_rules);
        if($validator->fails()){
            return back()->withErrors($validator)->withInput($request->all());
        }

        if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
            $discountHelper = new DiscountHelper();
        }

        if( !$request->has('without_registry')) {
            $user = $this->authAttempt($request);
            if($user && !isset($user->id)) {
                return back()->withInput();
            }

            if($update_user = LarrockUsers::getModel()->whereId($user->id)->first()) {
                if(empty($user->fio)) {
                    $user->fio = $request->get('fio');
                }
                if(empty($user->address)) {
                    $user->address = $request->get('address');
                }
                if(empty($user->tel)) {
                    $user->tel = $request->get('tel');
                }
                //Обновление учетки
                $update_user->save();
            }
        }

        //Собираем данные для заказа
        $create_order = LarrockCart::getModel();
        if( !$request->has('without_registry')){
            $create_order->user = $user->id;
        }
        $create_order->items = Cart::instance('main')->content();

        $create_order->cost = (float)str_replace(' ', '', Cart::instance('main')->total());
        $create_order->cost_discount = NULL;

        if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
            if($discount = $discountHelper->check()){
                if($discount['profit'] > 0 && $discount['cost_after_discount'] > 0){
                    $create_order->cost_discount = $discount['cost_after_discount'];
                    $create_order->discount = $discount;
                }
            }

            //Обрабатываем счетчик использования скидок
            if(isset($create_order->discount->discount)){
                $discountHelper->discountCountApply($create_order->discount->discount);
            }
        }

        $create_order->status_order = 'Обрабатывается';
        $create_order->status_pay = 'Не оплачено';
        $create_order->method_delivery = $request->get('delivery-method');
        $create_order->method_pay = $request->get('pay-method');
        $create_order->address = $request->get('address');
        $create_order->tel = $request->get('tel');
        $create_order->email = $request->get('email');
        $create_order->fio = $request->get('fio');
        $create_order->kupon = $request->get('kupon');
        $create_order->comment = $request->get('comment');
        if( !$order_id = LarrockCart::getModel()->max('order_id')){
            $order_id = 1;
        }
        $create_order->order_id = ++$order_id;

        if($create_order->save()){
            $this->changeTovarStatus($create_order->items, $create_order->id);
            $this->mailFullOrder($request, $create_order);
            Session::push('message.success', 'Ваш заказ #'. $create_order->order_id .' успешно добавлен');
            Cart::instance('main')->destroy();
            return redirect()->to('/user/cabinet');
        }
        Session::push('message.danger', 'Не удалось оформить заказ');
        return back()->withInput();
    }

    /**
     * Отправка email'а о новом заказе
     * @param Request $request
     * @param         $order
     */
    public function mailFullOrder(Request $request, $order)
    {
        \Log::info('NEW ORDER #'. $order->order_id .'. Order: '. json_encode($order));

        $mails = array_map('trim', explode(',', env('MAIL_TO_ADMIN', 'robot@martds.ru')));
        if( !empty($order->email)){
            $mails[] = $order->email;
        }
        $mails = array_unique($mails);

        $subject = 'Заказ #'. $order->order_id .' на сайте '. env('SITE_NAME', array_get($_SERVER, 'HTTP_HOST')) .' успешно оформлен';
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        Mail::send('larrock::emails.orderFull', ['data' => $order->toArray(), 'subject' => $subject],
            function($message) use ($mails, $subject){
                $message->from('no-reply@'. array_get($_SERVER, 'HTTP_HOST'), env('MAIL_TO_ADMIN_NAME', 'ROBOT'));
                $message->to($mails);
                $message->subject($subject);
            });

        if( !empty($order->email)){
            Session::push('message.success', 'На Ваш email отправлено письмо с деталями заказа');
        }
    }

    /**
     * Меняем количество товара в наличии
     *
     * @param $cart
     * @param $id_order
     *
     * @return bool|\Illuminate\Http\RedirectResponse|CartController
     */
    protected function changeTovarStatus($cart, $id_order)
    {
        foreach($cart as $item){
            if($data = LarrockCatalog::getModel()->find($item->id)){
                $data->nalichie -= $item->qty; //Остаток товара
                $data->sales += $item->qty; //Количество продаж
                if($data->save()){
                    Session::push('message.success', 'Товар для вас зарезервирован');
                }else{
                    Session::push('message.danger', 'Не удалось зарезервировать товар под ваш заказ');
                }
            }else{
                //Товара больше нет в продаже, откатываем заказ
                $find_order = LarrockCart::getModel()->find($id_order);
                $find_order->delete();
                Cart::instance('main')->remove($item->id);
                Session::push('message.danger', 'Товара из вашей корзины больше нет в нашем каталоге');
                return back()->withInput();
            }
        }
        return TRUE;
    }

    /**
     * Попытка авторизации
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Http\RedirectResponse
     */
    protected function authAttempt(Request $request)
    {
        if( !$user = Auth::guard()->user()){
            if($request->get('password', '') === ''){
                Session::push('message.danger', 'Пароль обязателен для заполнения');
                return back()->withInput($request->all());
            }
            //Пользователь не авторизован, пробуем авторизовать
            if(Auth::attempt(['email' => $request->get('email'), 'password' => $request->get('password')])){
                return Auth::guard()->user();
            }
            //Авторизация не прошла
            //Пользователь еще не зарегистрирован, регистрируем
            if($user = $this->fastRegistry($request)){
                //Зарегистрировали, теперь авторизуем
                if(Auth::attempt(['email' => $request->get('email'), 'password' => $request->get('password')])){
                    // Authentication passed...
                    return Auth::guard()->user();
                }
                Session::push('message.danger', 'Покупатель '. $request->input('email') .' зарегистрирован, но не авторизован');
            }
            return back()->withInput($request->all());

        }
        //Пользователь уже зарегистрирован
        return $user;
    }

    /**
     * Тихая регистрация
     *
     * @param Request $request
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Http\RedirectResponse
     */
    protected function fastRegistry(Request $request)
    {
        if($user = LarrockUsers::getModel()->whereEmail($request->input('email'))->first()){
            Session::push('message.danger', 'Неверный пароль. Забыли Ваш пароль? <a target="_blank" href="/password/reset">Восстановление пароля</a>');
            return back()->withInput($request->all());
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()){
            return back()->withInput()->withErrors($validator);
        }

        LarrockUsers::getModel()->create([
            'email' => $request->get('email'),
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'tel' => $request->get('tel'),
            'address' => $request->get('address'),
            'fio' => $request->get('fio'),
            'password' => \Hash::make($request->get('password')),
        ]);

        if($user = LarrockUsers::getModel()->whereEmail($request->input('email'))->first()){
            $user->attachRole(3);
            Session::push('message.success', 'Пользователь '. $request->input('email') .' успешно зарегистрирован');
            $this->mailRegistry($request, $user);
            return $user;
        }
        Session::push('message.danger', 'Пользователь '. $request->input('email') .' не был добавлен');
        return back()->withInput($request->all());
    }

    /**
     * Отправка письма о регистрации
     * @param Request $request
     * @param User    $user
     */
    public function mailRegistry(Request $request, $user)
    {
        \Log::info('NEW USER REGISTRY ID#'. $user->id .' email:'. $user->email);

        $mails = array_map('trim', explode(',', env('MAIL_TO_ADMIN', 'robot@martds.ru')));
        $mails[] = $user->email;

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        Mail::send('larrock::emails.register', ['data' => $user->toArray()],
            function($message) use ($mails){
                $message->from('no-reply@'. array_get($_SERVER, 'HTTP_HOST'), env('MAIL_TO_ADMIN_NAME', 'ROBOT'));
                $message->to($mails);
                $message->subject('Вы успешно зарегистрировались на сайте '. env('SITE_NAME', array_get($_SERVER, 'HTTP_HOST'))
                );
            });

        Session::push('message.success', 'На Ваш email отправлено письмо с регистрационными данными');
    }

    /* https://github.com/Crinsane/LaravelShoppingcart */
    /**
     * Add a row to the cart
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartAdd(Request $request)
    {
        $get_tovar = LarrockCatalog::getModel()->whereId($request->get('id'))->firstOrFail();
        if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
            $discountHelper = new DiscountHelper();
            $apply_discount = $discountHelper->apply_discountsByTovar($get_tovar, TRUE);
            $cost = $apply_discount->cost;
        }else{
            $cost = $get_tovar->cost;
        }
        $qty = $request->get('qty', 1);
        if($qty < 1){
            $qty = 1;
        }
        $options = $request->get('options', []);
        if( !empty($options)){
            $options = (array) json_decode($options);
        }

        $cartid = Cart::instance('main')->search(function ($cartItem, $rowId) use ($request) {
            return $cartItem->id === $request->get('id');
        });
        if($cartid === false){
            $cartid = Cart::instance('main')->search(function ($cartItem, $rowId) use ($request) {
                return $cartItem->id === (int)$request->get('id');
            });
        }
        if(isset($cartid[0])){
            if((int)$get_tovar['nalichie'] > 0 && (int)$get_tovar['nalichie'] <= (int)Cart::instance('main')->get($cartid[0])->qty){
                return response()->json(['status' => 'error', 'message' => 'У вас в корзине все доступное количество товара']);
            }
        }
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        Cart::instance('main')->add($request->get('id'), $get_tovar->title, $qty, $cost, $options)->associate(LarrockCart::getModelName());

        if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
            $discountHelper = new DiscountHelper();
            $discounts = $discountHelper->check();
            $total = $discounts['cost_after_discount'];
            $profit = $discounts['profit'];
        }else{
            $total = Cart::instance('main')->total();
            $profit = 0;
        }

        return response()->json(['status' => 'success', 'message' => 'Товар добавлен в корзину', 'total' => $total,
            'total_discount' => $profit, 'count' => Cart::instance('main')->count()]);
    }

    /**
     * Empty the cart
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartDestroy()
    {
        Cart::instance('main')->destroy();
        return response('OK');
    }

    /**
     * Get the price total
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartTotal()
    {
        Cart::instance('main')->total();
        return response('OK');
    }

    /**
     * Get the cart content
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartContent()
    {
        Cart::instance('main')->content();
        return response('OK');
    }

    /**
     * Update params of one row of the cart
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartUpdate(Request $request)
    {
        Cart::instance('main')->update($request->get('rowid'), []);
        return response('OK');
    }

    /**
     * Update the quantity of one row of the cart
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartQty(Request $request)
    {
        if($update = Cart::instance('main')->update($request->get('rowid'), $request->get('qty'))){
            $subtotal = $update->subtotal;
            $total_discount = 0;
            if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')){
                $discountHelper = new DiscountHelper();
                if($discount = $discountHelper->check(NULL, Cart::instance('main')->total())){
                    if($discount['profit'] > 0 && $discount['cost_after_discount'] > 0){
                        $total_discount = $discount['cost_after_discount'];
                    }
                }
            }
            return response()->json(['total' => Cart::instance('main')->total(), 'subtotal' => $subtotal, 'total_discount' => $total_discount]);
        }
        return abort('500', 'not valid data input');
    }

    /**
     * Remove a row from the cart
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartRemove(Request $request)
    {
        Cart::instance('main')->remove($request->get('rowid'));
        return response(Cart::count());
    }

    /**
     * Remove a row from the cart
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartCount()
    {
        Cart::instance('main')->count();
        return response('OK');
    }

    /**
     * Страница договора-оферты магазина
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function oferta()
    {
        return view('larrock::front.cart.oferta');
    }

    /**
     * Удаление заказа
     *
     * @param $id
     * @return $this
     */
    public function removeOrder($id)
    {
        $order = LarrockCart::getModel()->find($id);
        if($order->delete()){
            $this->changeTovarStatus($order->items, $id);
            Session::push('message.danger', 'Заказ успешно отменен');
        }else{
            Session::push('message.danger', 'Произошла ошибка во время отмены заказа');
        }
        return back()->withInput();
    }
}