<form class="uk-form uk-form-stacked uk-margin-bottom" method="get" action="/admin/cart">
    <div class="uk-grid">
        <div>
            <label class="uk-form-label" for="order_id">Номер заказа:</label>
            <select name="order_id" id="order_id" class="uk-select uk-form-small uk-form-width-small">
                <option>Любой</option>
                @foreach($data as $value)
                    <option @if((int)Request::get('order_id') === $value->order_id) selected @endif value="{{ $value->order_id }}">{{ $value->order_id }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="uk-form-label">Даты:</label>
            <div class="uk-inline">
                <span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: calendar"></span>
                <input type="text" name="date_start" class="date uk-input uk-form-small uk-form-width-small" placeholder="Дата: от"
                       value="@if( !empty(Request::get('date_start'))) {{ Request::get('date_start') }} @endif">
            </div>
            <div class="uk-inline">
                <span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: calendar"></span>
                <input type="text" name="date_end" class="date uk-input uk-form-small uk-form-width-small" placeholder="Дата: до"
                       value="@if( !empty(Request::get('date_end'))) {{ Request::get('date_end') }} @endif">
            </div>
        </div>
        <div>
            <label class="uk-form-label" for="status_order">Статус заказа:</label>
            <select name="status_order" id="status_order" class="uk-select uk-form-small">
                <option>Любой</option>
                @foreach($app->rows['status_order']->options as $status)
                    <option value="{{ $status }}" @if(Request::get('status_order') === $status) selected @endif>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="uk-form-label" for="status_pay">Статус оплаты:</label>
            <select name="status_pay" id="status_pay" class="uk-select uk-form-small">
                <option>Любой</option>
                @foreach($app->rows['status_pay']->options as $status)
                    <option value="{{ $status }}" @if(Request::get('status_pay') === $status) selected @endif>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="uk-form-label" for="user_search">Покупатель:</label>
            <input type="text" name="user_search" id="user_search" placeholder="Любые контакты" class="uk-input uk-form-small"
                   value="@if( !empty(Request::get('user_search'))){{ Request::get('user_search') }}@endif">
        </div>
        <div>
            <button class="uk-button uk-button-default uk-button-large" type="submit" name="filter">Фильтровать</button>
        </div>
    </div>
    @if(Request::has('order_id'))
        <div class="uk-text-right">
            <a class="uk-button uk-button-danger" href="/admin/cart" title="Отменить фильтры заказов"><span uk-icon="icon: history"></span> Отменить</a>
        </div>
    @endif
</form>