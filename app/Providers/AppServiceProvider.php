<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\CartItemRepository;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Repositories\ReviewRepository;
// Импорты для Elasticsearch
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Psr\Http\Client\ClientInterface;
use GuzzleHttp\Client as GuzzleClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 1. Привязываем PSR-интерфейс клиента к Guzzle
        $this->app->bind(ClientInterface::class, function () {
            return new GuzzleClient();
        });

        // 2. Правильная регистрация клиента Elasticsearch для Matchish/Scout
        $this->app->singleton(Client::class, function () {
            $host = config('elasticsearch.hosts', ['http://elasticsearch:9200']);

            return ClientBuilder::create()
                ->setHosts($host)
                ->build();
        });

        // Регистрация репозиториев
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(CartItemRepositoryInterface::class, CartItemRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\ProductImage::observe(\App\Observers\ProductImageObserver::class);
    }
}
