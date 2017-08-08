<?php

namespace Larrock\ComponentCart;

use Alert;
use Breadcrumbs;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Larrock\Core\Component;
use Mail;
use Validator;
use View;
use Cart;
use Larrock\ComponentCart\Facades\LarrockCart;
use Larrock\ComponentCatalog\Facades\LarrockCatalog;
use Larrock\ComponentUsers\Facades\LarrockUsers;

class AdminCartController extends Controller
{
	public function __construct()
	{
        $this->config = LarrockCart::shareConfig();

        Breadcrumbs::setView('larrock::admin.breadcrumb.breadcrumb');
        Breadcrumbs::register('admin.'. LarrockCart::getName() .'.index', function($breadcrumbs){
            $breadcrumbs->push(LarrockCart::getTitle(), '/admin/'. LarrockCart::getName());
        });
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
		$add_data->order_id = ModelCart::max('order_id') +1;
		if($add_data->save()){
			Alert::add('successAdmin', 'Ошибка. Новый заказ не создан')->flash();
			return back();
		}
        Alert::add('errorAdmin', 'Заказ #'. $add_data->order_id .' создан. Обязательно пересохраните заказ с параметрами!')->flash();
        return back();
	}

	public function removeItem(Request $request)
	{
		$id = $request->get('id');
		$order = LarrockCart::getModel()->whereOrderId($request->get('order_id'))->firstOrFail();
		$items = collect($order->items);
		$order->cost -= $order->items->{$id}->subtotal;
		$order->items = $items->forget($request->get('id'));
		if($order->save()){
			Alert::add('successAdmin', 'Заказ #'. $order->order_id .' изменен')->flash();
			$this->mailFullOrderChange($request, $order);
			\Cache::flush();
			return back();
		}

		Alert::add('errorAdmin', 'Заказ #'. $order->order_id .' не изменен')->flash();
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
			Alert::add('successAdmin', 'Заказ #'. $data->order_id .' изменен')->flash();
			\Cache::flush();
			return back();
		}

		Alert::add('errorAdmin', 'Заказ #'. $data->order_id .' не изменен')->flash();
		return back()->withInput();
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

		//dd($items);
		$order->items = json_encode($items);
		$order->cost += $items->{$id}->subtotal;

		$tovar = LarrockCatalog::getModel()->whereId($items->{$id}->id)->first();
		$tovar->nalichie += $request->get('old-qty', 1);
		$tovar->nalichie -= $items->{$id}->qty;
		$tovar->sales -= $request->get('old-qty', 1);
		$tovar->sales += $items->{$id}->qty;
		if($tovar->nalichie < 0){
			Alert::add('errorAdmin', 'Недостаточно товара в наличии для изменения заказа. Не хватает: '. $tovar->nalichie .'шт.')->flash();
			return back();
		}

		if($order->save()){
			$this->mailFullOrderChange($request, $order);
			//Меняем количество товара в остатке и кол-во продаж
			if($tovar->save()){
				Alert::add('successAdmin', 'Остатки товара изменены')->flash();
			}else{
				Alert::add('errorAdmin', 'Остатки товара не списаны')->flash();
			}

			Alert::add('successAdmin', 'Заказ #'. $order->order_id .' изменен')->flash();
			\Cache::flush();
			return back();
		}

