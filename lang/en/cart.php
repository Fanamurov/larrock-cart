<?php


return [

    'found' => 'Найдено записей: :count',

    '404' => 'Такого заказа #:number на сайте нет',
    '404_tovar' => 'Такого товара #:number на сайте нет',

    'delete' => 'Заказ #:number успешно удален',
    'delete_fail' => 'Заказ #:number не удален',

    'cancel' => 'Заказ #:number успешно отменен',
    'cancel_fail' => 'Произошла ошибка во время отмены заказа #:number',

    'empty' => 'Ваша корзина пуста',

    'order_create' => 'Ваш заказ #:number успешно добавлен',
    'order_create_fail' => 'Произошла ошибка. Не удалось оформить заказ. Пожалуйста, свяжитесь с администрацией сайта',

    'tovar' => [
        'reserved' => 'Товар для вас зарезервирован',
        'reserved_fail' => 'Не удалось зарезервировать товар под ваш заказ',
        'lost' => 'Товара :name из вашей корзины больше нет в нашем каталоге',
        'lost_change_cart' => 'Товара :name из вашей корзины больше нет в нашем каталоге',
    ],

    'admin' => [
        'order_create' => 'Заказ #:number успешно добавлен администратором',
        'order_create_notice' => 'По-умолчанию создание заказа администратором не является поводом для отправки 
        уведомления покупателю по email. Воспользуйтесь соответствующей кнопкой у заказа',
        'order_change' => 'Заказ #:number изменен',
        'tovar_lost' => 'Такого товара уже нет в заказе',
        'order_not_change' => 'Заказ #:number не изменен',
        'tovar_add' => 'Товар :name успешно добавлен к заказу',
        'tovar_add_fail' => 'Добавить товар к заказу не удалось',

        'nalicie_change' => 'Остатки товара изменены',
        'nalicie_not_change' => 'Остатки товара не изменены',
    ]

];