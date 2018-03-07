<div class="uk-margin-bottom uk-width-1-1 uk-width-1-2@m">
    <h4 class="panel-p-title"><a href="/admin/{{ $component->name }}">Незавершенные заказы</a></h4>
    <div class="uk-card uk-card-default uk-card-small">
        <div class="uk-card-body">
            @if(count($data) > 0)
                <table class="uk-table uk-table-small uk-table-hover uk-table-divider">
                    @foreach($data as $value)
                        <tr class="link_block_this" data-href="/admin/{{ $component->name }}#order{{ $value->order_id }}t">
                            <td>
                                <h4 class="uk-margin-remove-bottom"><a href="/admin/cart?order_id={{ $value->order_id }}&user_search">Заказ #{{ $value->order_id }}</a></h4>
                                <div>{{ $value->fio or 'n/a' }}</div>
                            </td>
                            <td>
                                <div data-uk-tooltip title="Дата обновления заказа" class="uk-text-small">{{ \Carbon\Carbon::parse($value->updated_at)->format('d M Y h:i') }}</div>
                                <span class="uk-label uk-label-success" data-uk-tooltip title="Статус заказа">{{ $value->status_order }}</span>
                                <span class="uk-label uk-label-warning" data-uk-tooltip title="Статус оплаты">{{ $value->status_pay }}</span>
                            </td>
                            <td>
                                <span class="uk-text-nowrap">
                                    @if($value->cost_discount > 0)
                                        {{ $value->cost_discount + $value->cost_delivery }} руб.
                                    @else
                                        @if($value->cost > 0) {{ $value->cost + $value->cost_delivery }} руб. @else догов. @endif
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p>Необработанных заказов еще нет</p>
            @endif
            <a href="/admin/{{ $component->name }}/create" class="uk-button uk-button-default uk-width-1-1">Создать заказ</a>
        </div>
    </div>
</div>