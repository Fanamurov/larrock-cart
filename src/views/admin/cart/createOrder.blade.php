@extends('larrock::admin.main')
@section('title') {{ $app->name }} admin @endsection

@section('content')
    <div class="container-head uk-margin-bottom">
        {!! Breadcrumbs::render('admin.cart.index') !!}
    </div>

    <form method="post" action="/admin/cart/createOrder">
        <div class="ibox-content">
            <h1>Шаг 1. Выбор товаров</h1>
            <div class="uk-form">
                <select name="tovar[]" id="tovar"></select>
            </div>
            <div class="uk-grid" id="tovars_container"></div>
        </div>

        <div class="ibox-content">
            <h1>Шаг 2. Внесение данных покупателя</h1>
            <div class="uk-form uk-form-stacked">
                @include('larrock::admin.cart.order-item-user-info', ['data' => LarrockUsers::getModel(), 'formRemove' => TRUE])
            </div>
        </div>

        <div class="uk-width-1-1 uk-text-right uk-margin-large-top uk-margin-large-bottom">
            {{ csrf_field() }}
            <button type="submit" class="uk-button uk-button-large uk-button-primary">Создать заказ</button>
        </div>
    </form>

    <script type="text/javascript">
        $('#tovar').selectize({
            maxItems: null,
            valueField: 'id',
            labelField: 'title',
            searchField: 'title',
            persist: false,
            createOnBlur: false,
            create: false,
            plugins: ['remove_button'],
            allowEmptyOption: true,
            sortField: {
                field: 'title',
                direction: 'asc'
            },
            options: [
                @foreach($catalog as $value)
                {
                    title: '{!! $value->title !!}',
                    id: '{!! $value->id !!}',
                    category: '{!! $value->get_category()->first()->title !!}',
                    cost: '{!! $value->first_cost_value !!}',
                    what: '{!! $value->what !!}'
                },
                @endforeach
            ],
            render: {
            item: function(item, escape) {
                return '<div>' +
                    (item.title ? '<span class="title">' + escape(item.title.replace('&quot;', '').replace('&quot;', '')) + ' '+ item.cost +' '+ item.what +'</span>' : '') +
                    (item.category ? '<br/><span class="category">' + escape(item.category.replace('&quot;', '').replace('&quot;', '')) + '</span>' : '') +
                    '</div>';
            },
            option: function(item, escape) {
                return '<div>' +
                    '<span class="uk-label">' + escape(item.title.replace('&quot;', '').replace('&quot;', '')) + ' '+ item.cost +' '+ item.what +'</span>' +
                    (item.category ? '<br/><span class="caption">в разделе: ' + escape(item.category.replace('&quot;', '').replace('&quot;', '')) + '</span>' :'') +
                    '</div>';
            }
        },
        onItemAdd: function(value, item){
            $.ajax({
                url: '/admin/cart/getTovarForCreate',
                type: 'POST',
                dataType: 'html',
                data: {
                    id: value
                },
                error: function() {
                    alert('ERROR!');
                },
                success: function(res) {
                    $('#tovars_container').append(res);
                }
            });
        },
        onItemRemove: function(value, item){
            $('#createOrderItem'+value).remove();
        }});
    </script>
@endsection