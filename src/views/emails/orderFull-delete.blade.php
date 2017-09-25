@extends('vendor.larrock.emails.template.body')

@section('content')
    <h1 style="font:26px/32px Calibri,Helvetica,Arial,sans-serif;">{{ $subject }}</h1>
    <h3>Статус заказа: {{ $data['status_order'] }}</h3>
    <h3>Статус оплаты: {{ $data['status_pay'] }}</h3>
    <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;"><strong>ФИО:</strong> {{ $data['fio'] }}</p>
    <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;"><strong>Email/логин в личный кабинет:</strong> {{ $data['email'] }}</p>
    <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;"><strong>Телефон:</strong> {{ $data['tel'] }}</p>
    <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;"><strong>Адрес доставки:</strong> {{ $data['address'] }}</p>
    <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;"><strong>Метод доставки:</strong> {{ $data['method_delivery'] }}</p>
    <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;"><strong>Метод оплаты:</strong> {{ $data['method_pay'] }}</p>
    @if( !empty($data['comment']))
        <p style="font:14px/16px Calibri,Helvetica,Arial,sans-serif;"><strong>Комментарий к заказу:</strong> {{ $data['comment'] }}</p>
    @endif

    <table lang="ru" style="width: 100%; padding-top: 15px" cellspacing="0" cellpadding="5">
        <thead>
        <tr>
            <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;font-weight:700">Наименование</td>
            <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;font-weight:700">Количество</td>
            <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;font-weight:700">Стоимость</td>
            <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;font-weight:700">Итого</td>
        </tr>
        </thead>
        <tbody>
        @foreach($data['items'] as $item)
            <tr>
                @if(isset($item->catalog->full_url))
                    <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;"><a href="{{ env('APP_URL') }}{{ $item->catalog->full_url }}">{{ $item->name }}</a></td>
                @else
                    <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;">{{ $item->name }}</td>
                @endif
                <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;">{{ $item->qty }}</td>
                <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;">{{ $item->price }}</td>
                <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;">{{ $item->subtotal }} руб.</td>
            </tr>
        @endforeach
        @if($data['cost_discount'] > 0 && $data['cost_discount'] < $data['cost'])
            <tr>
                <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;text-align: right" colspan="4">Итого: {!! $data['cost'] !!} руб.</td>
            </tr>
            <tr>
                <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;text-align: right" colspan="4"><strong>Всего к оплате со скидкой: {!! $data['cost_discount'] !!} руб.</strong></td>
            </tr>
            @if(isset($data['discount']->discount))
                <tr>
                    <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif" colspan="4">
                        <p style="font-weight:700">Примененные скидки:
                        @if(array_key_exists('cart', $data['discount']->discount))
                            <sup>*</sup>{{ $data['discount']->discount->cart->description }}<br/>
                        @endif
                        @if(array_key_exists('history', $data['discount']->discount))
                            <sup>*</sup>{{ $data['discount']->discount->history->description }}<br/>
                        @endif
                        @if(array_key_exists('category', $data['discount']->discount))
                            <sup>*</sup>{{ $data['discount']->discount->category->description }}<br/>
                        @endif
                        </p>
                    </td>
                </tr>
            @endif
        @else
            <tr>
                <td style="border: #bcbcbc 1px solid;font:14px/16px Calibri,Helvetica,Arial,sans-serif;text-align: right" colspan="4"><strong>Всего к оплате: {!! $data['cost'] !!} руб.</strong></td>
            </tr>
        @endif
        </tbody>
    </table>

    <p style="font:18px/20px Calibri,Helvetica,Arial,sans-serif;">Ссылка для оплаты/отслеживания заказов: <a href="{{ env('APP_URL') }}/user" target="_blank">личный кабинет</a></p>
@endsection

@section('footer')
    @include('vendor.larrock.emails.template.footer')
@endsection