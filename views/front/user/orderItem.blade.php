<div class="orderItem uk-margin-large-top">
    <div class="uk-grid">
        <div class="uk-width-1-1">
            <p class="uk-h2">Заказ #{{ $data->order_id }} <small class="uk-text-muted">от {{ \Carbon\Carbon::parse($data->updated_at)->format('d.m.Y') }}г.</small></p>
        </div>
        <div class="uk-width-1-1">
            <div class="uk-alert order-pay">
                <div class="@if($data->cost > 0) text-order-pay @endif">
                    @if($data->status_pay !== 'Оплачено')
                        @if($data->cost > 0)
                            <span class="uk-align-left">Всего к оплате: <strong class="total">{{ $data->cost + $data->cost_delivery }}</strong> руб.</span>
                        @else
                            <span class="uk-align-left">К оплате по договорной цене</span>
                        @endif
                        @if(isset($config_cart->rows['method_pay']))
                            @if($data->method_pay !== 'наличными')
                                @if(file_exists(base_path(). '/vendor/fanamurov/larrock-yandex-kassa') && config('larrock-yandex-kassa.shop_id'))
                                    @include('larrock::front.modules.yandexkassa.form-cart')
                                @else
                                    <p class="uk-alert uk-alert-danger uk-display-block">Метод оплаты не подключен</p>
                                @endif
                            @else
                                <span class="not-pay">{{ $data->status_pay }}</span>
                            @endif
                        @endif
                    @else
                        <span class="success-pay">Оплачено {{ $data->cost + $data->cost_delivery }} руб.</span>
                        @if( !empty($data->invoiceId) && isset($data->payment_data->status))
                            @include('larrock::admin.cart.payment-data')
                        @endif
                    @endif
                </div>
                <div class="uk-clearfix"></div>

                @if($data->cost > 0)
                    @if(isset($data->discount->profit))
                        <div class="uk-alert">
                            <p>Скидка: {!! $data->discount->profit !!} руб.<br/>
                                <ul>
                                    @if(isset($data->discount->d_cart))
                                    <li>{{ $data->discount->d_cart->title }}: {{ $data->discount->d_cart->description }}</li>
                                    @endif
                                    @if(isset($data->discount->d_history))
                                    <li>{{ $data->discount->d_history->title }}: {{ $data->discount->d_history->description }}</li>
                                    @endif
                                    @if(isset($data->discount->d_kupon))
                                    <li>{{ $data->discount->d_kupon->title }}: {{ $data->discount->d_kupon->description }}</li>
                                    @endif
                                </ul>
                            </p>
                        </div>
                    @endif
                @endif

                @if(isset($config_cart->rows['method_pay']))
                    <p class="uk-text-muted">Метод оплаты: {{ $data->method_pay }}</p>
                @endif
                @if(isset($config_cart->rows['method_delivery']))
                    <p class="uk-text-muted">Метод доставки: {{ $data->method_delivery }} - {{ $data->cost_delivery }} рублей</p>
                @endif
                <p class="uk-text-muted">
                    {{ $data->fio}},
                    @if( !empty($data->address)){{ $data->address}},@endif
                    @if( !empty($data->tel)){{ $data->tel}},@endif
                    @if( !empty($data->tel)){{ $data->email}}@endif</p>
            </div>

            <div class="uk-alert uk-alert-warning order-status">
                <p>Статус заказа: {{ $data->status_order }}</p>
                @if($data->status_pay !== 'Оплачено')
                    <form method="post" action="/user/removeOrder/{{ $data->id }}" class="uk-form cancel-order">
                        {!! csrf_field() !!}
                        <button type="submit" class="uk-button please_conform">Отменить заказ</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="uk-width-1-1">
            <table class="uk-table">
                <thead>
                <tr class="uk-hidden-small">
                    <th></th>
                    <th></th>
                    <th>Количество</th>
                    <th>Цена</th>
                    <th class="uk-text-right">Итого</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data->items as $item)
                    <tr>
                        <td class="tovar_image uk-hidden-small">
                            @if($item->catalog && $item->catalog->getFirstImage)
                                <img src="{{ $item->catalog->getFirstImage->getUrl('140x140') }}" alt="{{ $item->name }}" class="all-width">
                            @else
                                <img src="/_assets/_front/_images/empty_big.png" alt="Not Photo" class="all-width">
                            @endif
                        </td>
                        <td class="description-row">
                            @if(isset($item->catalog->id) && config('larrock.catalog.ShowItemPage') === true)
                                <p class="uk-h4"><a href="{{ $item->catalog->full_url }}">{{ $item->name }}</a></p>
                            @else
                                <p class="uk-h4">{{ $item->name }}</p>
                            @endif
                            <div class="item-options">
                                @foreach($config_catalog->rows as $row_key => $config_row)
                                    @if(array_key_exists('in_card', $config_row) && isset($item->catalog->{$row_key}) && !empty($item->catalog->{$row_key}))
                                        <p><span class="uk-text-muted">{{ $config_row['title'] }}:</span> {{ $item->catalog->{$row_key} }}</p>
                                    @endif
                                @endforeach
                                @foreach($item->options as $key_option => $option)
                                    @if( !is_object($option))
                                        <p><span class="uk-text-muted">@lang('larrock::fields.'.$key_option):</span> {{ $option }}</p>
                                    @else
                                        <p class="{{$key_option}}-param">
                                            @if(array_key_exists('className', $option))
                                                <span class="uk-form-label">@lang('larrock::fields.'.$option->className):</span>
                                            @else
                                                <span class="uk-form-label">@lang('larrock::fields.'.$key_option):</span>
                                            @endif
                                            {{ $option->title }}
                                        </p>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td>
                            {{ $item->qty }} шт.
                            <div class="subtotal uk-hidden-medium uk-hidden-large">
                                @if($item->subtotal > 0)
                                    <small class="uk-text-muted">x</small> <span class="price-item">{{ $item->price }}</span> <small class="uk-text-muted">=</small>
                                    <span>{{ $item->subtotal }}</span> руб.
                                @else
                                    <small class="uk-text-muted">договорная</small>
                                @endif
                            </div>
                        </td>
                        <td class="cost-row uk-hidden-small">
                            @if($item->price > 0)
                                <small class="uk-text-muted">x</small> <span class="price-item">{{ $item->price }}</span> <small class="uk-text-muted">=</small>
                            @else
                                <small class="uk-text-muted">договорная</small>
                            @endif
                        </td>
                        <td class="subtotal uk-hidden-small uk-text-right">
                            @if($item->subtotal > 0)
                                <span>{{ $item->subtotal }}</span> руб.
                            @else
                                <small class="uk-text-muted">договорная</small>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="uk-grid">
        <div class="uk-width-1-1">
            @if( !empty($data->comment))
                <p><span class="uk-text-muted">Комментарий:</span> {{ $data->comment }}</p>
            @endif
        </div>
    </div>
</div>