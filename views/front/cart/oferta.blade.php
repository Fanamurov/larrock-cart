@extends('larrock::front.main')
@section('title') Договор-оферта интернет-магазина {{ env('APP_URL') }} @endsection

@section('content')
    <div class="page_oferta">
        <h1>Договор-оферта интернет-магазина:</h1>
        <p>&nbsp;</p>
        <p>{{ env('SITE_NAME') }}, в лице интернет-магазина {{ env('APP_URL') }} (далее ПРОДАВЕЦ), публикует настоящий договор, являющийся публичным договором-офертой в адрес как физических, так и юридических лиц (далее ПОКУПАТЕЛЬ) о нижеследующем:</p>
        <h2>Статья 1. Предмет договора-оферты.</h2>
        <p>1.1. ПРОДАВЕЦ обязуется передать в собственность ПОКУПАТЕЛЮ, а ПОКУПАТЕЛЬ обязуется оплатить и принять заказанные в интернет-магазине {{ env('APP_URL') }} товары (далее ТОВАР).</p>
        <h2>Статья 2. Момент заключения договора.</h2>
        <p>2.1. Текст данного Договора является публичной офертой (в соответствии со статьей 435 и частью 2 статьи 437 Гражданского кодекса РФ).</p>
        <p>2.2. Факт оформления ЗАКАЗА ТОВАРА у ПРОДАВЦА как самостоятельно, так и через оператора, является безоговорочным принятием данного Договора, и ПОКУПАТЕЛЬ рассматривается как лицо, вступившее с {{ env('APP_URL') }} в договорные отношения.</p>
        <p>2.3. Оформление ЗАКАЗА ТОВАРА и расчета осуществляется путем заказа ПОКУПАТЕЛЕМ в интернет-магазине {{ env('APP_URL') }}.</p>
        <p>&nbsp;</p>
        <h2>Статья 3. Характеристики ТОВАРА.</h2>
        <p>3.1. В связи с разными техническими характеристиками мониторов цвет ТОВАРА может отличаться от представленного на сайте.</p>
        <p>3.2. Характеристики и внешний вид ТОВАРА могут отличаться от описанных на сайте.</p>
        <p>&nbsp;</p>
        <h2>Статья 4. Цена ТОВАРА.</h2>
        <p>4.1. Цены в интернет-магазине указаны в валюте страны покупателя за единицу ТОВАРА.</p>
        <p>4.2. Тарифы на оказание услуг по доставке, разгрузке, подъеме и сборке ТОВАРА указаны в интернет-магазине на каждый ТОВАР в зависимости от его характеристики.</p>
        <p>4.3. Общая сумма ЗАКАЗА, которая в некоторых случаях (по желанию покупателя) может включать платную доставку и сборку ТОВАРА, указывается в разделе &laquo;Корзина&raquo; в строке &laquo;Итого&raquo;.</p>
        <p>&nbsp;</p>
        <h2>Статья 5. Оплата ТОВАРА.</h2>
        <p>5.1. При наличной форме оплаты ПОКУПАТЕЛЬ обязан уплатить ПРОДАВЦУ цену ТОВАРА в момент его передачи, а ПРОДАВЕЦ обязан предоставить ПОКУПАТЕЛЮ кассовый или товарный чек, или иной документ, подтверждающий оплату ТОВАРА.</p>
        <p>5.2. При безналичной форме оплаты обязанность ПОКУПАТЕЛЯ по уплате цены ТОВАРА считается исполненной с момента зачисления соответствующих денежных средств в размере 100% (ста процентов) предоплаты на расчетный счет ПРОДАВЦА по реквизитам, указанным в п. 13 (Реквизиты магазина) настоящего ДОГОВОРА.</p>
        <p>5.3. При безналичной форме оплаты просрочка уплаты ПОКУПАТЕЛЕМ цены ТОВАРА на срок свыше 5 (пяти) дней является существенным нарушением настоящего договора. В этом случае ПРОДАВЕЦ вправе в одностороннем порядке отказаться от исполнения настоящего договора, уведомив об этом ПОКУПАТЕЛЯ.</p>
        <p>5.4. ТОВАРЫ поставляются ПОКУПАТЕЛЮ по ценам, наименованию, в количестве, соответствующем счету, оплаченному ПОКУПАТЕЛЕМ.</p>
        <p>&nbsp;</p>
        <h2>Статья 6. Доставка ТОВАРА.</h2>
        <p>6.1. Доставка ТОВАРА ПОКУПАТЕЛЮ осуществляется адресу и в сроки, согласованные ПОКУПАТЕЛЕМ и менеджером ПРОДАВЦА при оформлении ЗАКАЗА, либо ПОКУПАТЕЛЬ самостоятельно забирает товар со склада ПРОДАВЦА по адресу, указанному в п. 13 (Реквизиты магазина) настоящего ДОГОВОРА.</p>
        <p>6.2. Точная стоимость доставки ТОВАРА определяется менеджером ПРОДАВЦА при оформлении заказа и не может быть изменена после согласования ПОКУПАТЕЛЕМ.</p>
        <p>6.3. Неявка ПОКУПАТЕЛЯ или не совершение иных необходимых действий для принятия ТОВАРА могут рассматриваться ПРОДАВЦОМ в качестве отказа ПОКУПАТЕЛЯ от исполнения ДОГОВОРА.</p>
        <p>&nbsp;</p>
        <h2>Статья 7. Гарантии на товар.</h2>
        <p>7.1. На всю продукцию, продающуюся в Интернет-магазине {{ env('APP_URL') }}, имеются все необходимые сертификаты качества и санитарно-гигиенические заключения.</p>
        <p>7.2. Гарантийный срок эксплуатации на ТОВАР устанавливает производитель. Срок гарантии указывается в гарантийном талоне.</p>
        <p>&nbsp;</p>
        <h2>Статья 8. Права и обязанности сторон.</h2>
        <p>8.1. ПРОДАВЕЦ обязуется:</p>
        <p>8.1.1. Не разглашать любую частную информацию ПОКУПАТЕЛЯ и не предоставлять доступ к этой информации третьим лицам, за исключением случаев, предусмотренных Российским законодательством.</p>
        <p>8.1.2. Предоставить ПОКУПАТЕЛЮ возможность получения бесплатных телефонных консультаций по телефонам, указанным на сайте магазина ({{ env('APP_URL') }}). Объем консультаций ограничивается конкретными вопросами, связанными с выполнениями ЗАКАЗА.</p>
        <p>8.1.3. ПРОДАВЕЦ оставляет за собой право изменять настоящий ДОГОВОР в одностороннем порядке до момента его заключения.</p>
        <p>8.2. ПОКУПАТЕЛЬ обязуется:</p>
        <p>8.2.1. До момента заключения ДОГОВОРА ознакомиться с содержанием договора-оферты, условиями оплаты и доставки на сайте магазина ({{ env('APP_URL') }}).</p>
        <p>8.2.2. Предоставлять достоверную информацию о себе (ФИО, контактные телефоны, адрес электронной почты) и реквизиты для доставки ТОВАРА.</p>
        <p>8.2.3. Принять и оплатить ТОВАР в указанные в настоящем ДОГОВОРЕ сроки.</p>
        <p>&nbsp;</p>
        <h2>Статья 9. Ответственность сторон и разрешение споров.</h2>
        <p>9.1. Стороны несут ответственность за неисполнение или ненадлежащее исполнение настоящего ДОГОВОРА в порядке, предусмотренном настоящим ДОГОВОРОМ и действующим законодательством РФ.</p>
        <p>9.2. Продавец не несет ответственности за доставку ЗАКАЗА, если ПОКУПАТЕЛЕМ указан неправильный адрес доставки.</p>
        <p>9.3. ПРОДАВЕЦ не несет ответственности, если ожидания ПОКУПАТЕЛЯ о потребительских свойствах ТОВАРА оказались не оправданны.</p>
        <p>9.4. ПРОДАВЕЦ не несет ответственности за частичное или полное неисполнение обязательств по доставке ТОВАРА, если они являются следствием форс-мажорных обстоятельств.</p>
        <p>9.5. ПОКУПАТЕЛЬ, оформляя ЗАКАЗ, несет ответственность за достоверность предоставляемой информации о себе, а так же подтверждает, что с условиями настоящего ДОГОВОРА ознакомлен и согласен.</p>
        <p>9.6. Все споры и разногласия, возникающие при исполнении СТОРОНАМИ обязательств по настоящему Договору, решаются путем переговоров. В случае невозможности их устранения, СТОРОНЫ имеют право обратиться за судебной защитой своих интересов.</p>
        <p>&nbsp;</p>
        <h2>Статья 10. Возврат и обмен товара.</h2>
        <p>10.1. Требование ПОКУПАТЕЛЯ об обмене либо о возврате ТОВАРА подлежит удовлетворению, если ТОВАР не был в употреблении, сохранены его потребительские свойства, сохранена и не нарушена упаковка, сохранены документы, подтверждающие факт покупки этого ТОВАРА в интернет-магазине {{ env('APP_URL') }}.</p>
        <p>10.2. Срок такого требования составляет 14 (четырнадцать) дней с момента передачи ТОВАРА ПОКУПАТЕЛЮ.</p>
        <p>10.3. ПОКУПАТЕЛЬ компенсирует ПРОДАВЦУ необходимые транспортные расходы, понесенные в связи с организацией обмена или возврата ТОВАРА.</p>
        <p>&nbsp;</p>
        <h2>Статья 11. Форс-мажорные обстоятельства.</h2>
        <p>11.1. Стороны освобождаются от ответственности за неисполнение или ненадлежащее исполнение обязательств по Договору на время действия непреодолимой силы. Под непреодолимой силой понимаются чрезвычайные и непреодолимые при данных условиях обстоятельства, препятствующие исполнению своих обязательств СТОРОНАМИ по настоящему Договору. К ним относятся стихийные явления (землетрясения, наводнения и т. п.), обстоятельства общественной жизни (военные действия, чрезвычайные положения, крупнейшие забастовки, эпидемии и т. п.), запретительные меры государственных органов (запрещение перевозок, валютные ограничения, международные санкции запрета на торговлю и т. п.). В течение этого времени СТОРОНЫ не имеют взаимных претензий, и каждая из СТОРОН принимает на себя свой риск последствия форс-мажорных обстоятельств.</p>
        <p>&nbsp;</p>
        <h2>Статья 12. Срок действия договора.</h2>
        <p>12.1. Настоящий ДОГОВОР вступает в силу с момента обращения в {{ env('SITE_NAME') }} и оформления ЗАКАЗА, и заканчивается при полном исполнении обязательств СТОРОНАМИ.</p>
        <p>&nbsp;</p>
        <h2>Статья 13. Реквизиты интернет магазина.</h2>
        <p>Реквизиты магазина {{ env('SITE_NAME') }} находятся на странице "Контакты" <a href="/page/kontakty" target="_blank">{{ env('APP_URL') }}page/kontakty</a></p>
    </div>
@endsection