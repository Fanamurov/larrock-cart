<?php

namespace Larrock\ComponentCart;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Larrock\ComponentCart\Helpers\CartMail;
use Larrock\ComponentCatalog\CatalogComponent;
use Larrock\ComponentDiscount\Helpers\DiscountHelper;
use Larrock\Core\Component;
use Larrock\Core\Helpers\MessageLarrock;
use Larrock\Core\Models\Link;
use Larrock\Core\Traits\ShareMethods;
use Validator;
use View;
use Cache;
use LarrockCart;
use LarrockCatalog;
use LarrockUsers;

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
     * @param Request $request
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

        $cache_key = sha1('UsersAll');
        $data['users'] = Cache::rememberForever($cache_key, function () {
            return LarrockUsers::getModel()->all();
        });
        return view('larrock::admin.cart.list', $data);
	}

    /**
     * Фильтры для вывода заказов
     * @param Request $request
     * @return mixed
     */
	protected function filter(Request $request)
    {
        /** @var \Larrock\ComponentCart\Models\Cart $query */
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
        MessageLarrock::success(\Lang::get('larrock::cart.found', ['count' => $query->count()]));
        return $query->latest()->paginate(30);
    }

    /**
     * Визард создания нового заказа из админки
     * @param Request $request
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
	public function create(Request $request)
	{
        $cache_key = sha1('catalogItemsAll');
        $data['catalog'] = Cache::remember($cache_key, 1140, function () {
            return LarrockCatalog::getModel()->whereActive(1)->get(['id', 'title', 'cost', 'what']);
        });

        $cache_key = sha1('UsersAll');
        $data['users'] = Cache::rememberForever($cache_key, function () {
            return LarrockUsers::getModel()->all();
        });
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
            $active = NULL;
            foreach ($get_tovar->get_category as $item_category){
                foreach ($item_category->parent_tree as $category){
                    if($category->active === 1 && $category->level = 1){
                        $active = TRUE;
                    }
                }
            }
            if( !$active){
                return response('Товар находится в неопубликованном разделе', 404);
            }
            return view('larrock::admin.cart.createOrderItem', ['data' => $get_tovar, 'app' => new CatalogComponent()]);
        }
        return response('Товар не найден', 404);
    }

    /**
     * ВРЕМЕННОЕ КОСТЫЛЬНОЕ РЕШЕНИЕ
     * Экшен создания заказа по внесенным из визарда данным
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function createOrder(Request $request)
    {
        \Cart::instance('temp')->destroy();
        foreach ($request->get('tovar') as $key => $tovar){
            $params['id'] = $tovar;
            if($request->has('costValue_'. $tovar)){
                $params['costValueId'] = $request->get('costValue_'. $tovar);
            }
            //TODO:Просто скопирон метод cartAdd(). Переписать
            $get_tovar = LarrockCatalog::getModel()->whereId($tovar)->firstOrFail();

            //Модификации товаров
            if(isset($params['costValueId'])){
                $costValueId = $params['costValueId'];
                if($costValueId && (int)$costValueId > 0){
                    $costValue = Link::whereId($costValueId)->firstOrFail();
                    $get_tovar->cost = $costValue->cost;
                }
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

            $id = $params['id'];

            if(isset($params['costValueId']) && $costValueId && (int)$costValueId > 0){
                $link = Link::whereId($costValueId)->firstOrFail();
                if($searchParam = $link->model_child::whereId($link->id_child)->first()){
                    $searchParam['className'] = $link->model_child;
                    $options['costValue'] = $searchParam->toArray();
                }
                $id .= '_'. $costValueId;
            }

            /** @noinspection PhpVoidFunctionResultUsedInspection */
            \Cart::instance('temp')->add($id, $get_tovar->title, $qty, $cost, $options)->associate(LarrockCatalog::getModelName());
        }

        //TODO:Просто скопирон метод saveOrder(). Переписать
        $order = LarrockCart::getModel();
        $order->fill($request->all());

        if( !empty($request->get('user_id'))){
            $order->user = $request->get('user_id');
        }

        $order->items = \Cart::instance('temp')->content();

        if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount')) {
            $discountHelper = new DiscountHelper();
            $discounts = $discountHelper->check(\Cart::instance('temp')->total(), $request->get('kupon'));
            $order->cost = $discounts->total;
            $order->discount = $discounts;
            if($discounts->d_kupon){
                $order->kupon = $discounts->d_kupon->word;
            }
            $discounts->countApplyDiscounts();
        }else{
            $order->cost = (float)str_replace(',', '', \Cart::instance('temp')->total());
        }

        $order->status_order = $request->get('status_order');
        $order->status_pay = $request->get('status_pay');
        if( !$order_id = LarrockCart::getModel()->max('order_id')){
            $order_id = 1;
        }
        $order->order_id = ++$order_id;
        $order->save();

        \Cart::instance('temp')->destroy();
        MessageLarrock::success(\Lang::get('larrock::cart.admin.order_create', ['number' => $order->order_id]), TRUE);
        MessageLarrock::success(\Lang::get('larrock::cart.admin.order_create_notice'));
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
                MessageLarrock::success(\Lang::get('larrock::cart.admin.order_change', ['number' => $order->order_id]));
                \Cache::flush();
                return back();
            }
        }else{
		    MessageLarrock::danger(\Lang::get('larrock::cart.admin.tovar_lost'));
        }

        MessageLarrock::danger(\Lang::get('larrock::cart.admin.order_not_change', ['number' => $order->order_id]));
		return back()->withInput();
	}

    /**
     * Изменение заказа
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
	public function update(Request $request, $id)
	{
        $validator = Validator::make($request->all(), Component::_valid_construct(LarrockCart::getConfig(), 'update', $id));
        if($validator->fails()){
            return back()->withInput($request->except('password'))->withErrors($validator);
        }

        /** @var \Larrock\ComponentCart\Models\Cart $data */
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
                $cartMail = new CartMail();
                $cartMail->mailOrder($request, $data, $subject);
			}
            MessageLarrock::success(\Lang::get('larrock::cart.admin.order_change', ['number' => $data->order_id]));
			\Cache::flush();
			return back();
		}
        MessageLarrock::danger(\Lang::get('larrock::cart.admin.order_not_change', ['number' => $data->order_id]));
		return back()->withInput();
	}

    /**
     * Добавление товара в уже существующий заказ
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function cartAdd(Request $request)
    {
        \Cart::instance('temp')->destroy();
        if( !$order = LarrockCart::getModel()->whereOrderId($request->get('order_id'))->first()){
            MessageLarrock::danger(\Lang::get('larrock::cart.admin.404', ['number' => $request->get('order_id')]));
            return back();
        }

        if( !$get_tovar = LarrockCatalog::getModel()->whereId($request->get('id'))->first()){
            MessageLarrock::danger(\Lang::get('larrock::cart.admin.404_tovar', ['number' => $request->get('id')]));
            return back();
        }

        //Добавляем уже существуеющие детали заказа
        foreach ($order->items as $item){
            \Cart::instance('temp')->add($item->id, $item->name, $item->qty, $item->price,
                (array)$item->options)->associate(LarrockCatalog::getModelName());
        }

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
            if($searchParam = $link->model_child::whereId($link->id_child)->first()){
                $searchParam['className'] = $link->model_child;
                $options['costValue'] = $searchParam->toArray();
            }
        }

        $id = $request->get('id');
        if($costValueId && (int)$costValueId > 0){
            $id .= '_'. $costValueId;
        }

        $cartid = \Cart::instance('temp')->search(function ($cartItem, $rowId) use ($id) {
            return $cartItem->id === $id;
        });

        /*if(isset($cartid[0])){
            if((int)$get_tovar['nalichie'] > 0 && (int)$get_tovar['nalichie'] <= (int)\Cart::instance('temp')->get($cartid[0])->qty){
                return response()->json(['status' => 'error', 'message' => 'У вас в корзине все доступное количество товара']);
            }
        }*/

        if(\count($cartid) > 0){
            $rowIdUpdate = $cartid->first()->rowId;
            $qtyUpdate = $cartid->first()->qty + $qty;
            \Cart::instance('temp')->update($rowIdUpdate, $qtyUpdate);
        }else{
            \Cart::instance('temp')->add($id, $get_tovar->title, $qty, $cost, $options)->associate(LarrockCatalog::getModelName());
        }

        $order->items = \Cart::instance('temp')->content();
        \Cart::instance('temp')->destroy();

        if($order->save()){
            MessageLarrock::success(\Lang::get('larrock::cart.admin.tovar_add', ['name' => $get_tovar->title]));
        }else{
            MessageLarrock::danger(\Lang::get('larrock::cart.admin.tovar_add_fail'));
        }
        return back();
    }

	/**
	 * Изменение количества товара в заказе
	 * @param Request $request
	 * @param         $id
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
		/*if($tovar->nalichie < 0){
		    MessageLarrock::danger('Недостаточно товара в наличии для изменения заказа. Не хватает: '. $tovar->nalichie .'шт.');
			return back();
		}*/

		if($order->save()){
			if($tovar->save()){
                MessageLarrock::success(\Lang::get('larrock::cart.admin.nalicie_change'));
			}else{
                MessageLarrock::danger(\Lang::get('larrock::cart.admin.nalicie_not_change'));
			}

            MessageLarrock::success(\Lang::get('larrock::cart.admin.order_change', ['number' => $order->order_id]));
			\Cache::flush();
			return back();
		}
        MessageLarrock::danger(\Lang::get('larrock::cart.admin.order_not_change', ['number' => $order->order_id]));
		return back()->withInput();
	}

    /**
     * Удаление заказа
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
	public function destroy(Request $request, $id)
	{
		$data = LarrockCart::getModel()->find($id);
		if( !$data){
            MessageLarrock::danger(\Lang::get('larrock::cart.404', ['number' => $id]));
            return back();
        }
		if($data->delete()){
            $data->status_order = 'Удален';
            $cartMail = new CartMail();
            $cartMail->mailOrder($request, $data);
			MessageLarrock::success(\Lang::get('larrock::cart.delete', ['number' => $id]));
			\Cache::flush();
		}else{
            MessageLarrock::danger(\Lang::get('larrock::cart.delete_fail', ['number' => $id]));
		}
		return back();
	}

    /**
     * Отправка письма покупателю
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
	public function sendNotify(Request $request)
    {
        if($order = LarrockCart::getModel()->whereOrderId($request->get('order_id'))->first()){
            $cartMail = new CartMail();
            $cartMail->mailOrder($request, $order);
        }else{
            MessageLarrock::danger(\Lang::get('larrock.cart.404', ['number' => $request->get('order_id')]));
        }
        return back();
    }
}