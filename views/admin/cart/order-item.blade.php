<div class="orderItem" id="order{{ $data->order_id }}">
    <div class="uk-grid">
        <div class="uk-width-expand">
            <h2 class="uk-margin-remove-bottom">Заказ #{{ $data->order_id }} <small class="uk-text-muted">{{ Carbon\Carbon::parse($data->updated_at)->format('d M Y H:i') }}</small></h2>
            <span class="uk-label uk-label-success uk-text-uppercase">{{ $data->status_order }}</span>
            <span class="uk-label uk-label-warning uk-text-uppercase">{{ $data->status_pay }}</span>
        </div>
        <div class="uk-width-auto">
            @if( !isset($disableSendNotify))
                <form class="uk-form uk-display-inline-block uk-margin-right" method="post" action="{{ route('cart.sendNotify') }}">
                    <input type="hidden" name="order_id" value="{{ $data->order_id }}">
                    {{ csrf_field() }}
                    <button class="uk-button uk-button-default uk-form-small" type="submit">Отправить email покупателю</button>
                </form>
            @endif
            <form action="/admin/{{ $package->name }}/{{ $data->id }}" method="post" class="uk-form uk-float-right">
                <input name="_method" type="hidden" value="DELETE">
                {{ csrf_field() }}
                <button type="submit" class="uk-button uk-button-danger uk-button-small please_conform">Удалить заказ</button>
            </form>
        </div>
    </div>

    <div class="uk-margin-top">
        @include('larrock::admin.cart.order-item-user-info')
        @if($data->payment_data)
            @include('larrock::admin.cart.payment-data')
        @endif
    </div>

    <div class="uk-margin-top tovars_container{{ $data->order_id }}">
        @include('larrock::admin.cart.order-item-tovars-table')
    </div>
</div>