# Laravel Larrock CMS :: Cart Component

---

#### Depends
- fanamurov/larrock-core
- fanamurov/larrock-catalog
- fanamurov/larrock-category
- fanamurov/larrock-users
- gloudemans/shoppingcart
- artem328/laravel-yandex-kassa

## INSTALL

1. Install larrock-core, larrock-catalog, larrock-category
2. Install larrock-cart
  ```sh
  composer require fanamurov/larrock-cart
  ```

4. Add the ServiceProvider to the providers array in app/config/app.php
  ```
  //LARROCK COMPONENT Cart DEPENDS
  \Larrock\ComponentCart\LarrockComponentCartServiceProvider::class,
  //https://packagist.org/packages/gloudemans/shoppingcart :: Корзина для каталога
  Gloudemans\Shoppingcart\ShoppingcartServiceProvider::class,
  //https://github.com/artem328/laravel-yandex-kassa
  Artem328\LaravelYandexKassa\YandexKassaServiceProvider::class,
  ```

  aliases
  ```
  'Cart'          => Gloudemans\Shoppingcart\Facades\Cart::class,
  'YandexKassa' => Artem328\LaravelYandexKassa\Facades\YandexKassa::class,
  ```

5. Publish views, migrations etc.
  ```sh
  $ php artisan vendor:publish
  ```
  Or
  ```sh
  $ php artisan vendor:publish --provider="Larrock\ComponentCart\LarrockComponentCartServiceProvider"
  $ php artisan vendor:publish --provider="Gloudemans\Shoppingcart\ShoppingcartServiceProvider"
  $ php artisan vendor:publish --provider="Artem328\LaravelYandexKassa\YandexKassaServiceProvider"
  ```
       
6. Run artisan command:
  ```sh
  $ php artisan larrock:check
  ```
  And follow the tips for setting third-party dependencies
  
  
7. Run migrations
  ```sh
  $ php artisan migrate
  ```

##START
http://yousite/admin/cart