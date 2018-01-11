<form class="cart-user-info uk-form uk-form-stacked" action="/admin/{{ $app->name }}/{{ $data->id }}" method="post">
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
            <input name="_method" type="hidden" value="PUT">
            <input type="hidden" name="order_id" value="{{ $data->order_id }}">
            {{ csrf_field() }}
        </div>
    </div>
</form>