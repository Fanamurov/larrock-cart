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
                    <p class="uk-h2 uk-margin-top-remove">Заказ #{{ $data->order_id }}
                        <small class="uk-text-muted">{{ Carbon\Carbon::parse($data->updated_at)->format('d/M/Y H:i') }}</small></p>
                    @include('larrock::admin.cart.order-item-tovars')
                </div>
                <div class="uk-width-1-1 uk-width-medium-6-10">
                    @include('larrock::admin.cart.order-item-user-info')
                    @if($data->payment_data)
                        @include('larrock::admin.cart.payment-data')
                    @endif
                </div>
            </div>
            @if($data->status_order === 'Завершен' || $data->status_order === 'Отменен')
        </div>
    @endif
</div>