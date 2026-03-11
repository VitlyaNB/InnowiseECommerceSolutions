<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Elastic\Elasticsearch\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchProductAction extends Controller
{
    public function __construct(
        private readonly Client $elasticsearch
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->string('query')->value();
        /** @var mixed $categoriesInput */
        $categoriesInput = $request->input('categories', []);
        $categoryIds = is_array($categoriesInput) ? $categoriesInput : [];
        
        $minPrice = $request->has('min_price') ? $request->float('min_price') : null;
        $maxPrice = $request->has('max_price') ? $request->float('max_price') : null;
        $sort = $request->string('sort', 'created_at_desc')->value();
        $perPage = $request->integer('per_page', 12);
        $page = $request->integer('page', 1);

        $params = [
            'index' => 'products_index',
            'body'  => [
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
                'query' => [
                    'bool' => [
                        'must' => [],
                        'filter' => [
                            ['term' => ['is_active' => true]]
                        ]
                    ]
                ],
                'aggs' => [
                    'categories' => [
                        'terms' => ['field' => 'category_id', 'size' => 50]
                    ],
                    'min_price' => ['min' => ['field' => 'price']],
                    'max_price' => ['max' => ['field' => 'price']]
                ]
            ]
        ];

        // Search query
        if (!empty($query)) {
            $params['body']['query']['bool']['must'][] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['name^3', 'description', 'category_name'],
                    'fuzziness' => 'AUTO'
                ]
            ];
        }

        // Category filter
        if (!empty($categoryIds)) {
            $categoryIds = array_map(fn($v) => (int) (is_scalar($v) ? $v : 0), $categoryIds);
            $params['body']['query']['bool']['filter'][] = [
                'terms' => ['category_id' => $categoryIds]
            ];
        }

        // Price range
        if ($minPrice !== null || $maxPrice !== null) {
            $range = ['price' => []];
            if ($minPrice !== null) $range['price']['gte'] = $minPrice;
            if ($maxPrice !== null) $range['price']['lte'] = $maxPrice;
            $params['body']['query']['bool']['filter'][] = ['range' => $range];
        }

        // Sorting
        switch ($sort) {
            case 'price_asc':
                $params['body']['sort'] = [['price' => 'asc']];
                break;
            case 'price_desc':
                $params['body']['sort'] = [['price' => 'desc']];
                break;
            case 'name_asc':
                $params['body']['sort'] = [['name.keyword' => 'asc']];
                break;
            default:
                $params['body']['sort'] = [['created_at' => 'desc']];
        }

        /** @var \Elastic\Elasticsearch\Response\Elasticsearch $response */
        $response = $this->elasticsearch->search($params);
        /** @var array<string, mixed> $results */
        $results = $response->asArray();

        /** @var array{total: array{value: int}, hits: array<int, array{_id: string|int}>} $hits */
        $hits = $results['hits'];
        
        $hitItems = $hits['hits'];
        
        $ids = array_map(fn($hit) => (int) $hit['_id'], $hitItems);

        if (empty($ids)) {
            $products = collect();
        } else {
            $fetchedProducts = Product::whereIn('id', $ids)
                ->with(['images', 'category'])
                ->get();

            // Sort by the order of IDs returned by Elasticsearch
            $idToIndex = array_flip($ids);
            $products = $fetchedProducts->sortBy(fn($product) => $idToIndex[$product->id])->values();
        }

        /** @var array<string, array{buckets?: array<int, array{key: int|string, doc_count: int|string}>, value: float|int}> $aggregations */
        $aggregations = $results['aggregations'] ?? [];

        $total = $hits['total']['value'];

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
            ],
            'filters' => [
                'categories' => collect($aggregations['categories']['buckets'] ?? [])
                    ->map(fn($b) => [
                        'id' => (int) $b['key'],
                        'count' => (int) $b['doc_count']
                    ]),
                'min_price' => $aggregations['min_price']['value'],
                'max_price' => $aggregations['max_price']['value'],
            ]
        ]);
    }
}
