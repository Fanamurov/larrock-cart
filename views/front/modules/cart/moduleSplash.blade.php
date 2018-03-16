<div class="moduleCart">
    <p class="cart-empty @if(Cart::instance('main')->count() > 0) uk-hidden @endif"><i class="uk-icon-shopping-cart"></i> Корзина пуста</p>
    <p class="cart-show @if(Cart::instance('main')->count() < 1) uk-hidden @endif">
        <a href="/cart">
            <span class="uk-icon-shopping-cart"></span>
            <span class="cart-text">
            @if(Cart::instance('main')->total() > 0)
                @if(file_exists(base_path(). '/vendor/fanamurov/larrock-discount'))
                    В корзине на сумму <span class="total_cart text uk-text-nowrap">{{ $discountsShare->total }}</span> р.
                @else
                    В корзине на сумму <span class="total_cart text uk-text-nowrap">{!! Cart::instance('main')->total() !!}</span> р.
                @endif
            @else
                В корзине товаров: {{ Cart::instance('main')->count() }}
            @endif
            </span>
        </a>
    </p>
</div>