<div class="moduleCart">
    <p class="cart-empty @if(Cart::instance('main')->count() > 0) uk-hidden @endif"><i class="uk-icon-shopping-cart"></i> Корзина пуста</p>
    <p class="cart-show @if(Cart::instance('main')->count() < 1) uk-hidden @endif">
        <a href="/cart">
            <span class="flaticon flaticon-shopping-cart"></span>
            В корзине на сумму <span class="total_cart text">
                @if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount') && $discountsShare['profit'] > 0)
                    {{ $discountsShare['cost_after_discount'] }}
                @else
                    {!! Cart::instance('main')->total() !!}
                @endif
            </span> р.
            @if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount') && $discountsShare['profit'] > 0)
                <span class="moduleCart-discount_row" @if($discountsShare['profit'] < 1) style="display: none" @endif>скидка: <span class="total_discount_cart">{{ $discountsShare['profit'] }}</span> р.</span>
            @endif
        </a>
    </p>
</div>