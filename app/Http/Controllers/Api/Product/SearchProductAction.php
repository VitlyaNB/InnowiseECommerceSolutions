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

            // 3. Формируем "сырой" запрос к Elastic
            $response = $client->search([
                'index' => (new Product)->searchableAs(), // Имя индекса (обычно shop_products)
                'body'  => [
                    'query' => [
                        'multi_match' => [
                            'query'     => $query,
                            'fields'    => ['name^5', 'description'], // Имя важнее описания в 5 раз
                            'fuzziness' => 'AUTO', // Включаем поддержку опечаток
                            'operator'  => 'and',
                        ],
                    ],
                    'from' => $from,
                    'size' => $perPage,
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

            return ProductResource::collection($paginator);

        } catch (\Exception $e) {
            // Логируем ошибку, чтобы видеть детали в docker logs
            Log::error("Elasticsearch Direct Search Error: " . $e->getMessage());

            // Возвращаем пустой результат вместо 500 ошибки, чтобы сайт не падал
            return ProductResource::collection([]);
        }
    }
}
