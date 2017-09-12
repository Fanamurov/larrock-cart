<div class="orderItem uk-margin-large-top">
    <div class="uk-grid">
        <div class="uk-width-1-1">
            <p class="uk-h2">Заказ #{{ $data->order_id }} <small class="uk-text-muted">от {{ \Carbon\Carbon::parse($data->updated_at)->format('d.m.Y') }}г.</small></p>
        </div>
        <div class="uk-width-1-1">
            <div class="uk-alert order-pay">
                <div class="text-order-pay">
                    @if($data->status_pay !== 'Оплачено')
                        @if($data->cost_discount > 0 && $data->cost_discount < $data->cost)
                            <span class="uk-align-left">Всего к оплате со скидкой: <strong class="total">{{ $data->cost_discount }}</strong> руб.</span>
                        @else
                            <span class="uk-align-left">Всего к оплате: <strong class="total">{{ $data->cost }}</strong> руб.</span>
                        @endif
                        @if($data->method_pay !== 'наличными')
                            @if(View::exists('larrock::front.yandexkassa.form') && config('yandex_kassa.sc_id'))
                                @include('larrock::front.yandexkassa.form')
                            @else
                                Метод оплаты не подключен
                            @endif
                        @else
                            <p class="not-pay">{{ $data->status_pay }}</p>
                        @endif
                    @else
                        <p class="success-pay">Оплачено {{ $data->cost }} руб.</p>
                    @endif
                </div>
                <div class="uk-clearfix"></div>
                <p class="uk-text-muted">Метод оплаты: {{ $data->method_pay }}</p>
                <p class="uk-text-muted">Тип плательщика: {{ $data->type_costumer }}</p>
                <p class="uk-text-muted">Метод доставки: {{ $data->method_delivery }}</p>
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
                            @if(isset($item->catalog->id))
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
                            </div>
                        </td>
                        <td>
                            {{ $item->qty }} шт.
                            <div class="subtotal uk-hidden-medium uk-hidden-large">
                                <small class="uk-text-muted">x</small> <span class="price-item">{{ $item->price }}</span> <small class="uk-text-muted">=</small>
                                <span>{{ $item->subtotal }}</span> руб.
                            </div>
                        </td>
                        <td class="cost-row uk-hidden-small"><small class="uk-text-muted">x</small> <span class="price-item">{{ $item->price }}</span> <small class="uk-text-muted">=</small></td>
                        <td class="subtotal uk-hidden-small uk-text-right"><span>{{ $item->subtotal }}</span> руб.</td>
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