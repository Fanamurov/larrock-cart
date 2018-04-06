@extends('larrock::admin.main')
@section('title') {{ $package->name }} admin @endsection

@section('content')
    <div class="container-head uk-margin-bottom">
        <div class="uk-grid">
            <div class="uk-width-expand">
                {!! Breadcrumbs::render('admin.cart.index') !!}
            </div>
            <div class="uk-width-auto">
                @if(isset($allowCreate))
                    <a class="uk-button uk-button-large uk-button-primary" href="/admin/{{ $package->name }}/create">Создать новый заказ</a>
                @endif
            </div>
        </div>
    </div>

    @include('larrock::admin.cart.filters')

    @if(count($data) === 0)
        <div class="uk-alert uk-alert-warning">Заказов еще нет</div>
    @else
        @foreach($data as $value)
            <div class="uk-margin-bottom ibox-content">
                @include('larrock::admin.cart.order-item', ['data' => $value, 'catalog' => $catalog])
            </div>
        @endforeach

        @if(method_exists($data, 'total'))
            <div class="uk-margin-large-bottom">
                {!! $data->links('larrock::admin.pagination.uikit3') !!}
            </div>
        @endif
    @endif
@endsection

    <script type="text/javascript">
        function AddTovalList(order_id) {
            $('.show-add_to_cart'+ order_id).addClass('uk-hidden');
            $('#tovar'+ order_id).removeClass('uk-hidden');
            $('#tovar'+ order_id).selectize({
                maxItems: 1,
                valueField: 'id',
                labelField: 'title',
                searchField: 'title',
                persist: false,
                createOnBlur: false,
                create: false,
                placeholder: '-- Добавить товар к заказу --',
                allowEmptyOption: true,
                options: [
                        @foreach($catalog as $value)
                    {
                        title: '{!! $value->title !!}',
                        id: '{!! $value->id !!}',
                        cost: '{!! $value->first_cost_value !!}',
                        what: '{!! $value->what !!}'
                    },
                    @endforeach
                ],
                render: {
                    item: function (item, escape) {
                        return '<div>' +
                            (item.title ? '<span class="title">' + escape(item.title.replace('&quot;', '').replace('&quot;', '')) + ' ' + item.cost + ' ' + item.what + '</span>' : '') +
                            (item.category ? '<br/><span class="category">' + escape(item.category.replace('&quot;', '').replace('&quot;', '')) + '</span>' : '') +
                            '</div>';
                    },
                    option: function (item, escape) {
                        return '<div>' +
                            '<span class="uk-label">' + escape(item.title.replace('&quot;', '').replace('&quot;', '')) + ' ' + item.cost + ' ' + item.what + '</span>' +
                            (item.category ? '<br/><span class="caption">в разделе: ' + escape(item.category.replace('&quot;', '').replace('&quot;', '')) + '</span>' : '') +
                            '</div>';
                    }
                },
                onItemAdd: function (value, item) {
                    UIkit.notification({
                        message: 'Добавляем товар к заказу...',
                        status: 'primary',
                        pos: 'top-right',
                        timeout: 5000
                    });
                    $.ajax({
                        url: '/admin/ajax/cartAdd',
                        type: 'POST',
                        dataType: 'html',
                        data: {
                            id: value,
                            order_id: order_id,
                            ajax: 'true',
                            in_template: 'true'
                        },
                        error: function () {
                            alert('ERROR!');
                        },
                        success: function (res) {
                            $('.tovars_container' + order_id).html(res);
                            notify_show('success', 'Товар успешно добавлен к заказу');
                        }
                    });
                    return false;
                }
            });
        }
    </script>