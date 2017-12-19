<?php

namespace Larrock\ComponentCart;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Cart;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Larrock\ComponentCart\Exceptions\LarrockCartException;
use Larrock\ComponentDiscount\Helpers\DiscountHelper;
use Larrock\ComponentUsers\Models\User;
use Larrock\Core\Component;
use Mail;
use Session;
use Validator;
use Larrock\ComponentCatalog\Facades\LarrockCatalog;
use Larrock\ComponentUsers\Facades\LarrockUsers;
use Larrock\ComponentCart\Facades\LarrockCart;

class CartController extends Controller
{
    use AuthenticatesUsers, ValidatesRequests;

    /** @var $this Component */
    protected $config;

    /** @var  bool Используется ли оформление заказа без регистрации */
    protected $withoutRegistry;

    /** @var  bool|User Данные о пользователе */
    protected $user;

    /** @var  bool Следить ли за наличием, остатками товаров */
    protected $protectNalicie;

    public function __construct()
    {
        $this->config = LarrockCart::shareConfig();
        $this->protectNalicie = NULL;
        $this->middleware(LarrockCart::combineFrontMiddlewares());
    }

    /**
     * Страница интерфейса корзины
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function getIndex()
    {
        if(Cart::instance('main')->count() === 0){
            Session::push('message.danger', 'Ваша корзина пуста');
            return redirect('/');
        }

        $cart = Cart::instance('main')->content();
        /*foreach($cart as $key => $item){
            //Проверяем наличие товара
            if($get_tovar = LarrockCatalog::getModel()->whereId($item->id)->first()){
                if($this->protectNalicie){
                    if((int)$get_tovar->nalichie < 1){
                        Cart::instance('main')->remove($item->rowid);
                        Session::push('message.danger', 'Товара '. $item->name .' уже нет в наличии, товар удален из корзины');
                        if(Cart::instance('main')->count() < 1){
                            return back()->withInput();
                        }
                        return redirect('/cart')->withInput();
                    }
                }
            }else{
                Session::push('message.danger', 'Товара '. $item->name .' уже нет на нашем сайте, товар удален из корзины');
                Cart::instance('main')->remove($item->rowid);
                if(Cart::instance('main')->count() < 1){
                    return back()->withInput();
                }
                return redirect('/cart')->withInput();
            }
        }*/
        $seo = ['title' => 'Корзина товаров. Оформление заявки'];

