<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Elastic\Elasticsearch\Client;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class SearchProductAction extends Controller
{
    /**
     * Поиск товаров через прямой запрос к Elasticsearch.
     */
    public function __invoke(Request $request)
    {
        $query = $request->input('query') ?? $request->input('q');

        // Если запрос пустой, возвращаем пустой список
        if (!$query) {
            return ProductResource::collection([]);
        }

        try {
            // 1. Получаем клиент Elasticsearch из контейнера
            $client = app(Client::class);

            // 2. Настройки пагинации
            $page = (int) $request->input('page', 1);
            $perPage = 12;
            $from = ($page - 1) * $perPage;

            $categoryIds = $request->input('category_id');
            $priceMin = $request->input('price_min');
            $priceMax = $request->input('price_max');
            $inStock = $request->boolean('in_stock', false);

            $filters = [];
            if (!empty($categoryIds)) {
                $ids = is_array($categoryIds) ? $categoryIds : explode(',', (string) $categoryIds);
                $filters[] = ['terms' => ['category_id' => array_map('intval', $ids)]];
            }
            if ($priceMin !== null || $priceMax !== null) {
                $range = [];
                if ($priceMin !== null) {
                    $range['gte'] = (float) $priceMin;
                }
                if ($priceMax !== null) {
                    $range['lte'] = (float) $priceMax;
                }
                $filters[] = ['range' => ['price' => $range]];
            }
            if ($inStock) {
                $filters[] = ['range' => ['quantity' => ['gt' => 0]]];
            }

            // 3. Формируем "сырой" запрос к Elastic
            $response = $client->search([
                'index' => (new Product)->searchableAs(), // Имя индекса (обычно shop_products)
                'body'  => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'multi_match' => [
                                        'query'     => $query,
                                        'fields'    => ['name^5', 'description'], // Имя важнее описания в 5 раз
                                        'fuzziness' => 'AUTO', // Включаем поддержку опечаток
                                        'operator'  => 'and',
                                    ],
                                ],
                            ],
                            'filter' => $filters,
                        ],
                    ],
                    'from' => $from,
                    'size' => $perPage,
                    'aggs' => [
                        'categories' => [
                            'terms' => [
                                'field' => 'category_id',
                                'size' => 20,
                            ],
                        ],
                        'price_stats' => [
                            'stats' => [
                                'field' => 'price',
                            ],
                        ],
                    ],
                ],
            ]);

            // 4. Обрабатываем ответ
            $hits = $response['hits']['hits'];
            $total = $response['hits']['total']['value'];

            // Собираем ID найденных товаров
            $ids = array_column($hits, '_id');

            if (empty($ids)) {
                return ProductResource::collection([]);
            }

            // 5. Загружаем модели из базы данных (чтобы были картинки, категории и т.д.)
            $products = Product::with(['category', 'images'])
                ->whereIn('id', $ids)
                ->where('is_active', true)
                ->get();

            // 6. Сортируем модели в том порядке, в котором их вернул Elastic (по релевантности)
            $sortedProducts = $products->sortBy(function ($model) use ($ids) {
                return array_search($model->id, $ids);
            })->values();

            // 7. Создаем пагинатор вручную
            $paginator = new LengthAwarePaginator(
                $sortedProducts,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $facets = [
                'categories' => collect($response['aggregations']['categories']['buckets'] ?? [])
                    ->map(fn ($bucket) => [
                        'id' => (int) $bucket['key'],
                        'count' => (int) $bucket['doc_count'],
                    ])
                    ->values(),
                'price' => [
                    'min' => (float) ($response['aggregations']['price_stats']['min'] ?? 0),
                    'max' => (float) ($response['aggregations']['price_stats']['max'] ?? 0),
                ],
            ];

            return ProductResource::collection($paginator)
                ->additional(['meta' => ['facets' => $facets]]);

        } catch (\Exception $e) {
            // Логируем ошибку, чтобы видеть детали в docker logs
            Log::error("Elasticsearch Direct Search Error: " . $e->getMessage());

            // Возвращаем пустой результат вместо 500 ошибки, чтобы сайт не падал
            return ProductResource::collection([]);
        }
    }
}
