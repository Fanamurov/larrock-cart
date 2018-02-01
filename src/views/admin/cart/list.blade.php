@extends('larrock::admin.main')
@section('title') {{ $app->name }} admin @endsection

@section('content')
    <div class="container-head uk-margin-bottom">
        {!! Breadcrumbs::render('admin.cart.index') !!}
    </div>

    @include('larrock::admin.cart.filters')

    <div class="add-panel uk-width-1-1 uk-text-right uk-margin-bottom">
        <a class="uk-button uk-button-large uk-button-primary" href="/admin/{{ $app->name }}/create">Создать новый заказ</a>
    </div>

    @if(count($data) === 0)
        <div class="uk-alert uk-alert-warning">Заказов еще нет</div>
    @else
        @foreach($data as $value)
            <div class="uk-margin-large-bottom">
                @include('larrock::admin.cart.order-item', ['data' => $value, 'catalog' => $catalog])
            </div>
        @endforeach

        @if($data->total() > 10)
            <div class="uk-margin-large-bottom">
                {!! $data->render() !!}
            </div>
        @endif
    @endif
@endsection