<?php

namespace App\Providers;

use App\Infrastructure\Interfaces\ElasticsearchClientInterface;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Infrastructure\Persistence\ElasticsearchClientWrapper;
use App\Infrastructure\Persistence\EloquentTransactionManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Observers\CategoryObserver;
use App\Observers\ProductImageObserver;
use App\Observers\ProductObserver;
use App\Repositories\CartItemRepository;
use App\Repositories\ChatRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ProductViewRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProductViewRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\UserRepository;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine;
use Psr\Http\Client\ClientInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientInterface::class, function () {
            return new GuzzleClient();
        });

        $this->app->singleton(Client::class, function () {
            /** @var array<int, string> $hosts */
            $hosts = (array) config('scout.elasticsearch.hosts', ['http://elasticsearch:9200']);

            return ClientBuilder::create()
                ->setHosts($hosts)
                ->build();
        });

        $this->app->singleton(ElasticsearchClientInterface::class, ElasticsearchClientWrapper::class);
        $this->app->singleton(TransactionManagerInterface::class, EloquentTransactionManager::class);

        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(CartItemRepositoryInterface::class, CartItemRepository::class);
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->bind(ProductViewRepositoryInterface::class, ProductViewRepository::class);
    }

    public function boot(): void
    {
        ProductImage::observe(ProductImageObserver::class);
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);

        resolve(EngineManager::class)->extend('elasticsearch', function ($app) {
            return new ElasticSearchEngine(
                $app->make(Client::class)
            );
        });
    }
}
