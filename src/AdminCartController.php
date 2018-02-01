<?php

namespace Larrock\ComponentCart;

use Cart;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Larrock\ComponentCatalog\CatalogComponent;
use Larrock\Core\Component;
use Larrock\Core\Helpers\MessageLarrock;
use Larrock\Core\Models\Link;
use Larrock\Core\Traits\ShareMethods;
use Mail;
use Session;
use Spatie\MediaLibrary\Media;
use Validator;
use View;
use Cache;
use Larrock\ComponentCart\Facades\LarrockCart;
use Larrock\ComponentCatalog\Facades\LarrockCatalog;
use Larrock\ComponentUsers\Facades\LarrockUsers;

class AdminCartController extends Controller
{
    use ShareMethods;

    protected $config;
    
	public function __construct()
	{
	    $this->shareMethods();
        $this->middleware(LarrockCart::combineAdminMiddlewares());
        $this->config = LarrockCart::shareConfig();
        \Config::set('breadcrumbs.view', 'larrock::admin.breadcrumb.breadcrumb');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return View
	 */
	public function index(Request $request)
	{
	    if($request->has('user_search')){
            $data['data'] = $this->filter($request);
        }else{
            $data['data'] = LarrockCart::getModel()->with(['get_user'])->latest()->paginate(30);
        }

        $cache_key = sha1('catalogItemsAll');
        $data['catalog'] = Cache::remember($cache_key, 1140, function () {
            return LarrockCatalog::getModel()->whereActive(1)->get(['id', 'title', 'cost', 'what']);
        });
        $data['users'] = LarrockUsers::getModel()->all();
        return view('larrock::admin.cart.list', $data);
	}

    /**
     * Фильтры для вывода заказов
     * @param Request $request
     * @return mixed
     */
	protected function filter(Request $request)
    {
        $query = LarrockCart::getModel()->with(['get_user']);

        if( !empty($request->get('order_id'))){
            $query->where('order_id', '=', $request->get('order_id'));
        }
        if( !empty($request->get('date_start'))){
            $query->where('created_at', '>=', $request->get('date_start'));
        }
        if( !empty($request->get('date_end'))){
            $query->where('created_at', '<=', $request->get('date_end'));
        }
        if( !empty($request->get('status_order'))){
            $query->whereStatusOrder($request->get('status_order'));
        }
        if( !empty($request->get('status_pay'))){
            $query->whereStatusPay($request->get('status_pay'));
        }

        //Поиск по полям юзеров
        if( !empty($request->get('user_search'))){
            $user_rows = ['fio', 'address', 'tel', 'email'];

            $query->where(function ($queryLike) use ($user_rows, $request){
                foreach ($user_rows as $key => $row){
                    if($key === 0){
                        $queryLike->where($row, 'like', '%'. $request->get('user_search') .'%');
                    }else{
                        $queryLike->orWhere($row, 'like', '%'. $request->get('user_search') .'%');
                    }
                }
            });
        }

        MessageLarrock::success('Найдено '. $query->count() .' записей');

        return $query->latest()->paginate(30);
    }

    /**
     * Визард создания нового заказа из админки
     * @param Request $request
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
	public function create(Request $request)
	{
        $data['catalog'] = LarrockCatalog::getModel()->whereActive(1)->get();
        $data['users'] = LarrockUsers::getModel()->all();

	    return view('larrock::admin.cart.createOrder', $data);
	}

    /**
     * Получение товаров для добавления через визард
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
	public function getTovarForCreate(Request $request)
    {
        if($get_tovar = LarrockCatalog::getModel()->whereActive(1)->whereId($request->get('id'))->with(['get_category'])->first()){
            foreach ($get_tovar->get_category as $item_category){
                foreach ($item_category->parent_tree as $category){
                    if($category->active !== 1){
                        return response('Товар находится в неопубликованном разделе', 404);
                    }
                }
            }
            return view('larrock::admin.cart.createOrderItem', ['data' => $get_tovar, 'app' => new CatalogComponent()]);
        }
        return response('Товар не найден', 404);
    }

    /**
     * ВРЕМЕННОЕ КОСТЫЛЬНОЕ РЕШЕНИЕ
     * Экшен создания заказа по внесенным из визарда данным
     * @param Request $request
     */
    public function createOrder(Request $request)
    {
        //dd($request->all());
        Cart::instance('main')->destroy();
        foreach ($request->get('tovar') as $key => $tovar){
            $params['id'] = $tovar;
            if($request->has('costValue_'. $tovar)){
                $params['costValueId'] = $request->get('costValue_'. $tovar);
            }
            //TODO:Просто скопирон метод cartAdd(). Переписать
            $get_tovar = LarrockCatalog::getModel()->whereId($tovar)->firstOrFail();

            //Модификации товаров
            $costValueId = $params['costValueId'];
            if($costValueId && (int)$costValueId > 0){
                $costValue = Link::whereId($costValueId)->first();
                $get_tovar->cost = $costValue->cost;
            }

            $cost = $get_tovar->cost;
            $qty = $request->get('qty', 1);
            $qty = $qty[$key];
            if($qty < 1){
                $qty = 1;
            }
            $options = $request->get('options', []);
            if( !empty($options)){
                $options = (array) json_decode($options);
            }
            if($costValueId && (int)$costValueId > 0){
                $link = Link::whereId($costValueId)->first();
                if($searchParam = $link->model_child::whereId($link->id_child)->first()){
                    $searchParam['className'] = $link->model_child;
                    $options['costValue'] = $searchParam->toArray();
                }
            }

            $cartid = Cart::instance('main')->search(function ($cartItem, $rowId) use ($request) {
                //return $cartItem->id === $request->get('id');
            });
            if($cartid === false){
                $cartid = Cart::instance('main')->search(function ($cartItem, $rowId) use ($request) {
                    //return $cartItem->id === (int)$request->get('id');
                });
            }
            if(isset($cartid[0])){
                if((int)$get_tovar['nalichie'] > 0 && (int)$get_tovar['nalichie'] <= (int)Cart::instance('main')->get($cartid[0])->qty){
                    //return response()->json(['status' => 'error', 'message' => 'У вас в корзине все доступное количество товара']);
                }
            }

            $id = $params['id'];
            if($costValueId && (int)$costValueId > 0){
                $id .= '_'. $costValueId;
            }

            /** @noinspection PhpVoidFunctionResultUsedInspection */
            Cart::instance('main')->add($id, $get_tovar->title, $qty, $cost, $options)->associate(LarrockCatalog::getModelName());
            //END cartAdd
        }

        //TODO:Просто скопирон метод saveOrder(). Переписать
        $order = [];

        $cartFillableRows = LarrockCart::getFillableRows();
        foreach ($cartFillableRows as $key => $row){
            $order[$row] = $request->get($row);
        }

        if( !empty($request->get('user_id'))){
            $order['user'] = $request->get('user_id');
        }

        $order['items'] = Cart::instance('main')->content();

        $order['cost'] = (float)str_replace(',', '', Cart::instance('main')->total());
        $order['cost_discount'] = NULL;

        $order['status_order'] = $request->get('status_order');
        $order['status_pay'] = $request->get('status_pay');
        $order['kupon'] = $request->get('kupon');
        if( !$order_id = LarrockCart::getModel()->max('order_id')){
            $order_id = 1;
        }
        $order['order_id'] = ++$order_id;

        //dd($order);
        $create_order = LarrockCart::getModel()->create($order);
        Cart::instance('main')->destroy();
        Session::push('message.success', 'Ваш заказ #'. $create_order->order_id .' успешно добавлен');
        return redirect()->to('/admin/cart');
    }

    /**
     * Удаление конкретного товара из заказа
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
	public function removeItem(Request $request)
	{
		$id = $request->get('id');
		$order = LarrockCart::getModel()->whereOrderId($request->get('order_id'))->firstOrFail();
		$items = collect($order->items);
		if(isset($order->items->{$id})){
            $order->cost -= $order->items->{$id}->subtotal;
            $order->items = $items->forget($request->get('id'));
            if($order->save()){
                Session::push('message.success', 'Заказ #'. $order->order_id .' изменен');
                $this->mailFullOrderChange($request, $order);
                \Cache::flush();
                return back();
            }
        }else{
            Session::push('message.danger', 'Товар уже удален из заказа');
        }

        Session::push('message.danger', 'Заказ #'. $order->order_id .' не изменен');
		return back()->withInput();
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
	public function update(Request $request, $id)
	{
        $validator = Validator::make($request->all(), Component::_valid_construct(LarrockCart::getConfig(), 'update', $id));
        if($validator->fails()){
            return back()->withInput($request->except('password'))->withErrors($validator);
        }

		$data = LarrockCart::getModel()->find($id);
		$need_mailIt = NULL; //нужно ли отправлять уведомление по email покупателю
		$subject = NULL; //Тема письма
		if($data->status_order !== $request->get('status_order')){
			$subject = 'Статус заказа изменен на '. $request->get('status_order');
			$need_mailIt = TRUE;
		}
		if($data->status_pay !== $request->get('status_pay')){
			$subject = 'Статус оплаты заказа изменен на '. $request->get('status_pay');
			$need_mailIt = TRUE;
		}

		$data->fill($request->all());
        $data->user = $request->user()->id;

		if($data->save()){
			if($need_mailIt){
				$this->mailFullOrderChange($request, $data, $subject);
			}
            Session::push('message.success', 'Заказ #'. $data->order_id .' изменен');
			\Cache::flush();
			return back();
		}

        Session::push('message.danger', 'Заказ #'. $data->order_id .' не изменен');
		return back()->withInput();
	}

    /**
     * Добавление товара к заказу
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
	public function store(Request $request)
    {
        $id = $request->get('id');
        $qty = $request->get('kolvo');
        if( !$order = LarrockCart::getModel()->whereOrderId($request->get('order_id'))->first()){
            Session::push('message.danger', 'Такого товара на сайте нет');
            return back();
        }

        $items = $order->items;
        $tovar = \LarrockCatalog::getModel()->whereId($id)->firstOrFail();

        $options = [];
        foreach ($request->except(['id', 'kolvo', 'order_id', '_token']) as $key => $option){
            $options[$key] = $option;
        }

        \Cart::instance('temp')->add(str_slug($tovar->title), $tovar->title, $qty, $request->get('costValue'), $options)->associate(\LarrockCatalog::getModelName());
        $cart = \Cart::instance('temp')->content();

        foreach ($items as $item){
            $cart->put($item->rowId, $item);
            $order->cost += $item->qty * $item->price;
        }

        \Cart::instance('temp')->destroy();
        $order->items = $cart;

        if($order->save()){
            $this->mailFullOrderChange($request, $order);
            Session::push('message.success', 'Товар '. $tovar->title .' успешно добавлен к заказу');
        }else{
            Session::push('message.danger', 'Добавить товар к заказу не удалось');
        }

        return back();
    }

	/**
	 * Изменение количества товара в заказе
	 * @param Request $request
	 * @param         $id
	 *
	 * @return AdminCartController|\Illuminate\Http\RedirectResponse
     */
	public function editQtyItem(Request $request, $id)
	{
		$order = LarrockCart::getModel()->whereOrderId($request->get('order_id'))->firstOrFail();
		$items = $order->items;
		$items->{$id}->qty = $request->get('qty', 1);
		$order->cost -= $items->{$id}->subtotal;
		$items->{$id}->subtotal = $request->get('qty') * $items->{$id}->price;

		$order->items = json_encode($items);
		$order->cost += $items->{$id}->subtotal;

		$tovar = LarrockCatalog::getModel()->whereId($items->{$id}->id)->first();
		$tovar->nalichie += $request->get('old-qty', 1);
		$tovar->nalichie -= $items->{$id}->qty;
		$tovar->sales -= $request->get('old-qty', 1);
		$tovar->sales += $items->{$id}->qty;
		if($tovar->nalichie < 0){
            Session::push('message.danger', 'Недостаточно товара в наличии для изменения заказа. Не хватает: '. $tovar->nalichie .'шт.');
			return back();
		}

		if($order->save()){
			$this->mailFullOrderChange($request, $order);
			//Меняем количество товара в остатке и кол-во продаж
			if($tovar->save()){
                Session::push('message.success', 'Остатки товара изменены');
			}else{
                Session::push('message.danger', 'Остатки товара не списаны');
			}

            Session::push('message.success', 'Заказ #'. $order->order_id .' изменен');
			\Cache::flush();
			return back();
		}

        Session::push('message.danger', 'Заказ #'. $order->order_id .' не изменен');
		return back()->withInput();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$data = LarrockCart::getModel()->find($id);
		if( !$data){
            Session::push('message.danger', 'Такого заказа на сайте уже нет');
            return back();
        }
		if($data->delete()){
			$this->mailFullOrderDelete($data);
            Session::push('message.success', 'Заказ успешно удален');
			\Cache::flush();
		}else{
            Session::push('message.danger', 'Заказ не удален');
		}
		return back();
	}

	/**
	 * Отправка email'а об удалении заказа
	 * @param         $order
	 */
	public function mailFullOrderDelete($order)
	{
		$order->status_order = 'Удален';

		$mails = array_map('trim', explode(',', env('MAIL_TO_ADMIN', 'robot@martds.ru')));
		if( !empty($order->email)){
            $mails[] = $order->email;
        }

		$subject = 'Заказ #'. $order->order_id .' на сайте '. env('SITE_NAME', array_get($_SERVER, 'HTTP_HOST')) .' удален';
        /** @noinspection PhpVoidFunctionResultUsedInspection */
		Mail::send('larrock::emails.orderFull-delete', ['data' => $order->toArray(), 'subject' => $subject],
			function($message) use ($mails, $subject){
				$message->from('no-reply@'. array_get($_SERVER, 'HTTP_HOST'), env('MAIL_TO_ADMIN_NAME', 'ROBOT'));
                $message->to($mails);
				$message->subject($subject);
			});

        \Log::info('ORDER DELETE: #'. $order->order_id .'. Order: '. json_encode($order));
        Session::push('message.success',  'На email покупателя отправлено письмо с деталями заказа');
	}

	/**
	 * Отправка email'а об изменении заказа
	 *
	 * @param Request $request
	 * @param         $order
	 * @param null    $subject
	 */
	public function mailFullOrderChange(Request $request, $order, $subject = NULL)
	{
		$mails = array_map('trim', explode(',', env('MAIL_TO_ADMIN', 'robot@martds.ru')));
        $mails[] = $order->email;

		if( !$subject){
			$subject = 'Заказ #'. $order->order_id .' на сайте '. env('SITE_NAME', array_get($_SERVER, 'HTTP_HOST')) .' изменен';
		}
        /** @noinspection PhpVoidFunctionResultUsedInspection */
		Mail::send('larrock::emails.orderFull-delete', ['data' => $order->toArray(), 'subject' => $subject],
			function($message) use ($mails, $subject){
				$message->from('no-reply@'. array_get($_SERVER, 'HTTP_HOST'), env('MAIL_TO_ADMIN_NAME', 'ROBOT'));
                $message->to($mails);
				$message->subject($subject);
			});

        \Log::info('ORDER CHANGE: #'. $order->order_id .'. Order: '. json_encode($order));
        Session::push('message.success', 'На email покупателя отправлено письмо с деталями заказа');
	}

	public function sendNotify(Request $request)
    {
        if($order = LarrockCart::getModel()->whereOrderId($request->get('order_id'))->first()){
            if( !empty($order->email)){
                $this->mailFullOrderChange($request, $order,
                    'Уведомление о статусе заказа #'. $request->get('order_id') .' на сайте '.
                    env('SITE_NAME', array_get($_SERVER, 'HTTP_HOST')));
            }else{
                MessageLarrock::danger('У покупателя из заказа #'. $request->get('order_id') .' не указан email');
            }
        }else{
            MessageLarrock::danger('Заказа #'. $request->get('order_id') .' нет в нашей базе');
        }
        return back();
    }
}