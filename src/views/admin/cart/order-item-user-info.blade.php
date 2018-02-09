@if( !isset($formRemove))
<form class="cart-user-info uk-form uk-form-stacked" action="/admin/{{ $app->name }}/{{ $data->id }}" method="post">
@endif
    <div class="uk-grid">
        <div class="uk-width-1-1 uk-width-medium-1-2">
            @foreach($app->rows as $row)
                @if($row->template_admin === 'user_info')
                    <div class="uk-form-row">
                        <label class="uk-form-label" for="{{$row->name}}{{ $data->order_id }}">{{ $row->title }}:</label>
                        @if(get_class($row) === 'Larrock\Core\Helpers\FormBuilder\FormTextarea')
                            <textarea class="not-editor uk-width-1-1" id="{{$row->name}}{{ $data->order_id }}" name="comment">{{ $data->{$row->name} }}</textarea>
                        @elseif($row->name === 'user')
                            <select id="user_id{{ $data->order_id }}" name="user_id" class="uk-width-1-1">
                                <option value="">Не назначен</option>
                                @foreach($users as $user_item)
                                    <option @if($data->user === $user_item->id) selected @endif value="{{ $user_item->id }}">{{ $user_item->fio }} ({{ $user_item->email }})</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" value="{{ $data->{$row->name} }}" id="{{ $row->name }}{{ $data->order_id }}" name="{{ $row->name }}" class="uk-width-1-1">
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
        <div class="uk-width-1-1 uk-width-medium-1-2">
            @foreach($app->rows as $row)
                @if($row->template_admin === 'status')
                    <div class="uk-form-row">
                        <label class="uk-form-label" for="{{$row->name}}{{ $data->order_id }}">{{ $row->title }}:</label>
                        @if(get_class($row) === 'Larrock\Core\Helpers\FormBuilder\FormSelect')
                            <select name="{{ $row->name }}" id="{{$row->name}}{{ $data->order_id }}" class="uk-width-1-1">
                                @foreach($row->options as $value)
                                    <option value="{{ $value }}" @if($data->{$row->name} === $value) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                        @elseif(get_class($row) === 'Larrock\Core\Helpers\FormBuilder\FormTextarea')
                            <textarea class="not-editor uk-width-1-1" id="{{$row->name}}{{ $data->order_id }}" name="comment">{{ $data->{$row->name} }}</textarea>
                        @else
                            <input type="text" value="{{ $data->{$row->name} }}" id="{{ $row->name }}{{ $data->order_id }}" name="{{ $row->name }}" class="uk-width-1-1">
                        @endif
                    </div>
                @endif
            @endforeach
            @if( !isset($formRemove))
                <div class="uk-form-row">
                    <button type="submit" class="uk-button uk-button-primary uk-button-large uk-width-1-1">Сохранить</button>
                </div>
                <input name="_method" type="hidden" value="PUT">
                <input type="hidden" name="order_id" value="{{ $data->order_id }}">
                {{ csrf_field() }}
            @endif

            <div class="notifys uk-width-1-1 uk-margin-top">
                <hr/>
                <form class="uk-form" method="post" action="{{ route('cart.sendNotify') }}">
                    <input type="hidden" name="order_id" value="{{ $data->order_id }}">
                    {{ csrf_field() }}
                    <button class="uk-button uk-width-1-1" type="submit">Отправить email покупателю</button>
                </form>
            </div>
        </div>
    </div>
@if( !isset($formRemove))
</form>
@endif