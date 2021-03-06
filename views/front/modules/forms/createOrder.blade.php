<form id="form-orderFull" class="form-orderFull uk-form uk-form-stacked" method="post" action="/cart/order">
    <p class="uk-h2">Оформление заказа:</p>
    <div class="uk-grid">
        @if( !Auth::guard()->check())
            <div class="uk-width-1-1 uk-width-medium-1-2">
                <div class="uk-form-row">
                    <label for="email" class="uk-form-label">Ваш email:<span class="text-muted" style="display: none"><sup>*он же логин</sup></span></label>
                    <input type="email" name="email" id="email" tabindex="1" class="uk-width-1-1 uk-form-large"
                           value="@if(Auth::guard()->check()){!! Auth::guard()->user()->email !!}@endif" required>
                </div>
            </div>
            <div class="uk-width-1-1 uk-width-medium-1-2">
                <div class="uk-form-row">
                    <label for="password" class="uk-form-label">Введите пароль или придумайте новый:</label>
                    <input type="text" name="password" id="password" required tabindex="2" class="uk-width-1-1 uk-form-large" disabled>
                </div>
            </div>
            <div class="uk-width-1-1 uk-width-medium-1-1">
                <div class="uk-form-row uk-margin-top">
                    <label for="without_registry" class="uk-form-label">
                        <input type="checkbox" name="without_registry" id="without_registry" value="true" checked> Сделать заказ без регистрации (email не обязателен, пароль не требуется)
                    </label>
                </div>
            </div>
        @else
            <div class="uk-width-1-1">
                <div class="uk-form-row">
                    <label for="email"><span class="uk-text-muted">Ваш email:</span> @if(Auth::guard()->check()){!! Auth::guard()->user()->email !!}@endif<span class="text-muted"><sup>*он же логин</sup></span></label>
                    <a class="uk-button uk-align-right" href="/logout"><i class="uk-icon-close"></i> Выйти</a>
                    <input type="hidden" name="email" id="email" tabindex="1"
                           value="@if(Auth::guard()->check() && empty(old('email'))){!! Auth::guard()->user()->email !!}@else {{ old('email') }} @endif">
                </div>
            </div>
        @endif
    </div>
    <div class="uk-grid">
        <div class="uk-width-1-1 uk-width-medium-5-10">
            @if(isset($package->rows['fio']))
                <div class="uk-form-row">
                    <label for="fio" class="uk-form-label">ФИО или название компании:</label>
                    <input type="text" name="fio" id="fio" class="uk-width-1-1 uk-form-large"
                           value="@if(Auth::guard()->check() && empty(old('fio'))){!! Auth::guard()->user()->fio !!}@else{{ old('fio') }}@endif" required>
                </div>
            @endif
            @if(isset($package->rows['tel']))
                <div class="uk-form-row">
                    <label for="tel" class="uk-form-label">Номер телефона:</label>
                    <input type="tel" name="tel" id="tel" class="uk-width-1-1 uk-form-large"
                           value="@if(Auth::guard()->check()){!! Auth::guard()->user()->tel !!}@else{{ old('tel') }}@endif" required>
                </div>
            @endif
            @if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount'))
                <div class="uk-form-row">
                    <label for="fio" class="uk-form-label">У вас есть скидочный купон?</label>
                    <input type="text" value="" placeholder="ИМЯ купона" name="kupon" class="uk-width-1-1 uk-form-large" onchange="checkKuponDiscount()">
                    <div class="kupon_text uk-alert" style="display: none"></div>
                </div>
            @endif
        </div>
        <div class="uk-width-1-1 uk-width-medium-5-10">
            @if(isset($package->rows['comment']))
                <div class="uk-form-row">
                    <label for="comment" class="uk-form-label">Комментарий к заказу:</label>
                    <textarea name="comment" id="comment" class="uk-width-1-1">{{ old('comment') }}</textarea>
                </div>
            @endif

            @if(isset($package->rows['method_delivery']))
                <div class="uk-form-row uk-hidden">
                    <label for="method_delivery" class="uk-form-label">Метод доставки:</label>
                    <select name="method_delivery" id="delivery-method" class="uk-width-1-1 uk-form-large">
                        @foreach($package->rows['method_delivery']->options as $key => $value)
                            <option @if(old('method_delivery') === $key) selected @endif value="{{ $key }}" data-cost="{{ $value }}">{{ $key }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="cost_delivery" value="0">
                <div class="uk-form-row row-address" style="display: none;">
                    <label for="address" class="uk-form-label">Адрес доставки:</label>
                    <textarea name="address" id="address" class="uk-width-1-1" placeholder="Укажите город, улицу, дом, номер квартиры/офиса" required>@if(Auth::guard()->check() && empty(old('address'))){{ Auth::guard()->user()->address }}@else {{ old('address') }} @endif</textarea>
                </div>
            @endif

            @if(isset($package->rows['method_pay']))
                <div class="uk-form-row uk-hidden">
                    <label for="method_pay" class="uk-form-label">Метод оплаты:</label>
                    <select name="method_pay" id="pay-method" class="uk-width-1-1 uk-form-large">
                        @foreach($package->rows['method_pay']->options as $value)
                            <option @if(old('method_delivery') === $value) selected @endif value="{{ $value }}" data-cost="{{ $value }}">{{ $value }}</option>
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
                <div class="repeat-total uk-text-right uk-margin-top">К оплате: <span class="repeat-total-cost">{{ Cart::instance('main')->total() }}</span> рублей</div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</form>
{!! JsValidator::formRequest('Larrock\ComponentCart\Requests\OrderFullRequest', '#form-orderFull') !!}

@push('scripts')
    <script>
        var method_delivery = $('select[name=method_delivery] option:selected').val();
        var cost_delivery = parseFloat($('select[name=method_delivery] option:selected').attr('data-cost'));
        var cost_tovar = parseFloat($('.row-total').attr('data-clear-total'));

        $('.delivery_total').html(cost_delivery);
        $('input[name=cost_delivery]').val(cost_delivery);
        $('.total').html(cost_delivery + cost_tovar);

        if(method_delivery === 'самовывоз'){
            $('.row-address').slideUp('slow')
        }else{
            $('.row-address').slideDown('slow')
        }

        $('select[name=method_delivery]').change(function () {
            var method_delivery = $('select[name=method_delivery] option:selected').val();
            if(method_delivery === 'самовывоз'){
                $('.row-address').slideUp('slow')
            }else{
                $('.row-address').slideDown('slow')
            }
            cost_delivery = parseFloat($('select[name=method_delivery] option:selected').attr('data-cost'));
            cost_tovar = parseFloat($('.row-total').attr('data-clear-total'));

            $('.delivery_total').html(cost_delivery);
            $('.total').html(cost_delivery + cost_tovar);
            $('input[name=cost_delivery]').val(cost_delivery);
        });

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
