<?php

namespace Larrock\ComponentCart\Helpers;

use Mail;
use Illuminate\Http\Request;
use Larrock\Core\Helpers\MessageLarrock;

class CartMail
{
    /**
     * Отправка уведомлений об изменении заказа на email покупателя и админов.
     * @param Request $request
     * @param $order
     * @param null $subject
     * @throws \Exception
     */
    public function mailOrder(Request $request, $order, $subject = null)
    {
        $mails = array_map('trim', explode(',', env('MAIL_TO_ADMIN')));
        if ($order->email) {
            $mails[] = $order->email;
        }
        $mails = array_unique($mails);

        if (! $subject) {
            $subject = 'Заказ #'.$order->order_id.' на сайте '.env('SITE_NAME')
                .' '.$order->status_order.' '.$order->status_pay;
        }
        /* @noinspection PhpVoidFunctionResultUsedInspection */
        Mail::send('larrock::emails.orderFull', ['data' => $order->toArray(), 'subject' => $subject],
            function ($message) use ($mails, $subject) {
                $message->from('no-reply@'.array_get($_SERVER, 'HTTP_HOST'), env('MAIL_FROM_NAME', 'ROBOT'));
                $message->to($mails);
                $message->subject($subject);
            });

        \Log::info('ORDER CHANGE: #'.$order->order_id.'. Order: '.json_encode($order));
        if ($order->email) {
            MessageLarrock::success('На email покупателя отправлено письмо с информацией по заказу');
        }
    }
}
