<form class="uk-form uk-grid uk-form-stacked uk-margin-large-bottom" method="get" action="/admin/cart">
    <div class="uk-float-left">
        <label class="uk-form-label" for="order_id">Номер заказа:</label>
        <input type="text" name="order_id" id="order_id" placeholder="ID заказа" class="uk-form-width-small"
               value="@if( !empty(Request::get('order_id'))) {{ Request::get('order_id') }} @endif">
    </div>
    <div class="uk-float-left">
        <label class="uk-form-label">Даты:</label>
        <div class="uk-form-icon">
            <i class="uk-icon-calendar"></i>
            <input type="text" name="date_start" class="date uk-form-width-small" placeholder="Дата: от" value="@if( !empty(Request::get('date_start'))) {{ Request::get('date_start') }} @endif">
        </div>
        <div class="uk-form-icon">
            <i class="uk-icon-calendar"></i>
            <input type="text" name="date_end" class="date uk-form-width-small" placeholder="Дата: до" value="@if( !empty(Request::get('date_end'))) {{ Request::get('date_end') }} @endif">
        </div>
    </div>
    <div class="uk-float-left">
        <label class="uk-form-label" for="status_order">Статус заказа:</label>
        <select name="status_order" id="status_order">
            <option value="">Все</option>
            @foreach($app->rows['status_order']->options as $status)
                <option value="{{ $status }}" @if(Request::get('status_order') === $status) selected @endif>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="uk-float-left">
        <label class="uk-form-label" for="status_pay">Статус оплаты:</label>
        <select name="status_pay" id="status_pay">
            <option value="">Все</option>
            @foreach($app->rows['status_pay']->options as $status)
                <option value="{{ $status }}" @if(Request::get('status_pay') === $status) selected @endif>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="uk-float-left">
        <label class="uk-form-label" for="user_search">Покупатель:</label>
        <input type="text" name="user_search" id="user_search" placeholder="Любые поля контактов" class="uk-form-width-small"
               value="@if( !empty(Request::get('user_search'))) {{ Request::get('user_search') }} @endif">
    </div>
    <div class="uk-float-left">
        <button class="uk-button uk-button-large" type="submit" name="filter">Фильтровать</button>
        @if(Request::has('order_id'))
            <a class="uk-button uk-button-danger" href="/admin/cart" title="Отменить фильтры заказов"><i class="uk-icon-remove"></i></a>
        @endif
    </div>
</form>