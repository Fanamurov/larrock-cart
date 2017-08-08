@extends('larrock::admin.print')
@section('title') Товарный чек №{{ $data->order_id }} @endsection

@section('content')
    <div class="doc_delivery">
        <p class="top_line uk-text-center uk-margin-large-bottom">
            <input class="uk-width-1-1 uk-text-center" type="text" value="Наименование организации">
            <small>(Наименование организации)</small>
        </p>

        <h1 style="text-align: center; line-height: 20px">Товарный чек №
            <span class="border_bottom editable-input"><input class="date_0" type="text" value="{{ $data->order_id }}" title="Кликните для редактирования"></span>
            от <span class="border_bottom editable-input"> <input class="date_1" type="text" value="{{ $data->created_at->format('d/m/Y') }}" title="Кликните для редактирования"></span> г.
        </h1>

        <table cellpadding="3" cellspacing="0" border="0" style="width: 100%" class="uk-table">
            <thead>
            <tr>
                <th style="width: 55px;">№ п/п</th>
                <th>Наименование, характеристика товара</th>
                <th style="width: 50px">Ед</th>
                <th style="width: 60px">Кол-во</th>
                <th style="width: 80px; text-align: right">Цена</th>
                <th style="width: 80px; text-align: right">Сумма</th>
            </tr>
            </thead>
            <tbody>
            @php($count=1)
            @foreach($data->items as $items_value)
                <tr>
                    <td style="text-align: center">{{ $count }}</td>
                    @foreach($items_value->options as $options_key => $options_value)
                        @php($items_value->name .= ' ('. $options_key .': '. $options_value .')')
                    @endforeach
                    <td class="editable-input"><input type="text" value="{{ $items_value->name }}" title="Кликните для редактирования"></td>
                    <td class="editable-input"><input style="width: 50px" type="text" value="шт." title="Кликните для редактирования"></td>
                    <td class="editable-input"><input style="width: 40px" type="text" value="{{ $items_value->qty }}" title="Кликните для редактирования"></td>
                    <td class="editable-input" style="text-align: right"><input style="width: 80px; text-align: right" type="text" value="{{ $items_value->price }}" title="Кликните для редактирования"></td>
                    <td class="editable-input" style="text-align: right"><input style="width: 80px; text-align: right" type="text" value="{{ $items_value->subtotal }}" title="Кликните для редактирования"></td>
                </tr>
                @php($count++)
            @endforeach
            <tr>
                <td colspan="5" style="text-align: right; border-left: none; border-bottom: none">Всего со скидкой:</td>
                <td class="editable-input" style="text-align: right"><input style="width: 80px; text-align: right" type="text" value="{{ $data->cost }}" title="Кликните для редактирования"></td>
            </tr>
            </tbody>
        </table>

        <p class="cost_propis border_bottom padding_bottom_null all_width">
            <span class="white_bg">Всего отпущено на сумму:</span>
            <span class="span_rub_value editable-input"><input class="uk-text-right" style="width: 600px" type="text" value="{{ $all_cost_string['rub'] }}"></span> <span class="span_rub">руб.</span>
            <span class="span_kop_value editable-input"><input style="width: 30px" type="text" value="{{ $all_cost_string['cop'] }}"></span> <span class="span_kop">коп.</span>
        </p>
        <br/>
        <p class="write_line">Продавец <span style="width: 150px;" class="border_bottom"><small>подпись</small></span> <span style="width: 230px; margin-left: 20px" class="border_bottom"><small class="fio_prod">ф.и.о.</small></span></p>
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