        if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
            $discountHelper = new DiscountHelper();
            $discount = $discountHelper->check();
            $discount_motivate = $discountHelper->motivate_cart_discount(Cart::instance('main')->total());
        }

        return view(config('larrock.views.cart.getIndex', 'larrock::front.cart.table'), compact('cart', 'seo', 'discount', 'discount_motivate' , ['cart', 'seo', 'discount', 'discount_motivate']));
    }

    /**
     * Создание заказа, Логин/регистрация пользователя при необходимости
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function createOrder(Request $request)
    {
        $this->validOrder($request);

        if($request->has('without_registry')) {
            $this->withoutRegistry = TRUE;
        }else{
            $this->user = $this->guard()->user();
        }

        if( !$this->user && !$this->withoutRegistry){
            $this->user = $this->login($request);
        }

        return $this->saveOrder($request);
    }

    /**
     * @param Request $request
     * @return $this|bool
     */
    protected function validOrder(Request $request)
    {
        $validate_rules = $this->config->getValid();

        if($this->withoutRegistry) {
            unset($validate_rules['email'], $validate_rules['password']);
        }

        $validator = Validator::make($request->all(), $validate_rules);
        if($validator->fails()){
            return back()->withErrors($validator)->withInput($request->all());
        }

        return TRUE;
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        return $this->guard()->user();
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Symfony\Component\HttpFoundation\Response
     *
     * @throws ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        //Авторизоваться не получилось, пробуем проверить на ошибку в пароле
        if(User::getModel()->whereEmail($request->get('email'))->first()){
            throw ValidationException::withMessages([
                $this->username() => 'Пароль не верный',
            ]);
        }

        if( !$this->withoutRegistry){
            //Пробуем зарегистрировать
            $user = $request->all();
            $user['role'] = 3;
            Validator::make($user, LarrockUsers::getValid())->validate();

            event(new Registered($user = $this->createUser($request->all())));

            $this->guard()->login($user);

            return $this->guard()->user();
        }

        return NULL;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function createUser(array $data)
    {
        $user = [];
        $rows = LarrockUsers::getRows();
        foreach ($rows as $key => $value){
            if(array_key_exists($key, $rows) && array_key_exists($key, $data) && !empty($data[$key])){
                if($key === 'password'){
                    $user[$key] = bcrypt($data[$key]);
                }else{
                    $user[$key] = $data[$key];
                }
            }
        }
        $user = LarrockUsers::getModel()->create($user);
        $user->attachRole(3);
        $this->mailRegistry($user);
        return $user;
    }

    /**
     * Сохранение заказа в БД
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    protected function saveOrder(Request $request)
    {
        $order = [];

        $cartFillableRows = LarrockCart::getFillableRows();
        foreach ($cartFillableRows as $key => $row){
            $order[$row] = $request->get($row);
        }

        if( !$this->withoutRegistry){
            $order['user'] = $this->user->id;
        }
        $order['items'] = Cart::instance('main')->content();

        $order['cost'] = (float)str_replace(',', '', Cart::instance('main')->total());
        $order['cost_discount'] = NULL;

        if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
            $discountHelper = new DiscountHelper();
            if($discount = $discountHelper->check()){
                if($discount['profit'] > 0 && $discount['cost_after_discount'] > 0){
                    $order['cost_discount'] = $discount['cost_after_discount'];
                    $order['discount'] = $discount;
                }
            }

            //Обрабатываем счетчик использования скидок
            if(isset($order['discount']->discount)){
                $discountHelper->discountCountApply($order['discount']->discount);
            }
        }

        $order['status_order'] = 'Обрабатывается';
        $order['status_pay'] = 'Не оплачено';
        $order['kupon'] = $request->get('kupon');
        if( !$order_id = LarrockCart::getModel()->max('order_id')){
            $order_id = 1;
        }
        $order['order_id'] = ++$order_id;

        if($this->changeTovarStatus($order['items']) && $create_order = LarrockCart::getModel()->create($order)){
            $this->mailFullOrder($create_order);
            Session::push('message.success', 'Ваш заказ #'. $create_order->order_id .' успешно добавлен');
            Cart::instance('main')->destroy();

            if( !$this->withoutRegistry){
                return redirect()->to('/cabinet');
            }
            return redirect()->to('/');
        }
        Session::push('message.danger', 'Не удалось оформить заказ');
        return back()->withInput();
    }

    /**
     * Отправка email'а о новом заказе
     * @param         $order
     */
    public function mailFullOrder($order)
    {
        \Log::info('NEW ORDER #'. $order->order_id .'. Order: '. json_encode($order));

        $mails = array_map('trim', explode(',', env('MAIL_TO_ADMIN', 'robot@martds.ru')));
        if( !empty($order->email)){
            $mails[] = $order->email;
        }
        $mails = array_unique($mails);

        $subject = 'Заказ #'. $order->order_id .' на сайте '. env('SITE_NAME', array_get($_SERVER, 'HTTP_HOST')) .' успешно оформлен';
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        Mail::send(config('larrock.views.cart.emailOrderFull', 'larrock::emails.orderFull'),
            ['data' => $order->toArray(), 'subject' => $subject],
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
     * Проверяем наличие товара
     * Меняем количество товара в наличии
     *
     * @param $cart
     *
     * @return bool
     */
    protected function changeTovarStatus($cart)
    {
        $ok = TRUE;
        foreach($cart as $item){
            if($data = LarrockCatalog::getModel()->whereId($item->id)->first()){
                $data->nalichie -= $item->qty; //Остаток товара
                $data->sales += $item->qty; //Количество продаж
                if($data->save()){
                    Session::push('message.success', 'Товар для вас зарезервирован');
                }else{
                    Session::push('message.danger', 'Не удалось зарезервировать товар под ваш заказ');
                }
            }else{
                //Товара больше нет в продаже, откатываем заказ
                $ok = NULL;
                Cart::instance('main')->remove($item->rowId);
                Session::push('message.danger', 'Товара '. $item->name .' из вашей корзины больше нет в нашем каталоге');
                Session::push('message.danger', 'Мы обновили вашу корзину удалив '. $item->name);

            }
        }
        return $ok;
    }

    /**
     * Отправка письма о регистрации
     * @param array    $user
     */
    public function mailRegistry($user)
    {
        \Log::info('NEW USER REGISTRY ID#'. $user->id .' email:'. $user->email);

        $mails = [];
        if(config('larrock.user.sendImailWhenNewRegister', true) === true){
            $mails = array_map('trim', explode(',', env('MAIL_TO_ADMIN', 'robot@martds.ru')));
        }
        $mails[] = $user->email;

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        Mail::send('larrock::emails.register', ['data' => $user],
            function($message) use ($mails){
                $message->from('no-reply@'. array_get($_SERVER, 'HTTP_HOST'), env('MAIL_TO_ADMIN_NAME', 'ROBOT'));
                $message->to($mails);
                $message->subject('Вы успешно зарегистрировались на сайте '. env('SITE_NAME', array_get($_SERVER, 'HTTP_HOST'))
                );
            });

        Session::push('message.success', 'На Ваш email отправлено письмо с регистрационными данными');
    }

    /**
     * Add a row to the cart
     *
     * @param Request $request
     * @see https://github.com/Crinsane/LaravelShoppingcart
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
        Cart::instance('main')->add($request->get('id'), $get_tovar->title, $qty, $cost, $options)->associate(LarrockCatalog::getModelName());

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
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws LarrockCartException
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
        throw LarrockCartException::withMessage('not valid data input');
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
        return view(config('larrock.views.cart.oferta', 'larrock::front.cart.oferta'));
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
            $this->changeTovarStatus($order->items);
            Session::push('message.danger', 'Заказ успешно отменен');
        }else{
            Session::push('message.danger', 'Произошла ошибка во время отмены заказа');
        }
        return back()->withInput();
    }
}