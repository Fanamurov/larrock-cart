@extends('larrock::front.main')
@section('title') Корзина заказа. Оформление покупки {!! env('MAIL_TO_ADMIN_NAME') !!} @endsection

@section('content')
    <form class="cart-page" id="cart-page">
        <h1>Корзина товаров </h1>
        <table class="uk-table">
            <thead>
            <tr class="uk-hidden-small">
                <th></th>
                <th></th>
                <th>Количество</th>
                <th>Цена</th>
                <th class="uk-text-right">Итого</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($cart as $row)
                <tr class="cart_item_row" data-rowid="{{ $row->rowId }}">
                    <td class="tovar_image uk-hidden-small">
                        @if($row->model->getFirstImage)
                            <a href="{{ $row->model->getFirstImage->getUrl() }}" target="_blank">
                                <img src="{{ $row->model->getFirstImage->getUrl('140x140') }}" alt="{{ $row->name }}" class="all-width">
                            </a>
                        @endif
                    </td>
                    <td class="description-row">
                        @if($row->model->getFirstImage)
                            <div class="uk-hidden-medium uk-hidden-large">
                                <a href="{{ $row->model->getFirstImage->getUrl() }}" target="_blank">
                                    <img src="{{ $row->model->getFirstImage->getUrl('140x140') }}" alt="{{ $row->name }}" class="all-width">
                                </a>
                            </div>
                        @endif
                        @if(config('larrock.catalog.ShowItemPage') === true)
                            <p class="uk-h4"><a href="{{ $row->model->full_url }}">{{ $row->name }}</a></p>
                        @else
                            <p class="uk-h4">{{ $row->name }}</p>
                        @endif
                        <div class="item-options">
                            @foreach($app->rows as $row_key => $config_row)
                                @if($config_row->template === 'in_card' && isset($row->model->{$row_key}) && !empty($row->model->{$row_key}))
                                    <p><span class="uk-text-muted">{{ $config_row->title }}:</span> {{ $row->model->{$row_key} }}</p>
                                @endif
                            @endforeach
                        </div>
                    </td>
                    <td class="spinner-row">
                        <div class="uk-button-group input-group-qty spinner-qty" data-trigger="spinner" data-cost="{{ $row->price }}" data-rowid="{{ $row->rowId }}">
                            <button class="addon-x uk-button" data-spin="down">-</button>
                            <input type="text" class="uk-form-controls editQty" id="kolvo-{{ $row->id }}" name="qty_{{ $row->rowId }}" value="{{ $row->qty }}"
                                   data-rule="quantity" @if(isset($row->model->id) && $row->model->nalichie > 0) data-max="{{ $row->model->nalichie }}" @endif
                                   data-min="1" step="1" data-rowid="{{ $row->rowId }}">
                            <button class="addon-what uk-button" data-spin="up">+</button>
                        </div>
                        <div class="subtotal uk-hidden-medium uk-hidden-large">
                            @if($row->price > 0)
                                <small class="uk-text-muted">x</small> <span class="price-item">{{ $row->price }}</span> <small class="uk-text-muted">=</small>
                                <span class="subtotal">{{ $row->subtotal }}</span> руб.
                            @else
                                <small class="uk-text-muted subtotal">договорная</small>
                            @endif
                        </div>
                        <button type="button" class="removeCartItem uk-button uk-button-danger uk-hidden-medium uk-hidden-large uk-width-1-1 button-remove-phone" data-rowid="{{ $row->rowId }}">Удалить</button>
                    </td>
                    <td class="cost-row uk-hidden-small">
                        @if($row->price > 0)
                            <small class="uk-text-muted">x</small> <span class="price-item">{{ $row->price }}</span> <small class="uk-text-muted">=</small>
                        @else
                            <small class="uk-text-muted">договорная</small>
                        @endif
                    </td>
                    <td class="subtotal uk-hidden-small uk-text-right">
                        @if($row->price > 0)
                            <span class="subtotal">{{ $row->subtotal }}</span> руб.
                        @else
                            <small class="uk-text-muted subtotal">договорная</small>
                        @endif
                    </td>
                    <td class="uk-hidden-small uk-text-right"><button type="button" class="removeCartItem uk-button uk-button-danger uk-button-small" data-rowid="{{ $row->rowId }}">Удалить</button></td>
                </tr>
            @endforeach
            @if(isset($discount) && $discount['profit'] > 0)
                <tr class="total-row uk-hidden">
                    <td colspan="6">
                        <p class="uk-text-right row-total uk-text-muted">Сумма: <strong class="total">{!! Cart::instance('main')->total() !!}</strong> руб.</p>
                    </td>
                </tr>
                <tr class="discount_row">
                    <td colspan="6">
                        <p class="uk-text-right row-total">Всего к оплате со скидкой: <strong class="total_discount uk-h1">{!! $discount['cost_after_discount'] !!}</strong> руб.</p>
                        @if(array_key_exists('cart', $discount['discount']))
                            <div class="discount-row-text uk-text-right uk-text-success">
                                {!! $discount['discount']['cart']->description !!} &mdash;
                                @if((integer)$discount['discount']['cart']->percent > 0)
                                    {!! $discount['discount']['cart']->percent !!}%
                                @endif
                                @if((integer)$discount['discount']['cart']->num > 0)
                                    {!! $discount['discount']['cart']->num !!} руб.
                                @endif
                            </div>
                        @endif
                        @if(array_key_exists('history', $discount['discount']))
                            <div class="discount-row-text uk-text-right uk-text-success">
                                {!! $discount['discount']['history']->description !!} &mdash;
                                @if((integer)$discount['discount']['history']->percent > 0)
                                    {!! $discount['discount']['history']->percent !!}%
                                @endif
                                @if((integer)$discount['discount']['history']->num > 0)
                                    {!! $discount['discount']['history']->num !!} руб.
                                @endif
                            </div>
                        @endif
                        @if(array_key_exists('category', $discount['discount']))
                            <div class="discount-row-text uk-text-right uk-text-success">
                                {!! $discount['discount']['category']->description !!} &mdash;
                                @if((integer)$discount['discount']['category']->percent > 0)
                                    {!! $discount['discount']['category']->percent !!}%
                                @endif
                                @if((integer)$discount['discount']['category']->num > 0)
                                    {!! $discount['discount']['category']->num !!} руб.
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
            @else
                <tr class="total-row">
                    <td colspan="6">
                        <p class="uk-text-right row-total">Всего к оплате: <strong class="total">{!! Cart::instance('main')->total() !!}</strong> руб.</p>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </form>

    @if(isset($discount) && count($discount_motivate) > 0)
    <div class="uk-grid motivate-container">
        <div class="uk-width-1-1">
            <p class="uk-h2">Накопительные скидки в корзине:</p>
            <ul class="motivate_list">
            @foreach($discount_motivate as $motivate)
                @if($motivate->cost_min < Cart::instance('main')->total())
                    <li class="uk-text-success">
                        {!! $motivate->description !!} &mdash; ваша скидка
                        @if((integer)$motivate->percent > 0)
                            {!! $motivate->percent !!}%
                        @endif
                        @if((integer)$motivate->num > 0)
                            {!! $motivate->num !!} руб.
                        @endif
                    </li>
                @else
                    <li>
                        {!! $motivate->description !!}

                        @if($motivate->cost_min > Cart::instance('main')->total())
                            Добавьте в корзину товаров на сумму {!! $motivate->cost_min - Cart::instance('main')->total() !!} рублей и получите скидку
                        @endif

                        @if((integer)$motivate->percent > 0)
                            {!! $motivate->percent !!}%.
                        @endif
                        @if((integer)$motivate->num > 0)
                            {!! $motivate->num !!} руб.
                        @endif
                    </li>
                @endif
            @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="uk-grid uk-margin-large-top">
        <div class="uk-width-1-1">
            @include('larrock::front.modules.forms.createOrder')
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript" src="/_assets/bower_components/jquery.spinner/js/jquery.spinner.js"></script>
<script>
    rebuild_cost();
</script>
@endpush