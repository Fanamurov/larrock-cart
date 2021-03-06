<?php

namespace Larrock\ComponentCart;

use Cache;
use Larrock\ComponentCatalog\Models\Catalog;
use LarrockCart;
use Larrock\Core\Component;
use Larrock\ComponentCart\Models\Cart;
use Larrock\Core\Helpers\FormBuilder\FormTags;
use Larrock\Core\Helpers\FormBuilder\FormInput;
use Larrock\Core\Helpers\FormBuilder\FormHidden;
use Larrock\Core\Helpers\FormBuilder\FormSelect;
use Larrock\Core\Helpers\FormBuilder\FormTextarea;

class CartComponent extends Component
{
    public function __construct()
    {
        $this->active = true;
        $this->name = $this->table = 'cart';
        $this->title = 'Заказы';
        $this->description = 'Заказы с интернет-магазина';
        $this->model = \config('larrock.models.cart', Cart::class);
        $this->addRows();
    }

    protected function addRows()
    {
        $row = new FormInput('order_id', 'ID заказа');
        $this->setRow($row->setValid('max:255')->setInTableAdmin()->setFillable());

        $row = new FormSelect('status_order', 'Статус заказа');
        $this->setRow($row->setValid('max:255')->setDefaultValue('Обрабатывается')
            ->setOptions(['Обрабатывается', 'Обработано', 'Готов к выдаче', 'Отменен', 'Завершен'])
            ->setFillable()->setTemplateAdmin('status'));

        $row = new FormSelect('status_pay', 'Статус оплаты');
        $this->setRow($row->setValid('max:255')->setDefaultValue('Не оплачено')
            ->setOptions(['Не оплачено', 'Оплачено'])->setInTableAdmin()->setFillable()->setTemplateAdmin('status'));

        $row = new FormSelect('method_pay', 'Метод оплаты');
        $this->setRow($row->setValid('max:255')
            ->setDefaultValue('наличными')
            ->setOptions(['наличными', 'Visa, Mastercard (через сервис Яндекс.Касса)'])
            ->setTemplateAdmin('status')->setFillable());

        $row = new FormSelect('method_delivery', 'Метод доставки');
        $this->setRow($row->setValid('max:255')
            ->setDefaultValue('самовывоз')
            ->setOptions(['самовывоз' => 0,
                'курьером (в черте города)' => 300,
                'доставка по России' => 700, ])->setTemplateAdmin('status')->setFillable());

        $row = new FormHidden('user', 'ID покупателя');
        $this->setRow($row->setFillable()->setTemplateAdmin('user_info'));

        $row = new FormInput('fio', 'ФИО получателя');
        $this->setRow($row->setFillable()->setTemplateAdmin('user_info'));

        $row = new FormInput('tel', 'Телефон');
        $this->setRow($row->setFillable()->setTemplateAdmin('user_info'));

        $row = new FormInput('email', 'Email/login');
        $this->setRow($row->setValid('email|min:4')->setTemplateAdmin('user_info')->setFillable());

        $row = new FormTextarea('address', 'Адрес доставки');
        $this->setRow($row->setFillable()->setTemplateAdmin('user_info'));

        $row = new FormInput('cost', 'Стоимость заказа');
        $this->setRow($row->setDefaultValue(0)->setFillable());

        $row = new FormTags('items', 'Товары в заказе');
        $this->setRow($row->setModels(Cart::class, \Larrock\ComponentCatalog\Models\Catalog::class)->setFillable());

        $row = new FormTextarea('comment', 'Комментарий заказчика');
        $this->setRow($row->setFillable()->setTemplateAdmin('user_info'));

        $row = new FormTextarea('comment_admin', 'Комментарий продавца');
        $this->setRow($row->setFillable()->setTemplateAdmin('status'));

        $row = new FormInput('cost_delivery', 'Стоимость доставки');
        $this->setRow($row->setFillable()->setCssClassGroup('uk-width-1-1 uk-width-1-2@m'));

        return $this;
    }

    public function renderAdminMenu()
    {
        $count = \Cache::rememberForever('count-data-admin-'.LarrockCart::getName(), function () {
            return LarrockCart::getModel()->count(['id']);
        });

        $count_new = \Cache::rememberForever('count-new-data-admin-'.LarrockCart::getName(), function () {
            return LarrockCart::getModel()->where('status_order', '!=', 'Завершен')->where('status_order', '!=', 'Отменен')->count(['id']);
        });

        return view('larrock::admin.sectionmenu.types.default', ['count' => $count_new.'/'.$count,
            'app' => LarrockCart::getConfig(), 'url' => '/admin/'.LarrockCart::getName(), ]);
    }

    public function toDashboard()
    {
        $data = Cache::rememberForever('LarrockCartItemsDashboard', function () {
            return LarrockCart::getModel()->latest('updated_at')->take(5)->get();
        });

        return view('larrock::admin.dashboard.cart', ['component' => LarrockCart::getConfig(), 'data' => $data]);
    }
}