		Alert::add('errorAdmin', 'Заказ #'. $order->order_id .' не изменен')->flash();
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
		if($data->delete()){
			$this->mailFullOrderDelete($data);
			Alert::add('successAdmin', 'Заказ успешно удален')->flash();
			\Cache::flush();
		}else{
			Alert::add('errorAdmin', 'Заказ не удален')->flash();
		}
		return back();
	}

	/**
	 * Отправка email'а об удалении заказа
	 * @param         $order
	 */
	public function mailFullOrderDelete($order)
	{
		//FormsLog::create(['formname' => 'order', 'params' => $request->all(), 'status' => 'Новое']);
		$order->status_order = 'Удален';

		$mails = collect(array_map('trim', explode(',', env('MAIL_TO_ADMIN', 'robot@martds.ru'))));

		$subject = 'Заказ #'. $order->order_id .' на сайте '. env('SITE_NAME', array_get($_SERVER, 'HTTP_HOST')) .' удален';
        /** @noinspection PhpVoidFunctionResultUsedInspection */
		Mail::send('emails.orderFull-delete', ['data' => $order->toArray(), 'subject' => $subject],
			function($message) use ($mails, $subject){
				$message->from('no-reply@'. array_get($_SERVER, 'HTTP_HOST'), env('MAIL_TO_ADMIN_NAME', 'ROBOT'));
				foreach($mails as $value){
					$message->to($value);
				}
				$message->subject($subject);
			});

        Alert::add('successAdmin', 'На email покупателя отправлено письмо с деталями заказа')->flash();
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
		//FormsLog::create(['formname' => 'order', 'params' => $request->all(), 'status' => 'Новое']);

		$mails = collect(array_map('trim', explode(',', env('MAIL_TO_ADMIN', 'robot@martds.ru'))));

		if( !$subject){
			$subject = 'Заказ #'. $order->order_id .' на сайте '. env('SITE_NAME', array_get($_SERVER, 'HTTP_HOST')) .' изменен';
		}
        /** @noinspection PhpVoidFunctionResultUsedInspection */
		Mail::send('emails.orderFull-delete', ['data' => $order->toArray(), 'subject' => $subject],
			function($message) use ($mails, $subject){
				$message->from('no-reply@'. array_get($_SERVER, 'HTTP_HOST'), env('MAIL_TO_ADMIN_NAME', 'ROBOT'));
				foreach($mails as $value){
					$message->to($value);
				}
				$message->subject($subject);
			});

        Alert::add('successAdmin', 'На email покупателя отправлено письмо с деталями заказа')->flash();
	}

	public function docCheck($id)
	{
		$data['data'] = LarrockCart::getModel()->whereOrderId($id)->firstOrFail();
		$data['all_cost_string'] = $this->num_propis($data['data']->cost);
		return view('larrock::admin.cart.doc_check', $data);
	}

	public function docDelivery($id)
	{
		$data['data'] = LarrockCart::getModel()->whereOrderId($id)->firstOrFail();
		return view('larrock::admin.cart.doc_delivery', $data);
	}

	/** Пишем сумму прописью */
	public function num_propis($num)
	{ // $num - целое число
		$explode = explode('.', $num);
		if(isset($explode['1'])){
			$num = $explode['0'];
			$cop = $explode['1'];
		}else{
			$cop = 00;
		}
		# Все варианты написания чисел прописью от 0 до 999 скомпануем в один небольшой массив
		$m=array(
			array('ноль'),
			array('-','один','два','три','четыре','пять','шесть','семь','восемь','девять'),
			array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать','пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать'),
			array('-','-','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
			array('-','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот'),
			array('-','одна','две')
		);

		# Все варианты написания разрядов прописью скомпануем в один небольшой массив
		$r=array(
			array('...ллион','','а','ов'), // используется для всех неизвестно больших разрядов
			array('тысяч','а','и',''),
			array('миллион','','а','ов'),
			array('миллиард','','а','ов'),
			array('триллион','','а','ов'),
			array('квадриллион','','а','ов'),
			array('квинтиллион','','а','ов')
			// ,array(... список можно продолжить
		);

		if($num==0)return$m[0][0]; # Если число ноль, сразу сообщить об этом и выйти
		$o=array(); # Сюда записываем все получаемые результаты преобразования

		# Разложим исходное число на несколько трехзначных чисел и каждое полученное такое число обработаем отдельно
		foreach(array_reverse(str_split(str_pad($num,ceil(strlen($num)/3)*3,'0',STR_PAD_LEFT),3))as$k=>$p){
			$o[$k]=array();

			# Алгоритм, преобразующий трехзначное число в строку прописью
			foreach($n=str_split($p)as$kk=>$pp)
				if(!$pp)continue;else
					switch($kk){
						case 0:$o[$k][]=$m[4][$pp];break;
						case 1:if($pp==1){$o[$k][]=$m[2][$n[2]];break 2;}else$o[$k][]=$m[3][$pp];break;
						case 2:if(($k==1)&&($pp<=2))$o[$k][]=$m[5][$pp];else$o[$k][]=$m[1][$pp];break;
					}$p*=1;if(!$r[$k])$r[$k]=reset($r);

			# Алгоритм, добавляющий разряд, учитывающий окончание руского языка
			if($p&&$k)switch(true){
				case preg_match("/^[1]$|^\\d*[0,2-9][1]$/",$p):$o[$k][]=$r[$k][0].$r[$k][1];break;
				case preg_match("/^[2-4]$|\\d*[0,2-9][2-4]$/",$p):$o[$k][]=$r[$k][0].$r[$k][2];break;
				default:$o[$k][]=$r[$k][0].$r[$k][3];break;
			}$o[$k]=implode(' ',$o[$k]);
		}

		$output['rub'] = implode(' ',array_reverse($o));
		$output['cop'] = $cop;

		return $output;
	}
}