<?php

namespace Larrock\ComponentCart;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Larrock\Core\Component;
use Larrock\Core\Traits\ShareMethods;
use Mail;
use Session;
use Spatie\MediaLibrary\Media;
use Validator;
use View;
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
	public function index()
	{
        $data['data'] = LarrockCart::getModel()->with(['get_user'])->latest()->paginate(30);
        $data['catalog'] = LarrockCatalog::getModel()->whereActive(1)->get(['id', 'title', 'cost']);
        $data['users'] = LarrockUsers::getModel()->all();
        return view('larrock::admin.cart.list', $data);
	}

	public function create(Request $request)
	{
		$add_data = LarrockCart::getModel();
		$add_data->user_id = $request->user()->id;
		$add_data->order_id = LarrockCart::getModel()->max('order_id') +1;
		if($add_data->save()){
            Session::push('message.success', 'Ошибка. Новый заказ не создан');
			return back();
		}
        Session::push('message.danger', 'Заказ #'. $add_data->order_id .' создан. Обязательно пересохраните заказ с параметрами!');
        return back();
	}

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

        \Cart::instance('temp')->add(str_slug($tovar->title), $tovar->title, $qty, $tovar->cost, $options)->associate(\LarrockCatalog::getModelName());
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
        $mails[] = $order->email;

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
}