<div class="uk-modal" tabindex="-1" role="dialog" id="ModalToCart">
    <div class="uk-modal-dialog">
        <div class="modal-content">
            <div class="uk-modal-header">
                <h2 class="modal-title item-title">{{ $data->title }} <button type="button" class="uk-modal-close uk-close uk-align-right"></button></h2>
            </div>
            <div class="uk-modal-body">
                <div id="ModalToCart-form" class="uk-form">
                    <div class="uk-grid">
                        <div class="uk-width-3-10 ModalToCart-image">
                            @foreach($data->getMedia('images')->sortByDesc('order_column') as $key => $image)
                                @if($key === 1)
                                    <img src="{{ $image->getUrl() }}" alt="Фото товара" class="item-photo all-width">
                                @else
                                    <img src="{{ $image->getUrl('140x140') }}" alt="Фото товара" class="item-photo all-width">
                                @endif
                            @endforeach
                            @if(count($data->getMedia('images')) === 0)
                                <img src="/_assets/_front/_images/empty_big.png" alt="Фото товара" class="item-photo all-width">
                            @endif
                        </div>
                        <div class="uk-width-7-10 ModalToCart-text">
                            @if( !empty($data->short))
                                <div class="item-description">
                                    {!! $data->short !!}
                                </div>
                                <br/><br/>
                            @endif
                            <div class="catalog-descriptions-rows">
                                @foreach($app->rows as $row_key => $row)
                                    @if($row->template && $row->template === 'in_card' && isset($data->{$row->name}) && !empty($data->{$row->name}))
                                        <p><span>{{ $row->title }}:</span> {{ $data->{$row->name} }}</p>
                                    @endif
                                @endforeach
                            </div>
                            <div class="params uk-form uk-form-horizontal">
                                @foreach($app->rows as $key => $value)
                                    @if($value->user_select && isset($value->connect->relation_name) && count($data->{$value->connect->relation_name}) > 0)
                                        <div class="uk-form-row">
                                            <label class="uk-form-label control-label-select" for="params-{{ $key }}">{{ $value->title }}:</label>
                                            <div class="uk-form-controls">
                                                <select class="tovar-params" name="{{ $key }}" id="params-{{ $key }}" data-title="{{ $value->title }}">
                                                    @foreach($data->{$value->connect->relation_name} as $value)
                                                        <option value="{{ $value->title }}">{{ $value->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <hr/>
                            <div class="uk-grid">
                                <div class="uk-width-3-10">
                                    <div class="uk-button-group input-group-qty" id="modal-spinner" data-trigger="spinner" data-rowid="{{ $data->id }}">
                                        <button class="uk-button addon-x" data-spin="down">-</button>
                                        <input type="text" class="uk-form-controls editQty" id="kolvo-{{ $data->id }}" name="kolvo" value="1"
                                               data-rule="quantity" @if($data->nalichie > 0) data-max="{{ $data->nalichie }}" @endif
                                               data-min="1" step="1" data-rowid="{{ $data->id }}">
                                        <button class="uk-button addon-what" data-spin="up">+</button>
                                    </div>
                                </div>
                                <div class="uk-width-7-10">
                                    <div class="total_cost">
                                        <p>x {{ $data->cost }} <strong>=</strong>
                                            <span class="cost" data-cost="{{ $data->cost }}">{{ $data->cost }}</span> руб.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <br/>
                            <div class="uk-text-right modal-buttons">
                                {{ csrf_field() }}
                                <button type="button" class="uk-button uk-button-link submit_to_cart" data-id="{{ $data->id }}">← Продолжить выбор</button>
                                <button type="button" class="uk-button submit_to_cart" data-id="{{ $data->id }}" data-link="/cart">Заказать →</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    submit_to_cart();
    rebuild_cost();
</script>