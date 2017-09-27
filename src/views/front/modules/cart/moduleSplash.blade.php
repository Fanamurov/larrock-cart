<div class="moduleCart">
    <p class="cart-empty @if(Cart::instance('main')->count() > 0) uk-hidden @endif"><i class="uk-icon-shopping-cart"></i> Корзина пуста</p>
    <p class="cart-show @if(Cart::instance('main')->count() < 1) uk-hidden @endif">
        <a href="/cart">
            <span class="uk-icon-shopping-cart"></span>
            <span class="cart-text">
            @if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount') && $discountsShare['profit'] > 0)
                @if($discountsShare['cost_after_discount'] > 0)
                    В корзине на сумму <span class="total_cart text uk-text-nowrap">{{ $discountsShare['cost_after_discount'] }}</span> р.
                @else
                    В корзине товаров: {{ Cart::instance('main')->count() }}
                @endif
            @else
                @if(Cart::instance('main')->total() > 0)
                    В корзине на сумму <span class="total_cart text uk-text-nowrap">{!! Cart::instance('main')->total() !!}</span> р.
                @else
                    В корзине товаров: {{ Cart::instance('main')->count() }}
                @endif
            @endif
            </span>

            @if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount') && $discountsShare['profit'] > 0)
                <span class="moduleCart-discount_row" @if($discountsShare['profit'] < 1) style="display: none" @endif>скидка: <span class="total_discount_cart uk-text-nowrap">{{ $discountsShare['profit'] }}</span> р.</span>
            @endif
        </a>
    </p>
</div>