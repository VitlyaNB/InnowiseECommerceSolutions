<?php

namespace App\Services;

use App\Dto\ProductDto;
use App\Infrastructure\Interfaces\ElasticsearchClientInterface;
use App\Services\Interfaces\RecommendationSearcherInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class RecommendationSearcher implements RecommendationSearcherInterface
{
    public function __construct(
        private ElasticsearchClientInterface $elasticsearchClient
    ) {}

    /** @return array<int, int> */
    public function searchSimilarIds(ProductDto $product, int $limit): array
    {
        try {
            /** @var array<string, mixed> $results */
            $results = $this->elasticsearchClient->search([
                'index' => 'products_index',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'multi_match' => [
                                        'query' => $product->name,
                                        'fields' => ['name^5', 'description'],
                                        'fuzziness' => 'AUTO',
                                    ],
                                ],
                            ],
                            'filter' => [
                                ['term' => ['category_id' => (int) $product->categoryId]],
                            ],
                            'must_not' => [
                                ['term' => ['id' => (int) $product->id]],
                            ],
                        ],
                    ],
                    'size' => $limit,
                ],
            ]);

            /** @var array<int, array<string, mixed>> $hits */
            $hits = [];
            $hitsContainer = $results['hits'] ?? null;
            if (is_array($hitsContainer) && isset($hitsContainer['hits']) && is_array($hitsContainer['hits'])) {
                $hits = $hitsContainer['hits'];
            }

            /** @var array<int, int> $ids */
            $ids = [];
            foreach ($hits as $hit) {
                if (! is_array($hit)) {
                    continue;
                }

                $hitId = $hit['_id'] ?? null;
                if (is_numeric($hitId)) {
                    $ids[] = (int) $hitId;
                }
            }

            return $ids;
        } catch (Throwable $exception) {
            Log::warning('Recommendation Elasticsearch error: '.$exception->getMessage());

            return [];
        }
    }
}
