<div id="dashboard-blocks" class="dashboard-item uk-width-small-1-1 uk-width-medium-1-2">
    <div class="uk-panel uk-alert">
        <p class="uk-h3"><a href="/admin/{{ $component->name }}">Новые <span class="uk-text-lowercase">{{ $component->title }}</span></a></p>
        @if(count($data) > 0)
        <table class="uk-table uk-table-hover">
            @foreach($data as $value)
                <tr class="link_block_this" data-href="/admin/cart#order{{ $value->order_id }}t">
                    <td>#{{ $value->order_id }}</td>
                    <td class="uk-text-nowrap">@if($value->cost_discount > 0)
                            {{ $value->cost_discount }} руб.
                        @else
                            @if($value->cost > 0)
                                {{ $value->cost }} руб.
                            @else
                                догов.
                            @endif
                        @endif
                    </td>
                    <td>
                        <span data-uk-tooltip title="Статус заказа">{{ $value->status_order }}</span><br/>
                        <span data-uk-tooltip title="Статус оплаты">{{ $value->status_pay }}</span>
                    </td>
                    <td>{{ $value->fio or 'n/a' }}</td>
                    <td class="uk-hidden-small">
                        <span data-uk-tooltip title="Дата создания заказа">{{ \Carbon\Carbon::parse($value->created_at)->format('d/m/Y') }}</span><br/>
                        <span data-uk-tooltip title="Дата обновления заказа">{{ \Carbon\Carbon::parse($value->updated_at)->format('d/m/Y') }}</span>
                    </td>
                </tr>
            @endforeach
        </table>
        @else
            <p>Заказов еще нет</p>
        @endif
    </div>
</div>