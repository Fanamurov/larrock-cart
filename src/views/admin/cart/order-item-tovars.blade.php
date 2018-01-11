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