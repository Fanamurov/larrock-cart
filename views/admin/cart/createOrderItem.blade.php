<div>
    <div class="uk-card uk-card-default uk-card-hover uk-form createOrderItem" data-id="{{ $data->id }}" id="createOrderItem{{ $data->id }}">
        <div class="uk-card-body">
            <h3 class="uk-card-title">{{ $data->title }}</h3>
            <div class="catalogImage">
                <a data-fancybox="gallery" data-caption="{{ $data->title }}" href="{{ $data->first_image }}">
                    <img src="{{ $data->first_image }}" class="catalogImage all-width" itemprop="image"></a>
            </div>
            @foreach($package->rows as $row_key => $row)
                @if($row->template === 'description' && isset($data->{$row_key}) && !empty($data->{$row_key}))
                    <p><strong>{{ $row->title }}:</strong> {{ $data->{$row_key} }}</p>
                @endif
                @if(isset($row->costValue) && $row->costValue && count($data->cost_values) > 0)
                    <p>Варианты поставки:</p>
                    <ul class="uk-list">
                        @foreach($data->cost_values as $param)
                            <li><label class="changeCostValue">
                                    <input value="{{ $param->id }}" data-costValueId="{{ $param->id }}" type="radio" name="costValue_{{ $data->id }}" @if($loop->first) checked @endif>
                                    {{ $param->title }} - {{ $param->cost }} {{ $data->what }}</label></li>
                        @endforeach
                    </ul>
                @endif
            @endforeach

            <div class="cost">
                @if($data->first_cost_value === 0)
                    <span class="empty-cost">цена договорная</span>
                @else
                    Цена: <span class="default-cost"><span class="cost_value">{{ $data->first_cost_value }}</span> <span class="what">{{ $data->what }}</span></span>
                @endif
            </div>

            <label class="uk-form-label">Кол-во:</label>
            <input type="text" name="qty[]" value="1" class="uk-input uk-form-small uk-form-width-xsmall">
        </div>
    </div>
</div>