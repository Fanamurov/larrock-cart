<ul uk-accordion>
    <li>
        <a class="uk-accordion-title uk-form-row totalcost-row uk-h2" href="#" uk-tooltip="title: По клику покажем полную информацию о товарах в заказе">
            Итого: {{ $data->cost + $data->cost_delivery }} рублей
            @if($data->cost_delivery > 0)
                <small class="uk-text-muted">[{{ $data->cost_delivery }} рублей - доставка]</small>
            @endif
        </a>
        <div class="uk-accordion-content">
            <table class="uk-table">
                @foreach($data->items as $key => $item)
                    <tr>
                        <td width="35">
                            @if($item->catalog && $item->catalog->first_image)
                                <div uk-lightbox>
                                    <a href="{{ $item->catalog->first_image }}" data-caption='{{ $item->name }}'>
                                        <img class="all-width" src="{{ $item->catalog->first_image }}" alt='{{ $item->name }}'>
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if(isset($item->catalog->full_url))
                                <h4>
                                    <a href="/admin/catalog/{{ $item->catalog->id }}/edit">{{ $item->name }}</a>
                                    @if( !empty($item->catalog->articul))
                                        <small class="uk-text-muted">[{{ $item->catalog->articul }}]</small>
                                    @endif
                                    <a href="{{ $item->catalog->full_url }}"><span uk-icon="icon: link"></span></a>
                                </h4>
                            @else
                                <h4>{{ $item->name }} (ТОВАРА БОЛЬШЕ НЕТ НА САЙТЕ!!!)</h4>
                            @endif
                        </td>
                        <td>
                            <ul class="list-attributes uk-list uk-margin-top-remove">
                                @foreach($item->options as $key_option => $option)
                                    @if( !is_object($option))
                                        <li><span class="uk-text-muted">@lang('larrock::fields.'.$key_option):</span> {{ $option }}</li>
                                    @else
                                        @if(array_key_exists('className', $option))
                                            <li><span class="uk-text-muted">@lang('larrock::fields.'.$option->className): {{ $option->title }}</span></li>
                                        @else
                                            <li><span class="uk-text-muted">@lang('larrock::fields.'.$key_option): {{ $option->title }}</span></li>
                                        @endif
                                    @endif
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            <div class="uk-form-row uk-form-row-costrow">
                                @foreach($app->rows as $row_key => $row)
                                    @if($row->template === 'description' && isset($item->{$row_key}) && !empty($item->{$row_key}))
                                        <p><strong>{{ $row->title }}:</strong> {{ $item->{$row_key} }}</p>
                                    @endif
                                    @if(isset($row->costValue) && $row->costValue && count($item->cost_values) > 0)
                                        <p>Варианты поставки:</p>
                                        <ul class="uk-list">
                                            @foreach($item->cost_values as $param)
                                                <li><label class="changeCostValue">
                                                        <input value="{{ $param->id }}" data-costValueId="{{ $param->id }}" type="radio" name="costValue_{{ $item->id }}" @if($loop->first) checked @endif>
                                                        {{ $param->title }} - {{ $param->cost }} {{ $item->what }}</label></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td width="200">
                            <div class="uk-form-row uk-form-row-costrow">
                                <div class="uk-grid uk-grid-small">
                                    <div class="uk-width-auto">
                                        <form action="/admin/{{ $app->name }}/qtyItem/{{ $key }}" method="post" class="uk-form">
                                            <input name="_method" type="hidden" value="PUT">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="order_id" value="{{ $data->order_id }}">
                                            <input type="hidden" name="old-qty" value="{{ $item->qty }}">
                                            <input type="text" value="{{ $item->qty }}" name="qty" class="uk-input uk-form-width-xsmall uk-form-small"
                                                   data-uk-tooltip title="Сохранение по Enter">
                                        </form>
                                    </div>
                                    <div class="uk-width-expand">
                                        <span class="uk-text-muted">x</span> {{ $item->price }}  руб.
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="uk-h4 uk-text-right">{{ $item->subtotal }} руб.</p>
                        </td>
                        <td>
                            <form action="/admin/{{ $app->name }}/removeItem" method="post" class="remove-item">
                                <input name="_method" type="hidden" value="DELETE">
                                <input type="hidden" name="order_id" value="{{ $data->order_id }}">
                                <input type="hidden" name="id" value="{{ $key }}">
                                {{ csrf_field() }}
                                <button class="uk-button uk-button-danger uk-button-small uk-width-1-1 please_conform" name="removeItem">удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="7">
                        <button type="button" class="uk-button uk-button-default show-add_to_cart{{ $data->order_id }}" onclick="AddTovalList({{ $data->order_id }});">Добавить товар к заказу</button>
                        <select id="tovar{{ $data->order_id }}" class="add_to_cart uk-width-1-1 uk-hidden" data-order_id="{{ $data->order_id }}"></select>
                    </td>
                </tr>
            </table>

            @if(isset($data->discount->profit) && $data->discount->profit > 0)
                <div class="uk-alert">
                    <p>Скидка: {!! $data->discount->profit !!} руб.</p>
                    <ul class="uk-list">
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
                </div>
            @endif
        </div>
    </li>
</ul>