@if( !isset($formRemove))
<form class="cart-user-info uk-form uk-form-stacked" action="/admin/{{ $app->name }}/{{ $data->id }}" method="post">
@endif
    <div class="uk-grid">
        <div class="uk-width-1-1 uk-width-1-2@m">
            <div class="uk-grid">
                @foreach($app->rows as $row)
                    @if($row->templateAdmin === 'user_info')
                        <div class="uk-form-row-small uk-width-1-1 @if(!$row instanceof \Larrock\Core\Helpers\FormBuilder\FormTextarea) uk-width-1-2@m @endif">
                            <label class="uk-form-label" for="{{$row->name}}{{ $data->order_id }}">{{ $row->title }}:</label>
                            @if($row instanceof \Larrock\Core\Helpers\FormBuilder\FormTextarea)
                                <textarea class="not-editor uk-width-1-1 uk-textarea uk-form-small" id="{{$row->name}}{{ $data->order_id }}"
                                          name="comment" placeholder="Не указано">{{ $data->{$row->name} }}</textarea>
                            @elseif($row->name === 'user')
                                <select id="user_id{{ $data->order_id }}" name="user_id" class="uk-width-1-1 uk-select uk-form-small">
                                    <option value="">Не назначен</option>
                                    @foreach($users as $user_item)
                                        <option @if($data->user === $user_item->id) selected @endif value="{{ $user_item->id }}">{{ $user_item->fio }} ({{ $user_item->email }})</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" value="{{ $data->{$row->name} }}" id="{{ $row->name }}{{ $data->order_id }}"
                                       name="{{ $row->name }}" placeholder="Не указано" class="uk-width-1-1 uk-input uk-form-small">
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="uk-width-1-1 uk-width-1-2@m">
            <div class="uk-grid">
                @foreach($app->rows as $row)
                    @if($row->templateAdmin === 'status')
                        <div class="uk-form-row-small uk-width-1-1 @if(!$row instanceof \Larrock\Core\Helpers\FormBuilder\FormTextarea) uk-width-1-2@m @endif">
                            <label class="uk-form-label" for="{{$row->name}}{{ $data->order_id }}">{{ $row->title }}:</label>
                            @if($row instanceof \Larrock\Core\Helpers\FormBuilder\FormSelect)
                                <select name="{{ $row->name }}" id="{{$row->name}}{{ $data->order_id }}" class="uk-width-1-1 uk-select uk-form-small">
                                    @foreach($row->options as $key => $value)
                                        @if($row->name === 'method_delivery')
                                            <option value="{{ $key }}" @if($data->{$row->name} === $key) selected @endif>{{ $key }}</option>
                                        @else
                                            <option value="{{ $value }}" @if($data->{$row->name} === $value) selected @endif>{{ $value }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            @elseif($row instanceof \Larrock\Core\Helpers\FormBuilder\FormTextarea)
                                <textarea class="not-editor uk-width-1-1 uk-textarea uk-form-small" id="{{$row->name}}{{ $data->order_id }}"
                                          name="comment" placeholder="Не указано">{{ $data->{$row->name} }}</textarea>
                            @else
                                <input type="text" value="{{ $data->{$row->name} }}" id="{{ $row->name }}{{ $data->order_id }}"
                                       name="{{ $row->name }}" placeholder="Не указано" class="uk-width-1-1 uk-input uk-form-small">
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
            @if( !isset($formRemove))
                <div class="uk-form-row uk-text-right uk-margin-top">
                    <button type="submit" class="uk-button uk-button-primary uk-button-large">Изменить заказ</button>
                </div>
                <input name="_method" type="hidden" value="PUT">
                <input type="hidden" name="order_id" value="{{ $data->order_id }}">
                {{ csrf_field() }}
            @endif
        </div>
    </div>
@if( !isset($formRemove))
</form>
@endif