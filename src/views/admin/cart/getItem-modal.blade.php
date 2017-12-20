<div class="uk-modal" id="ModalToCart">
    <div class="uk-modal-dialog">
        <div class="modal-content">
            <div class="uk-modal-header">
                <a class="uk-modal-close uk-close uk-float-right"></a>
                <h2 class="uk-margin-top-remove">{{ $data->title }}</h2>
            </div>
            <div class="modal-body">
                <form id="ModalToCart-form" action="" method="post" class="uk-form uk-form-stacked">
                    <div class="uk-grid">
                        <div class="uk-width-3-10">
                            @foreach($data->getMedia('images')->sortByDesc('order_column') as $key => $image)
                                @if($key === 0)
                                    <img src="{{ $image->getUrl() }}" alt="Фото товара" class="item-photo all-width">
                                @endif
                            @endforeach
                            @if(count($data->getMedia('images')) === 0)
                                <img src="/_assets/_front/_images/empty_big.png" alt="Фото товара" class="item-photo all-width">
                            @endif
                        </div>
                        <div class="uk-width-7-10">
                            @if( !empty($data->short))
                                <div class="item-description">
                                    {!! $data->short !!}
                                </div>
                                <br/><br/>
                            @endif
                            <p><a href="{{ $data->full_url }}"><i class="fa fa-share-square-o" aria-hidden="true"></i> Перейти к полному описанию</a></p>
                            <div class="catalog-descriptions-rows">
                                @foreach($app->rows as $row_key => $row)
                                    @if($row->template === 'in_card' && isset($data->{$row_key}) && !empty($data->{$row_key}))
                                        <div class="uk-form-row"><span class="uk-text-muted">{{ $row->title }}:</span> {{ $data->{$row_key} }}</div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="params">
                                @foreach($app->rows as $key => $value)
                                    @if($value->user_select && $value->user_select === TRUE && count($data->{$value->connect->relation_name}) > 0)
                                        <div class="uk-form-row">
                                            <label class="uk-form-label" for="params-{{ $key }}">{{ $value->title }}:</label>
                                            <select class="tovar-params uk-width-1-1" name="{{ $key }}" id="params-{{ $key }}" data-title="{{ $value->title }}">
                                                @foreach($data->{$value->connect->relation_name} as $value)
                                                    <option value="{{ $value->title }}">{{ $value->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <hr/>
                            <div class="uk-grid uk-grid-small">
                                <div class="uk-width-1-2">
                                    <input type="text" class="editQty" id="kolvo-{{ $data->id }}" name="kolvo" value="1" data-rowid="{{ $data->id }}">
                                    @if($data->nalichie > 0)
                                        <span class="uk-badge">в наличии {{ $data->nalichie }} шт.</span>
                                    @else
                                        <span class="uk-badge uk-badge-danger">под заказ</span>
                                    @endif
                                </div>
                                <div class="uk-width-1-2">
                                    <div class="total_cost" style="font-size: 20px; padding-top: 4px;">
                                        <p><strong>=</strong>
                                            <span class="cost" data-cost="{{ $data->cost }}">{{ $data->cost }}</span> руб.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <br/>
                            <div class="pull-right modal-buttons">
                                <input type="hidden" name="order_id" value="{{ $order->order_id }}">
                                <input type="hidden" name="id" value="{{ $data->id }}">
                                {{ csrf_field() }}
                                <button type="submit" class="uk-button uk-button-primary uk-button-large submit_to_cart" data-id="{{ $data->id }}" data-link="/cart">Добавить к заказу</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        rebuild_cost();
    </script>
</div>