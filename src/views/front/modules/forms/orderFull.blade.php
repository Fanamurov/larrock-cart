<form id="form-orderFull" class="form-orderFull uk-form uk-form-stacked" method="post" action="/cart/full">
    <p class="uk-h2">Оформление заказа:</p>
    <div class="uk-grid">
        @if( !Auth::guard()->check())
            <div class="uk-width-1-1 uk-width-medium-1-2">
                <div class="uk-form-row">
                    <label for="email" class="uk-form-label">Ваш email:<span class="text-muted"><sup>*он же логин</sup></span></label>
                    <input type="email" name="email" id="email" tabindex="1" class="uk-width-1-1 uk-form-large"
                           value="@if(Auth::guard()->check()){!! Auth::guard()->user()->email !!}@endif" required>
                </div>
            </div>
            <div class="uk-width-1-1 uk-width-medium-1-2">
                <div class="uk-form-row">
                    <label for="password" class="uk-form-label">Введите пароль или придумайте новый:</label>
                    <input type="text" name="password" id="password" required tabindex="2" class="uk-width-1-1 uk-form-large">
                </div>
            </div>
            <div class="uk-width-1-1 uk-width-medium-1-1">
                <div class="uk-form-row uk-margin-top">
                    <label for="without_registry" class="uk-form-label">
                        <input type="checkbox" name="without_registry" id="without_registry" value="true"> Сделать заказ без регистрации (email не обязателен, пароль не требуются)
                    </label>
                </div>
            </div>
        @else
            <div class="uk-width-1-1">
                <div class="uk-form-row">
                    <label for="email"><span class="uk-text-muted">Ваш email:</span> @if(Auth::guard()->check()){!! Auth::guard()->user()->email !!}@endif<span class="text-muted"><sup>*он же логин</sup></span></label>
                    <a class="uk-button uk-align-right" href="/user/logout"><i class="uk-icon-close"></i> Выйти</a>
                    <input type="hidden" name="email" id="email" tabindex="1"
                           value="@if(Auth::guard()->check() && empty(old('email'))){!! Auth::guard()->user()->email !!}@else {{ old('email') }} @endif">
                </div>
            </div>
        @endif
    </div>
    <div class="uk-grid">
        <div class="uk-width-1-1 uk-width-medium-5-10">
            @if(isset($app->rows['fio']))
                <div class="uk-form-row">
                    <label for="fio" class="uk-form-label">ФИО или название компании:</label>
                    <input type="text" name="fio" id="fio" class="uk-width-1-1 uk-form-large"
                           value="@if(Auth::guard()->check() && empty(old('fio'))){!! Auth::guard()->user()->fio !!}@else{{ old('fio') }}@endif" required>
                </div>
            @endif
            @if(isset($app->rows['tel']))
                <div class="uk-form-row">
                    <label for="tel" class="uk-form-label">Номер телефона:</label>
                    <input type="tel" name="tel" id="tel" class="uk-width-1-1 uk-form-large"
                           value="@if(Auth::guard()->check()){!! Auth::guard()->user()->tel !!}@else{{ old('tel') }}@endif" required>
                </div>
            @endif
            @if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount'))
                <div class="uk-form-row">
                    <label for="fio" class="uk-form-label">У вас есть скидочный купон?</label>
                    <input type="text" value="" placeholder="ИМЯ купона" name="kupon" class="uk-width-1-1 uk-form-large">
                </div>
            @endif
        </div>
        <div class="uk-width-1-1 uk-width-medium-5-10">
            @if(isset($app->rows['comment']))
                <div class="uk-form-row">
                    <label for="comment" class="uk-form-label">Комментарий к заказу:</label>
                    <textarea name="comment" id="comment" class="uk-width-1-1">{{ old('comment') }}</textarea>
                </div>
            @endif

            @if(isset($app->rows['method_delivery']))
                <div class="uk-form-row">
                    <label for="delivery-method" class="uk-form-label">Метод доставки:</label>
                    <select name="delivery-method" id="delivery-method" class="uk-width-1-1 uk-form-large">
                        @foreach($app->rows['method_delivery']->options as $value)
                            <option @if(old('delivery-method') === $value) selected @endif value="{{ $value }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if(isset($app->rows['method_pay']))
                <div class="uk-form-row">
                    <label for="address" class="uk-form-label">Адрес доставки:</label>
                    <textarea name="address" id="address" class="uk-width-1-1" placeholder="Укажите город, улицу, дом, номер квартиры/офиса" required>@if(Auth::guard()->check() && empty(old('address'))){{ Auth::guard()->user()->address }}@else {{ old('address') }} @endif</textarea>
                </div>
            @endif

            @if(isset($app->rows['method_pay']))
                <div class="uk-form-row">
                    <label for="pay-method" class="uk-form-label">Метод оплаты:</label>
                    <select name="pay-method" id="pay-method" class="uk-width-1-1 uk-form-large">
                        @foreach($app->rows['method_pay']->options as $value)
                            <option @if(old('pay-method') === $value) selected @endif value="{{ $value }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

            <div class="uk-form-row">
                <label class="uk-form-label"><input type="checkbox" value="1" name="oferta" checked>
                    Я принимаю <a href="/cart/oferta" target="_blank">условия оферты</a></label>
            </div><br/>

            {{ csrf_field() }}
            <div class="uk-form-row">
                <button type="submit" class="uk-button uk-button-primary uk-button-large uk-width-1-1" name="submit_orderFull">Оформить заказ</button>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</form>
{!! JsValidator::formRequest('Larrock\ComponentCart\Requests\OrderFullRequest', '#form-orderFull') !!}

@push('scripts')
    <script>
        $('input[name=without_registry]').change(function(){
            if($('input[name=without_registry]:checked').val() === 'true'){
                $('input[name=password]').prop('disabled', true);
                $('label[for=email]').find('span.text-muted').hide();
            }else{
                $('input[name=password]').prop('disabled', false);
                $('label[for=email]').find('span.text-muted').show();
            }
        });
    </script>
@endpush