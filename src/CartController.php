<?php

namespace Larrock\ComponentCart;

use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Cart;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Larrock\ComponentCart\Exceptions\LarrockCartException;
use Larrock\ComponentCart\Helpers\CartMail;
use Larrock\ComponentCatalog\CatalogComponent;
use Larrock\ComponentDiscount\Helpers\DiscountHelper;
use Larrock\ComponentUsers\Models\User;
use Larrock\Core\Component;
use Larrock\Core\Helpers\MessageLarrock;
use Larrock\Core\Models\Link;
use Mail;
use Response;
use Validator;
use LarrockCatalog;
use LarrockUsers;
use LarrockCart;
use View;

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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     * @throws \Exception
     */
    public function getIndex()
    {
        if(Cart::instance('main')->count() === 0){
            MessageLarrock::danger(\Lang::get('larrock::cart.empty'));
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
        return view(config('larrock.views.cart.getIndex', 'larrock::front.cart.table'), ['cart' => $cart, 'seo' => $seo]);
    }

    /**
     * Создание заказа, Логин/регистрация пользователя при необходимости
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Symfony\Component\HttpFoundation\Response
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
     * @param  array  $data
     * @return User|Model
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
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    protected function saveOrder(Request $request)
    {
        $order = LarrockCart::getModel();
        $order->fill($request->all());

        if( !$this->withoutRegistry){
            $order->user = $this->user->id;
        }
        $order->items = Cart::instance('main')->content();

        if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
            $discountHelper = new DiscountHelper();
            $discounts = $discountHelper->check(NULL, $request->get('kupon'));
            $order->cost = $discounts->total;
            $order->discount = $discounts;
            if($discounts->d_kupon){
                $order->kupon = $discounts->d_kupon->word;
            }
            $discounts->countApplyDiscounts();
        }else{
            $order->cost = (float)str_replace(',', '', Cart::instance('main')->total());
        }

        $order->status_order = 'Обрабатывается';
        $order->status_pay = 'Не оплачено';
        if( !$order_id = LarrockCart::getModel()->max('order_id')){
            $order_id = 1;
        }
        $order->order_id = ++$order_id;

        if($this->changeTovarStatus($order['items']) && $create_order = $order->save()){
            $cartMail = new CartMail();
            $cartMail->mailOrder($request, $order, 'Заказ #'. $order->order_id .' на сайте '. env('SITE_NAME') .' успешно создан');
            MessageLarrock::success(\Lang::get('larrock::cart.order_create', ['number' => $order->order_id]));
            Cart::instance('main')->destroy();

            if( !$this->withoutRegistry){
                return redirect()->to(route('user.cabiner'));
            }
            return redirect()->to('/');
        }
        MessageLarrock::danger(\Lang::get('larrock::cart.order_create_fail'));
        return back()->withInput();
    }

    /**
     * Проверяем наличие товара
     * Меняем количество товара в наличии
     * @param $cart
     * @return bool
     * @throws \Exception
     */
    protected function changeTovarStatus($cart)
    {
        $ok = TRUE;
        foreach($cart as $item){
            if($data = LarrockCatalog::getModel()->whereId($item->id)->first()){
                $data->nalichie -= $item->qty; //Остаток товара
                $data->sales += $item->qty; //Количество продаж
                if($data->save()){
                    MessageLarrock::success(\Lang::get('larrock::cart.tovar.reserved'));
                }else{
                    MessageLarrock::danger(\Lang::get('larrock::cart.tovar.reserved_fail'));
                }
            }else{
                //Товара больше нет в продаже, откатываем заказ
                $ok = NULL;
                Cart::instance('main')->remove($item->rowId);
                MessageLarrock::danger(\Lang::get('larrock::cart.tovar.lost', ['name' => $item->name]));
                MessageLarrock::danger(\Lang::get('larrock::cart.tovar.lost_change_cart', ['name' => $item->name]));
            }
        }
        return $ok;
    }

    /**
     * Отправка письма о регистрации
     * @param Model    $user
     */
    public function mailRegistry($user)
    {
        \Log::info('NEW USER REGISTRY ID#'. $user->id .' email:'. $user->email);

        $mails = [];
        if(config('larrock.user.sendImailWhenNewRegister', true) === true){
            $mails = array_map('trim', explode(',', env('MAIL_TO_ADMIN', 'robot@martds.ru')));
        }
        $mails[] = $user->email;
        $mails = array_unique($mails);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        Mail::send('larrock::emails.register', ['data' => $user],
            function($message) use ($mails){
                $message->from('no-reply@'. array_get($_SERVER, 'HTTP_HOST'), env('MAIL_TO_ADMIN_NAME', 'ROBOT'));
                $message->to($mails);
                $message->subject('Вы успешно зарегистрировались на сайте '. env('SITE_NAME', array_get($_SERVER, 'HTTP_HOST'))
                );
            });
        MessageLarrock::success('На Ваш email отправлено письмо с регистрационными данными');
    }

    /**
     * Add a row to the cart
     * @param Request $request
     * @see https://github.com/Crinsane/LaravelShoppingcart
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function cartAdd(Request $request)
    {
        $get_tovar = LarrockCatalog::getModel()->whereId($request->get('id'))->firstOrFail();

        //Модификации товаров
        $costValueId = $request->get('costValueId');
        if($costValueId && (int)$costValueId > 0){
            $costValue = Link::whereId($costValueId)->firstOrFail();
            $get_tovar->cost = $costValue->cost;
        }

        $cost = $get_tovar->cost;
        $qty = $request->get('qty', 1);
        if($qty < 1){
            $qty = 1;
        }
        $options = $request->get('options', []);
        if( !empty($options)){
            $options = (array) json_decode($options);
        }
        if($costValueId && (int)$costValueId > 0){
            $link = Link::whereId($costValueId)->firstOrFail();
            if($searchParam = $link->model_child::whereId($link->id_child)->firstOrFail()){
                $searchParam['className'] = $link->model_child;
                $options['costValue'] = $searchParam->toArray();
            }
        }

        $cartid = Cart::instance('main')->search(function ($cartItem, $rowId) use ($request) {
            return $cartItem->id === $request->get('id');
        });
        if($cartid === false){
            $cartid = Cart::instance('main')->search(function ($cartItem, $rowId) use ($request) {
                return $cartItem->id === (int)$request->get('id');
            });
        }
        if(isset($cartid[0]) &&
            (int)$get_tovar['nalichie'] > 0 &&
            (int)$get_tovar['nalichie'] <= (int)Cart::instance('main')->get($cartid[0])->qty){
            return response()->json(['status' => 'error', 'message' => 'У вас в корзине все доступное количество товара']);
        }

        $id = $request->get('id');
        if($costValueId && (int)$costValueId > 0){
            $id .= '_'. $costValueId;
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        Cart::instance('main')->add($id, $get_tovar->title, $qty, $cost, $options)->associate(LarrockCatalog::getModelName());

        $count = Cart::instance('main')->count();
        $total = Cart::instance('main')->total();
        $profit = NULL;

        if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
            $discountHelper = new DiscountHelper();
            $discounts = $discountHelper->check();
            $total = $discounts->total;
            $profit = $discounts->profit;
        }
        return response()->json(['status' => 'success', 'message' => 'Товар добавлен в корзину', 'count' => $count,
            'total' => $total, 'profit' => $profit]);
    }

    /**
     * Empty the cart
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartDestroy()
    {
        Cart::instance('main')->destroy();
        return response('OK');
    }

    /**
     * Get the price total
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartTotal()
    {
        Cart::instance('main')->total();
        return response('OK');
    }

    /**
     * Get the cart content
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartContent()
    {
        Cart::instance('main')->content();
        return response('OK');
    }

    /**
     * Update params of one row of the cart
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartUpdate(Request $request)
    {
        Cart::instance('main')->update($request->get('rowid'), []);
        return response('OK');
    }

    /**
     * Update the quantity of one row of the cart
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws LarrockCartException
     */
    public function cartQty(Request $request)
    {
        if($update = Cart::instance('main')->update($request->get('rowid'), $request->get('qty'))){
            $subtotal = $update->subtotal;
            $total = Cart::instance('main')->total();
            $profit = NULL;
            if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
                $discountHelper = new DiscountHelper();
                $discounts = $discountHelper->check();
                $total = $discounts->total;
                $profit = $discounts->profit;
            }
            return response()->json(['clear_total' => Cart::instance('main')->total(),
                'subtotal' => $subtotal, 'total' => $total, 'profit' => $profit]);
        }
        throw LarrockCartException::withMessage('not valid data input');
    }

    /**
     * Remove a row from the cart
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartRemove(Request $request)
    {
        Cart::instance('main')->remove($request->get('rowid'));
        return response(Cart::instance('main')->total());
    }

    /**
     * Remove a row from the cart
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cartCount()
    {
        Cart::instance('main')->count();
        return response('OK');
    }

    /**
     * Страница договора-оферты магазина
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function oferta()
    {
        return view(config('larrock.views.cart.oferta', 'larrock::front.cart.oferta'));
    }

    /**
     * Удаление заказа
     * @param Request $request
     * @param $id
     * @return $this
     * @throws \Exception
     */
    public function removeOrder(Request $request, $id)
    {
        $order = LarrockCart::getModel()->find($id);
        if($order->delete()){
            $this->changeTovarStatus($order->items);
            $order->status_order = 'Удален';
            $cartMail = new CartMail();
            $cartMail->mailOrder($request, $order);
            MessageLarrock::success(\Lang::get('larrock::cart.cancel', ['number' => $id]));
        }else{
            MessageLarrock::danger(\Lang::get('larrock::cart.cancel_fail', ['number' => $id]));
        }
        return back()->withInput();
    }
}