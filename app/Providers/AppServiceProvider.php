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
use App\Repositories\Interfaces\ProductViewRepositoryInterface;
use App\Repositories\ProductViewRepository;

// Импорты для Elasticsearch
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Psr\Http\Client\ClientInterface;
use GuzzleHttp\Client as GuzzleClient;
use Laravel\Scout\EngineManager;
// ВНИМАНИЕ: Здесь была ошибка. Правильное название класса с большой буквой 'S'
use Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // привязка PSR-интерфейса клиента к Guzzle
        $this->app->bind(ClientInterface::class, function () {
            return new GuzzleClient();
        });

        $this->app->singleton(Client::class, function () {
            // Берем хосты из конфига или ставим дефолт
            /** @var array<int, string> $hosts */
            $hosts = (array) config('scout.elasticsearch.hosts', ['http://elasticsearch:9200']);

            return ClientBuilder::create()
                ->setHosts($hosts)
                ->build();
        });

        // регистрация репозиториев
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(CartItemRepositoryInterface::class, CartItemRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->bind(ProductViewRepositoryInterface::class, ProductViewRepository::class);
    }

    public function boot(): void
    {
        \App\Models\ProductImage::observe(\App\Observers\ProductImageObserver::class);
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);

        resolve(EngineManager::class)->extend('elasticsearch', function ($app) {
            return new ElasticSearchEngine(
                $app->make(Client::class)
            );
        });
    }
}
