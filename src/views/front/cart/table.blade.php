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
                        <img src="{{ $row->model->first_image }}" class="catalogImage max-width pointer" data-id="{{ $row->model->id }}" itemprop="image">
                    </td>
                    <td class="description-row">
                        <div class="uk-hidden-medium uk-hidden-large">
                            <img src="{{ $row->model->first_image }}" class="catalogImage max-width pointer" data-id="{{ $row->model->id }}" itemprop="image">
                        </div>
                        @if(config('larrock.catalog.ShowItemPage', TRUE) === TRUE)
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
                            @foreach($row->options as $key_option => $option)
                                @if( !is_object($option) && !is_array($option))
                                    <p><span class="uk-text-muted">@lang('larrock::fields.'.$key_option):</span> {{ $option }}</p>
                                @else
                                    <p class="{{$key_option}}-param">
                                        @if(array_key_exists('className', $option))
                                            <span class="uk-form-label">@lang('larrock::fields.'.$option['className']):</span>
                                        @else
                                            <span class="uk-form-label">@lang('larrock::fields.'.$key_option):</span>
                                        @endif
                                        {{ $option['title'] }}
                                    </p>
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
            <tr>
                <td colspan="6">
                    <p class="uk-text-right row-total-delivery">Доставка: <strong class="delivery_total">0</strong> руб.</p>
                </td>
            </tr>
            <tr class="total-row">
                <td colspan="6">
                    @if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount'))
                        <p class="uk-text-right row-total">Всего к оплате: <strong class="total">{!! $discountsShare->total !!}</strong> руб.</p>
                        @if($discountsShare->profit > 0)
                            <p class="uk-text-right row-clear-total">Сумма заказа: <del><strong class="clear-total">{!! $discountsShare->clear_total !!}</strong> руб.</del></p>
                            <p class="uk-text-right row-total-discount">Ваша скидка: <strong class="total-discount">{{ $discountsShare->profit }}</strong> руб.</p>
                        @endif
                    @else
                        <p class="uk-text-right row-total">Всего к оплате: <strong class="total">{!! Cart::instance('main')->total() !!}</strong> руб.</p>
                    @endif
                </td>
            </tr>
            </tbody>
        </table>
    </form>

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