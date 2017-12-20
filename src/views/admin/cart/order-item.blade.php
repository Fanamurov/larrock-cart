<div class="orderItem uk-margin-large-bottom ibox-content" id="order{{ $data->order_id }}">
    @if($data->status_order === 'Завершен' || $data->status_order === 'Отменен')
        <button class="uk-button @if($data->status_order === 'Отменен') uk-button-danger @endif uk-width-1-1" type="button"
                onclick="$('#collapseOrder{{ $data->id }}').removeClass('uk-hidden'); $(this).remove()">
            Заказ #{{ $data->order_id }} {{ $data->status_order }} {{  $data->status_pay }} {{ $data->updated_at }}
        </button>
    @endif
    @if($data->status_order === 'Завершен' || $data->status_order === 'Отменен')
        <div class="uk-hidden" id="collapseOrder{{ $data->id }}">
            @endif
            <div class="uk-grid">
                <div class="uk-width-1-1 uk-width-medium-4-10">
                    <form action="/admin/{{ $app->name }}/{{ $data->id }}" method="post" class="uk-form uk-float-right">
                        <input name="_method" type="hidden" value="DELETE">
                        {{ csrf_field() }}
                        <button type="submit" class="uk-button uk-button-danger uk-button-small please_conform">Удалить заказ</button>
                    </form>
                    <p class="uk-h2 uk-margin-top-remove">Заказ #{{ $data->order_id }} <small class="uk-text-muted">{{ $data->updated_at }}</small></p>
                    <div class="uk-scrollable-text">
                        @foreach($data->items as $key => $item)
                            <div class="uk-grid uk-grid-medium">
                                <div class="uk-width-3-10">
                                    @if($item->catalog)
                                        <img class="all-width" src="{{ $item->catalog->first_image }}" alt='{{ $item->name }}'>
                                    @endif
                                    <form action="/admin/{{ $app->name }}/removeItem" method="post" class="remove-item">
                                        <input name="_method" type="hidden" value="DELETE">
                                        <input type="hidden" name="order_id" value="{{ $data->order_id }}">
                                        <input type="hidden" name="id" value="{{ $key }}">
                                        {{ csrf_field() }}
                                        <button class="uk-button uk-button-danger uk-button-small uk-width-1-1 please_conform" name="removeItem">удалить</button>
                                    </form>
                                </div>
                                <div class="uk-width-7-10">
                                    @if(isset($item->catalog->full_url))
                                        <p class="uk-h3 uk-margin-bottom-remove">
                                            <a href="/admin/catalog/{{ $item->catalog->id }}/edit">{{ $item->name }}</a>
                                            <a href="{{ $item->catalog->full_url }}"><i class="uk-icon-share-square-o"></i></a>
                                        </p>
                                        @if( !empty($item->catalog->articul))
                                            <p><small class="uk-text-muted">Артикул:</small> {{ $item->catalog->articul }}</p>
                                        @endif
                                    @else
                                        <p style="font-size: 16px">{{ $item->name }} (ТОВАРА БОЛЬШЕ НЕТ НА САЙТЕ!!!)</p>
                                    @endif
                                    <ul class="list-attributes uk-list uk-margin-top-remove">
                                        @foreach($item->options as $key_option => $value_option)
                                            <li><span class="uk-text-muted">{{ $key_option }}:</span> {{ $value_option }}</li>
                                        @endforeach
                                    </ul>
                                    <div class="uk-form-row uk-form-row-costrow uk-margin-top-remove">
                                        <div class="uk-grid uk-grid-small">
                                            <div class="uk-width-4-10">
                                                <form action="/admin/{{ $app->name }}/qtyItem/{{ $key }}" method="post" class="uk-form">
                                                    <input name="_method" type="hidden" value="PUT">
                                                    {{ csrf_field() }}
                                                    <input type="hidden" name="order_id" value="{{ $data->order_id }}">
                                                    <input type="hidden" name="old-qty" value="{{ $item->qty }}">
                                                    <input type="text" value="{{ $item->qty }}" name="qty" class="uk-width-1-1"
                                                           data-uk-tooltip title="Сохранение по Enter">
                                                </form>
                                            </div>
                                            <div class="uk-width-6-10 cost_del uk-h4"><span class="uk-text-muted">x</span> {{ $item->price }} = {{ $item->subtotal }} руб.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="uk-form-row totalcost-row uk-h2 uk-margin-top">Итого: {{ $data->cost }} рублей</div>
                    <div class="uk-form-row uk-form">
                        <select class="add_to_cart" data-order_id="{{ $data->id }}">
                            <option>--- Добавить к заказу ---</option>
                            @foreach($catalog as $catalog_item)
                                <option value="{{ $catalog_item->id }}">{{ $catalog_item->title }} - {{ $catalog_item->cost }} руб.</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <form class="cart-user-info uk-width-1-1 uk-width-medium-6-10 uk-form uk-form-stacked" action="/admin/{{ $app->name }}/{{ $data->id }}" method="post">
                    <div class="uk-grid">
                        <div class="uk-width-1-1 uk-width-medium-1-2">
                            <div class="uk-form-row">
                                <label class="uk-form-label" for="user_id{{ $data->order_id }}">ID пользователя:</label>
                                <select id="user_id{{ $data->order_id }}" name="user_id" class="uk-width-1-1">
                                    <option value="">Не назначен</option>
                                    @foreach($users as $user_item)
                                        <option @if($data->user === $user_item->id) selected @endif value="{{ $user_item->id }}">{{ $user_item->fio }} ({{ $user_item->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(isset($app->rows['fio']))
                                <div class="uk-form-row">
                                    <label class="uk-form-label" for="fio{{ $data->order_id }}">ФИО:</label>
                                    <input type="text" value="{{ $data->fio }}" id="fio{{ $data->order_id }}" name="fio" class="uk-width-1-1">
                                </div>
                            @endif
                            <div class="uk-form-row">
                                <label class="uk-form-label" for="email{{ $data->order_id }}">Email:</label>
                                <input type="text" value="{{ $data->email }}" id="email{{ $data->order_id }}" name="email" class="uk-width-1-1">
                            </div>
                            @if(isset($app->rows['tel']))
                                <div class="uk-form-row">
                                    <label class="uk-form-label" for="tel{{ $data->order_id }}">Телефон:</label>
                                    <input type="text" value="{{ $data->tel }}" id="tel{{ $data->order_id }}" name="tel" class="uk-width-1-1">
                                </div>
                            @endif
                            @if(isset($app->rows['address']))
                                <div class="uk-form-row">
                                    <label class="uk-form-label" for="address{{ $data->order_id }}">Адрес доставки:</label>
                                    <textarea class="not-editor uk-width-1-1" id="address{{ $data->order_id }}" name="address">{{ $data->address }}</textarea>
                                </div>
                            @endif
                            @if(isset($app->rows['comment']))
                                <div class="uk-form-row">
                                    <label class="uk-form-label" for="comment{{ $data->order_id }}">Комментарий покупателя:</label>
                                    <textarea class="not-editor uk-width-1-1" id="comment{{ $data->order_id }}" name="comment">{{ $data->comment }}</textarea>
                                </div>
                            @endif
                        </div>
                        <div class="uk-width-1-1 uk-width-medium-1-2">
                            <div class="uk-form-row">
                                <label class="uk-form-label" for="status_order{{ $data->order_id }}">Статус заказа:</label>
                                <select name="status_order" id="status_order{{ $data->order_id }}" class="uk-width-1-1">
                                    @foreach($app->rows['status_order']->options as $value)
                                        <option value="{{ $value }}" @if($data->status_order === $value) selected @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="uk-form-row">
                                <label class="uk-form-label" for="status_pay{{ $data->order_id }}">Статус оплаты:</label>
                                <select name="status_pay" id="status_pay{{ $data->order_id }}" class="uk-width-1-1">
                                    @foreach($app->rows['status_pay']->options as $value)
                                        <option value="{{ $value }}" @if($data->status_pay === $value) selected @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(isset($app->rows['method_pay']))
                                <div class="uk-form-row">
                                    <label class="uk-form-label" for="method_pay{{ $data->order_id }}">Метод оплаты:</label>
                                    <select name="method_pay" id="method_pay{{ $data->order_id }}" class="uk-width-1-1">
                                        @foreach($app->rows['method_pay']->options as $value)
                                            <option value="{{ $value }}" @if($data->method_pay === $value) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            @if(isset($app->rows['method_delivery']))
                                <div class="uk-form-row">
                                    <label class="uk-form-label" for="method_delivery{{ $data->order_id }}">Способ доставки:</label>
                                    <select name="method_delivery" id="method_delivery{{ $data->order_id }}" class="uk-width-1-1">
                                        @foreach($app->rows['method_delivery']->options as $value)
                                            <option value="{{ $value }}" @if($data->method_delivery === $value) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="uk-form-row">
                                <label class="uk-form-label" for="comment_admin{{ $data->order_id }}">Скрытый комментарий:</label>
                                <textarea name="comment_admin" id="comment_admin{{ $data->order_id }}" class="not-editor uk-width-1-1" rows="3">{{ $data->comment_admin }}</textarea>
                            </div>
                            <div class="uk-form-row">
                                <button type="submit" class="uk-button uk-button-primary uk-button-large uk-width-1-1">Сохранить</button>
                            </div>
                            <div class="uk-form-row">
                                <a class="uk-button uk-width-1-1" href="/admin/cart/check/{{ $data->order_id }}" target="_blank">Чек</a>
                            </div>
                            <div class="uk-form-row">
                                <a class="uk-button uk-width-1-1" href="/admin/cart/delivery/{{ $data->order_id }}" target="_blank">Бланк доставки</a>
                            </div>
                            <input name="_method" type="hidden" value="PUT">
                            <input type="hidden" name="order_id" value="{{ $data->order_id }}">
                            {{ csrf_field() }}
                        </div>
                    </div>
                </form>
            </div>
            @if($data->status_order === 'Завершен' || $data->status_order === 'Отменен')
        </div>
    @endif
</div>