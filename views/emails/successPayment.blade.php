@extends('larrock::emails.template.body')

@section('content')
    <h1 style="color: #202020 !important;
    display: block;
    font-family: Arial, sans-serif;
    font-size: 26px;
    font-style: normal;
    font-weight: bold;
    line-height: 100%;
    letter-spacing: normal;
    margin-top: 0;
    margin-right: 0;
    margin-bottom: 35px;
    margin-left: 0;
    text-align: left;">{{ $subject }}</h1>
    <h2 style="font-family: Arial, sans-serif; margin-bottom: 10px; font-size: 18px;">Статус заказа: {{ $data['status_order'] }}</h2>
    @if(isset($app->rows['status_pay']) && !empty($data['status_pay']))
        <h2 style="font-family: Arial, sans-serif; margin-top: 0;font-size: 18px; margin-bottom: 35px;">Статус оплаты: {{ $data['status_pay'] }}</h2>
    @endif
    @if(isset($app->rows['fio']) && !empty($data['fio']))
        <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;">ФИО: <strong>{{ $data['fio'] }}</strong></p>
    @endif
    @if(isset($app->rows['email']) && !empty($data['email']))
        <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;">Email/логин в личный кабинет: <strong>{{ $data['email'] }}</strong></p>
    @endif
    @if(isset($app->rows['tel']) && !empty($data['tel']))
        <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;">Телефон: <strong>{{ $data['tel'] }}</strong></p>
    @endif
    @if(isset($app->rows['address']) && !empty($data['address']))
        <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;">Адрес доставки: <strong>{{ $data['address'] }}</strong></p>
    @endif
    @if(isset($app->rows['method_delivery']) && !empty($data['method_delivery']))
        <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;">Метод доставки: <strong>{{ $data['method_delivery'] }}</strong></p>
    @endif
    @if(isset($app->rows['method_pay']) && !empty($data['method_pay']))
        <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;">Метод оплаты: <strong>{{ $data['method_pay'] }}</strong></p>
    @endif
    @if(isset($app->rows['comment']) && !empty($data['comment']))
        <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;">Комментарий к заказу: <strong>{{ $data['comment'] }}</strong></p>
    @endif

    <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;">Момент регистрации заказа в сервисе Яндекс.Денег: <strong>{{ $kassa['orderCreatedDatetime'] }}</strong></p>
    <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;">Момент регистрации оплаты заказа в Яндекс.Деньгах: <strong>{{ $kassa['paymentDatetime'] }}</strong></p>
    <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;">Уникальный номер транзакции в сервисе Яндекс.Денег: <strong>{{ $kassa['invoiceId'] }}</strong></p>
    <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;">Способ оплаты заказа: <strong>{{ $kassa['paymentType'] }}</strong></p>

    <table align="center" border="0" cellpadding="10" cellspacing="0" width="100%" style="height: 100% !important;
    margin: 0;
    padding: 0;
    width: 100% !important; border-collapse: collapse !important; margin-bottom: 50px; margin-top: 50px;">
        <thead>
        <tr>
            <td width="75px" style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;font-weight:400;width: 75px;font-style: italic; color: grey;"></td>
            <td style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;font-weight:400;font-style: italic; color: grey;">Наименование</td>
            <td style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;font-weight:400;font-style: italic; color: grey;">Кол-во</td>
            <td style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;font-weight:400;font-style: italic; color: grey;">Цена</td>
            <td style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;font-weight:400;font-style: italic; color: grey;">Итого</td>
        </tr>
        </thead>
        <tbody>
        @foreach($data['items'] as $item)
            <tr>
                <td width="75px" style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;width: 75px">
                    @if(config('larrock.catalog.ShowItemPage') === true && isset($item->catalog->full_url))
                        <a href="{{ env('APP_URL') }}{!! $item->catalog->full_url !!}">
                            <img style="width: 75px" src="{{ env('APP_URL') }}{!! $item->catalog->getFirstImage->getUrl('140x140') !!}">
                        </a>
                    @else
                        <img style="width: 75px" src="{{ env('APP_URL') }}{!! $item->catalog->getFirstImage->getUrl('140x140') !!}">
                    @endif
                </td>
                <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;">
                    @if(config('larrock.catalog.ShowItemPage') === true && isset($item->catalog->full_url))
                        <a href="{{ env('APP_URL') }}{{ $item->catalog->full_url }}">{{ $item->name }}</a>
                    @else
                        {{ $item->name }}
                    @endif
                    @foreach($item->options as $key_option => $option)
                        @if( !is_object($option))
                            <p><span class="uk-text-muted">@lang('larrock::fields.'.$key_option):</span> {{ $option }}</p>
                        @else
                            <p class="{{$key_option}}-param">
                                @if(array_key_exists('className', $option))
                                    <span class="uk-form-label">@lang('larrock::fields.'.$option->className):</span>
                                @else
                                    <span class="uk-form-label">@lang('larrock::fields.'.$key_option):</span>
                                @endif
                                {{ $option->title }}
                            </p>
                        @endif
                    @endforeach
                </td>
                <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;">
                    {{ $item->qty }}
                </td>
                <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;">
                    @if($item->price > 0)
                        {{ $item->price }}
                    @else
                        <small>договорная</small>
                    @endif
                </td>
                <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;">
                    @if($item->subtotal > 0)
                        {{ $item->subtotal }} руб.
                    @else
                        <small>договорная</small>
                    @endif
                </td>
            </tr>
        @endforeach
        @if($data['cost'] > 0)
            <tr>
                <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;text-align: right" colspan="5"><strong>Итого к оплате: {!! $data['cost'] + $data['cost_delivery'] !!} руб.</strong></td>
            </tr>
            @if(isset($data->discount->profit))
                <tr>
                    <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;text-align: right" colspan="5">Скидка: {!! $data->discount->profit !!} руб.</td>
                </tr>
                <tr>
                    <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif" colspan="5">
                        <p style="font-weight:700">Примененные скидки:<br/>
                            @if(isset($data->discount->d_cart))
                                <sup>*</sup>{{ $data->discount->d_cart->description }}<br/>
                            @endif
                            @if(isset($data->discount->d_history))
                                <sup>*</sup>{{ $data->discount->d_history->description }}<br/>
                            @endif
                            @if(isset($data->discount->d_kupon))
                                <sup>*</sup>{{ $data->discount->d_kupon->description }}<br/>
                            @endif
                        </p>
                    </td>
                </tr>
            @endif
        @else
            <tr>
                <td style="border: #bcbcbc 1px solid;font:20px/26px Calibri,Helvetica,Arial,sans-serif;text-align: right" colspan="5">
                    @if($data['cost'] > 0)
                        <strong>Всего к оплате: {!! $data['cost'] + $data['cost_delivery'] !!} руб.</strong>
                    @else
                        <strong>Всего к оплате: по договоренности</strong>
                    @endif
                </td>
            </tr>
        @endif
        @if($data['cost_delivery'] > 0)
            <tr>
                <td style="border: #bcbcbc 1px solid;font:20px/26px Calibri,Helvetica,Arial,sans-serif;text-align: right" colspan="5">
                    <strong>Стоимость доставки (включено в общую сумму): {!! $data['cost_delivery'] !!} руб.</strong>
                </td>
            </tr>
        @endif
        </tbody>
    </table>

    <p style="font:18px/20px Calibri,Helvetica,Arial,sans-serif;">Ссылка для оплаты/отслеживания заказов: <a href="{{ env('APP_URL') }}/cabinet" target="_blank" style="color: #ffffff; font-size: 16px; background: #f71f00; padding: 7px 11px; border: 1px solid #d4d4d4; text-decoration: none; font-family: Arial, sans-serif;">личный кабинет</a></p>
@endsection

@section('footer')
    @include('larrock::emails.template.footer')
@endsection