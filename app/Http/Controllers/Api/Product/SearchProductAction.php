<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Elastic\Elasticsearch\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SearchProductAction extends Controller
{
    #[OA\Get(
        path: '/api/products/search',
        summary: 'Search products using Elasticsearch',
        description: 'Full-text search with filters, sorting and aggregation. Returns paginated results plus filter metadata (available categories, min/max prices).',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'query', in: 'query', required: false, description: 'Search term', schema: new OA\Schema(type: 'string', example: 'smartphone')),
            new OA\Parameter(name: 'categories', in: 'query', required: false, description: 'Array of category IDs to filter by', schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer'))),
            new OA\Parameter(name: 'min_price', in: 'query', required: false, description: 'Minimum price filter', schema: new OA\Schema(type: 'number', format: 'float', example: 10.0)),
            new OA\Parameter(name: 'max_price', in: 'query', required: false, description: 'Maximum price filter', schema: new OA\Schema(type: 'number', format: 'float', example: 999.99)),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Sort order',
                schema: new OA\Schema(type: 'string', enum: ['created_at_desc', 'price_asc', 'price_desc', 'name_asc'], default: 'created_at_desc')
            ),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Results per page', schema: new OA\Schema(type: 'integer', default: 12)),
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Search results',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductResource')),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer', example: 42),
                                new OA\Property(property: 'per_page', type: 'integer', example: 12),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'number', example: 4),
                            ]
                        ),
                        new OA\Property(
                            property: 'filters',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'categories', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'min_price', type: 'number', example: 5.0),
                                new OA\Property(property: 'max_price', type: 'number', example: 1999.0),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->string('query')->value();
        /** @var mixed $categoriesInput */
        $categoriesInput = $request->input('categories', []);
        $categoryIds     = is_array($categoriesInput) ? $categoriesInput : [];

        $minPrice = $request->has('min_price') ? $request->float('min_price') : null;
        $maxPrice = $request->has('max_price') ? $request->float('max_price') : null;
        $sort     = $request->string('sort', 'created_at_desc')->value();
        $perPage  = $request->integer('per_page', 12);
        $page     = $request->integer('page', 1);

        $params = [
            'index' => 'products_index',
            'body'  => [
                'from'  => ($page - 1) * $perPage,
                'size'  => $perPage,
                'query' => [
                    'bool' => [
                        'must'   => [],
                        'filter' => [
                            ['term' => ['is_active' => true]]
                        ]
                    ]
                ],
                'aggs'  => [
                    'categories' => [
                        'terms' => ['field' => 'category_id', 'size' => 50]
                    ],
                    'min_price'  => ['min' => ['field' => 'price']],
                    'max_price'  => ['max' => ['field' => 'price']]
                ]
            ]
        ];

        // Search query
        if (!empty($query)) {
            $params['body']['query']['bool']['must'][] = [
                'multi_match' => [
                    'query'     => $query,
                    'fields'    => ['name^3', 'description', 'category_name'],
                    'fuzziness' => 'AUTO'
                ]
            ];
        }

        // Category filter
        if (!empty($categoryIds)) {
            $categoryIds                                   = array_map(fn ($v) => (int) (is_scalar($v) ? $v : 0), $categoryIds);
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

        try {
            /** @var \Elastic\Elasticsearch\Response\Elasticsearch $response */
            $response = $this->elasticsearch->search($params);
            /** @var array<string, mixed> $results */
            $results = $response->asArray();
        } catch (\Exception $e) {
            // Log error and fallback to DB search
            \Illuminate\Support\Facades\Log::warning("Elasticsearch search failed: " . $e->getMessage());
            return $this->fallbackSearch($query, $categoryIds, $minPrice, $maxPrice, $sort, $perPage, $page);
        }

        /** @var array{total: array{value: int}, hits: array<int, array{_id: string|int}>} $hits */
        $hits = $results['hits'];

        $hitItems = $hits['hits'];

        $ids = array_map(fn ($hit) => (int) $hit['_id'], $hitItems);

        if (empty($ids)) {
            $products = collect();
        } else {
            $fetchedProducts = Product::whereIn('id', $ids)
                ->with(['images', 'category'])
                ->get();

            // Sort by the order of IDs returned by Elasticsearch
            $idToIndex = array_flip($ids);
            $products  = $fetchedProducts->sortBy(fn ($product) => $idToIndex[$product->id])->values();
        }

        /** @var array<string, array{buckets?: array<int, array{key: int|string, doc_count: int|string}>, value: float|int}> $aggregations */
        $aggregations = $results['aggregations'] ?? [];

        $total = $hits['total']['value'];

        return response()->json([
            'data'    => ProductResource::collection($products),
            'meta'    => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => ceil($total / $perPage),
            ],
            'filters' => [
                'categories' => collect($aggregations['categories']['buckets'] ?? [])
                    ->map(fn ($b) => [
                        'id'    => (int) $b['key'],
                        'count' => (int) $b['doc_count']
                    ]),
                'min_price'  => $aggregations['min_price']['value'],
                'max_price'  => $aggregations['max_price']['value'],
            ]
        ]);
    }

    private function fallbackSearch(string $query, array $categoryIds, ?float $minPrice, ?float $maxPrice, string $sort, int $perPage, int $page): JsonResponse
    {
        $dbQuery = Product::query()->where('is_active', true)->with(['images', 'category']);

        if (!empty($query)) {
            $dbQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        if (!empty($categoryIds)) {
            $dbQuery->whereIn('category_id', $categoryIds);
        }

        if ($minPrice !== null) {
            $dbQuery->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $dbQuery->where('price', '<=', $maxPrice);
        }

        switch ($sort) {
            case 'price_asc':
                $dbQuery->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $dbQuery->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $dbQuery->orderBy('name', 'asc');
                break;
            default:
                $dbQuery->orderBy('created_at', 'desc');
        }

        $paginator = $dbQuery->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data'    => ProductResource::collection($paginator->items()),
            'meta'    => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
            'filters' => [
                'categories' => [], // DB fallback doesn't easily provide aggs without extra queries
                'min_price'  => Product::where('is_active', true)->min('price') ?? 0,
                'max_price'  => Product::where('is_active', true)->max('price') ?? 0,
            ]
        ]);
    }

    public function __construct(
        private readonly Client $elasticsearch
    ) {}
}
