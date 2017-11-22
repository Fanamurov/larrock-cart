<?php

namespace Larrock\ComponentCart;

use Larrock\Core\Component;
use Larrock\Core\Helpers\FormBuilder\FormCatalogItems;
use Larrock\Core\Helpers\FormBuilder\FormHidden;
use Larrock\Core\Helpers\FormBuilder\FormInput;
use Larrock\Core\Helpers\FormBuilder\FormSelect;
use Larrock\Core\Helpers\FormBuilder\FormTextarea;
use Larrock\ComponentCart\Models\Cart;
use Larrock\ComponentCart\Facades\LarrockCart;

class CartComponent extends Component
{
    public function __construct()
    {
        $this->active = TRUE;
        $this->name = $this->table = 'cart';
        $this->title = 'Заказы';
        $this->description = 'Заказы с интернет-магазина';
        $this->model = \config('larrock.models.cart', Cart::class);
        $this->addRows()->isSearchable();
    }

    protected function addRows()
    {
        $row = new FormInput('order_id', 'ID заказа');
        $this->rows['order_id'] = $row->setValid('max:255')->setInTableAdmin()->setFillable();

        $row = new FormSelect('status_order', 'Статус заказа');
        $this->rows['status_order'] = $row->setValid('max:255')->setDefaultValue('Обрабатывается')
            ->setOptions(['Обрабатывается', 'Обработано', 'Готов к выдаче', 'Отменен', 'Завершен'])->setInTableAdmin()->setFillable();

        $row = new FormSelect('status_pay', 'Статус оплаты');
        $this->rows['status_pay'] = $row->setValid('max:255')->setDefaultValue('Не оплачено')
            ->setOptions(['Не оплачено', 'Оплачено'])->setInTableAdmin()->setFillable();

        $row = new FormSelect('method_pay', 'Метод оплаты');
        $this->rows['method_pay'] = $row->setValid('max:255')
            ->setDefaultValue('наличными')
            ->setOptions(['наличными', 'Visa, Mastercard (через сервис Яндекс.Касса)'])->setFillable();

        $row = new FormSelect('method_delivery', 'Метод доставки');
        $this->rows['method_delivery'] = $row->setValid('max:255')
            ->setDefaultValue('самовывоз')
            ->setOptions(['самовывоз', 'курьером (в черте Хабаровска)', 'доставка по России'])->setFillable();

        $row = new FormTextarea('address', 'Адрес доставки');
        $this->rows['address'] = $row->setFillable();

        $row = new FormInput('fio', 'ФИО получателя');
        $this->rows['fio'] = $row->setFillable();

        $row = new FormHidden('user', 'ID покупателя');
        $this->rows['user'] = $row->setFillable();

        $row = new FormInput('tel', 'Телефон');
        $this->rows['tel'] = $row->setFillable();

        $row = new FormInput('email', 'Email/login');
        $this->rows['email'] = $row->setValid('email|min:4')->setInTableAdmin()->setFillable();

        $row = new FormInput('cost', 'Стоимость заказа');
        $this->rows['cost'] = $row->setDefaultValue(0)->setInTableAdmin()->setFillable();

        $row = new FormInput('cost_discount', 'Стоимость со скидкой');
        $this->rows['cost_discount'] = $row->setDefaultValue(0)->setFillable();

        $row = new FormCatalogItems('items', 'Товары в заказе');
        $this->rows['items'] = $row->setFillable();;

        $row = new FormTextarea('comment', 'Комментарий заказчика');
        $this->rows['comment'] = $row->setFillable();

        $row = new FormTextarea('comment_admin', 'Комментарий продавца');
        $this->rows['comment_admin'] = $row->setFillable();

        return $this;
    }

    public function renderAdminMenu()
    {
        $count = \Cache::remember('count-data-admin-'. LarrockCart::getName(), 1440, function(){
            return LarrockCart::getModel()->count(['id']);
        });

        $count_new = \Cache::remember('count-new-data-admin-'. LarrockCart::getName(), 1440, function(){
            return LarrockCart::getModel()->whereStatusOrder('Обрабатывается')->count(['id']);
        });
        return view('larrock::admin.sectionmenu.types.default', ['count' => $count .'/'. $count_new, 'app' => LarrockCart::getConfig(), 'url' => '/admin/'. LarrockCart::getName()]);
    }

    public function toDashboard()
    {
        return view('larrock::admin.dashboard.cart', ['component' => LarrockCart::getConfig(), 'data' => LarrockCart::getModel()->latest('created_at')->get()]);
    }
}