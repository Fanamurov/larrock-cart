@extends('larrock::admin.main')
@section('title') {{ $app->name }} admin @endsection

@section('content')
    <div class="container-head uk-margin-bottom">
        <div class="uk-grid">
            <div class="uk-width-expand">
                {!! Breadcrumbs::render('admin.cart.index') !!}
            </div>
            <div class="uk-width-auto">
                @if(isset($allowCreate))
                    <a class="uk-button uk-button-large uk-button-primary" href="/admin/{{ $app->name }}/create">Создать новый заказ</a>
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