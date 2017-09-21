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
                        <p class="uk-h4"><a href="{{ $row->model->full_url }}">{{ $row->name }}</a></p>
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
                        @if(isset($row->model->id))
                            <p class="uk-hidden">
                                @if($row->model->arrival_date > \Carbon\Carbon::now())
                                    <small class="uk-text-muted">дата поступления: {{ $row->model->arrival_date->format('d.m.Y') }}г.</small>
                                @else
                                    @if($row->model->nalichie > 0)
                                        <small class="uk-text-muted">в наличии <span class="nalichie" data-count="{{ $row->model->nalichie }}">{{ $row->model->nalichie }}</span> шт.</small>
                                    @else
                                        <small class="uk-text-muted">под заказ</small>
                                    @endif
                                @endif
                            </p>
                        @endif
                        <div class="subtotal uk-hidden-medium uk-hidden-large">
                            <small class="uk-text-muted">x</small> <span class="price-item">{{ number_format($row->price, 2, ',', ' ') }}</span> <small class="uk-text-muted">=</small>
                            <span>{{ number_format($row->subtotal, 2, ',', ' ') }}</span> руб.
                        </div>
                        <button type="button" class="removeCartItem uk-button uk-button-danger uk-hidden-medium uk-hidden-large uk-width-1-1 button-remove-phone" data-rowid="{{ $row->rowId }}">Удалить</button>
                    </td>
                    <td class="cost-row uk-hidden-small"><small class="uk-text-muted">x</small> <span class="price-item">{{ number_format($row->price, 2, ',', ' ') }}</span> <small class="uk-text-muted">=</small></td>
                    <td class="subtotal uk-hidden-small uk-text-right"><span>{{ number_format($row->subtotal, 2, ',', ' ') }}</span> руб.</td>
                    <td class="uk-hidden-small uk-text-right"><button type="button" class="removeCartItem uk-button uk-button-danger uk-button-small" data-rowid="{{ $row->rowId }}">Удалить</button></td>
                </tr>
            @endforeach
            @if(isset($discount) && $discount['profit'] > 0)
                <tr>
                    <td colspan="6">
                        <p class="uk-text-right row-total uk-text-muted">Сумма: <strong class="total">{!! Cart::instance('main')->total() !!}</strong> руб.</p>
                    </td>
                </tr>
                <tr class="discount_row">
                    <td colspan="6">
                        <p class="uk-text-right row-total">Всего к оплате со скидкой: <strong class="total_discount">{!! $discount['cost_after_discount'] !!}</strong> руб.</p>
                    @if(array_key_exists('cart', $discount['discount']))
                            <div class="small uk-text-right"><sup>*</sup>{{ $discount['discount']['cart']->description }}</div>
                    @endif
                    @if(array_key_exists('history', $discount['discount']))
                            <div class="small uk-text-right"><sup>*</sup>{{ $discount['discount']['history']->description }}</div>
                    @endif
                    @if(array_key_exists('category', $discount['discount']))
                        <div class="small uk-text-right"><sup>*</sup>{{ $discount['discount']['category']->description }}</div>
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
    <div class="uk-grid">
        <div class="uk-width-1-1">
            <p class="uk-h4">Накопительные скидки в корзине:</p>
            <ul>
            @foreach($discount_motivate as $motivate)
                <li>
                    {{ $motivate->description }}
                    @if($motivate->cost_min < Cart::instance('main')->total())
                        Применена!
                    @else
                        Добавьте в корзину товаров на сумму {!! $motivate->cost_min - Cart::instance('main')->total() !!} рублей
                    @endif
                </li>
            @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="uk-grid uk-margin-large-top">
        <div class="uk-width-1-1">
            @include('larrock::front.modules.forms.orderFull')
        </div>
    </div>
@endsection

@push('scripts')
<script>
    rebuild_cost();
</script>
@endpush