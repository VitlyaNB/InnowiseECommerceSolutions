<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class SearchProductAction extends Controller
{
    public function __invoke(Request $request)
    {
        $query = $request->input('query') ?? $request->input('q');

        if (!$query) {
            return ProductResource::collection([]);
        }

        try {
            // Используем стандартный поиск Scout, но добавляем параметры через callback
            $products = Product::search($query, function ($client, $body) use ($query) {
                // Настраиваем multi_match с поддержкой опечаток (fuzziness)
                $body['query'] = [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => ['name^5', 'description'],
                        'fuzziness' => 'AUTO',
                    ],
                ];

                return $client->search([
                    'index' => (new Product())->searchableAs(),
                    'body'  => $body,
                ]);
            })
                ->query(function ($builder) {
                    $builder->with(['category', 'images'])
                        ->where('is_active', true);
                })
                ->paginate(12);

            return ProductResource::collection($products);

        } catch (Exception $e) {
            // Логируем точную ошибку в storage/logs/laravel.log
            Log::error("Elasticsearch Search Error: " . $e->getMessage());

            return response()->json([
                'error' => 'Ошибка поискового сервиса',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
