@extends('larrock::admin.print')
@section('title') Бланк доставки к заказу №{{ $data->order_id }} @endsection

@section('content')
    <div class="doc_delivery">
        <p class="top_line uk-text-center uk-margin-large-bottom">
            <input class="uk-width-1-1 uk-text-center" type="text" value="Наименование организации">
            <small>(Наименование организации)</small>
        </p>
        <h1 style="text-align: center; line-height: 20px">Бланк доставки к заказу №{{ $data->order_id }}</h1>
        <p style="text-align: center; margin-left: 80px" class="editable-input delete-margin">
            Дата заказа: <input class="date_0" type="text" value="{{ $data->created_at->format('d/m/Y') }}" title="Кликните для редактирования">
        </p>
        <table cellpadding="3" cellspacing="0" border="0" class="uk-table uk-width-1-1 uk-form">
            <tbody>
            <tr>
                <td style="width: 200px;">Номер заказа</td>
                <td class="editable-input"><input class="uk-width-1-1" type="text" value="{{ $data->order_id }}" title="Кликните для редактирования"></td>
            </tr>
            <tr>
                <td>Получатель</td>
                <td class="editable-input"><input class="uk-width-1-1" type="text" value="{{ $data->fio }}" title="Кликните для редактирования"></td>
            </tr>
            <tr>
                <td>Телефон</td>
                <td class="editable-input"><input class="uk-width-1-1" type="text" value="{{ $data->tel }}" title="Кликните для редактирования"></td>
            </tr>
            <tr>
                <td>Адрес</td>
                <td class="editable-input"><input class="uk-width-1-1" type="text" value="{{ $data->address }}" title="Кликните для редактирования"></td>
            </tr>
            <tr>
                <td>Получаемые товары</td>
                <td class="editable-textarea">
                    @php($count=1)
                    @php($text = '')
                    @foreach($data->items as $items_value)
                        @foreach($items_value->options as $options_key => $options_value)
                            @php($items_value->name .= ' ('. $options_key .': '. $options_value .')')
                        @endforeach
                    @php $text .= $count .'. '. $items_value->name  .' '. $items_value->qty .'шт. Х '. $items_value->price .'='. $items_value->subtotal  .'руб.,' @endphp
                    @php($count++)
                    @endforeach
                    <textarea class="uk-width-1-1" title="Кликните для редактирования">{{ $text }}</textarea>
                </td>
            </tr>
            <tr>
                <td>Подпись получателя</td>
                <td class="text_padding"><p>Заказ получен, претензий не имею</p></td>
            </tr>
            </tbody>
        </table>
    </div>
    <button class="uk-button uk-button-primary uk-button-large btn-print">Печать</button>
@endsection

@push('scripts')
<script type="application/javascript">
    $('.btn-print').click(function(){
        $('.editable-textarea').each(function(){
            var text = $(this).find('textarea').text();
            $(this).html('<span>'+text+'</span>');
        });
        $('.editable-input').each(function(){
            var text = $(this).find('input').val();
            $(this).html('<span>'+text+'</span>');
        });
        $('.delete-margin').css('margin-left', 0);
        $(this).remove();
        window.print();
    });
</script>
@endpush