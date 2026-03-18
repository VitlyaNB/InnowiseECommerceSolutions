<?php

namespace App\Repositories;

use App\Dto\PaginatedResultDto;
use App\Dto\ProductFiltersDto;
use App\Dto\ProductIdsQueryDto;
use App\Dto\ProductDto;
use App\Dto\RandomProductsQueryDto;
use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(ProductFiltersDto $filters, int $perPage = 15): PaginatedResultDto
    {
        $query = Product::query()->with(['images', 'category']);

        if ($filters->categoryId !== null) {
            $query->where('category_id', $filters->categoryId);
        }

        if ($filters->isActive !== null) {
            $query->where('is_active', $filters->isActive);
        }

        if ($filters->priceMin !== null) {
            $query->where('price', '>=', $filters->priceMin);
        }

        if ($filters->priceMax !== null) {
            $query->where('price', '<=', $filters->priceMax);
        }

        if ($filters->inStock === true) {
            $query->where('quantity', '>', 0);
        }

        $paginator = $query->latest()->paginate($perPage);

        $items = $paginator
            ->getCollection()
            ->map(fn (Product $product): ProductDto => $this->mapToDto($product))
            ->all();

        return new PaginatedResultDto(
            items: $items,
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
        );
    }

    /** @return array<int, ProductDto> */
    public function getByCategory(int $categoryId): array
    {
        return Product::query()
            ->with(['images', 'category'])
            ->where('category_id', $categoryId)
            ->get()
            ->map(fn(Product $product) => $this->mapToDto($product))
            ->toArray();
    }

    public function findById(int $id): ?ProductDto
    {
        /** @var Product|null $product */
        $product = Product::query()->with(['images', 'category'])->find($id);

        return $product ? $this->mapToDto($product) : null;
    }

    public function create(ProductDto $data): ProductDto
    {
        /** @var Product $product */
        $product = Product::create($data->toArray());

        return $this->mapToDto($product->load(['images', 'category']));
    }

    public function update(int $id, ProductDto $data): bool
    {
        /** @var Product|null $product */
        $product = Product::query()->find($id);

        if (!$product) {
            return false;
        }

        return $product->update($data->toArray());
    }

    public function delete(int $id): bool
    {
        /** @var Product|null $product */
        $product = Product::query()->find($id);

        if (!$product) {
            return false;
        }

        return (bool) $product->delete();
    }

    public function incrementViewCount(int $id): void
    {
    }

    public function saveImage(int $productId, string $imagePath): void
    {
        ProductImage::create([
            'product_id' => $productId,
            'image_path' => $imagePath,
        ]);
    }

    public function deleteImages(int $productId): void
    {
        ProductImage::query()->where('product_id', $productId)->delete();
    }

    /** @return array<int, ProductDto> */
    public function getByIds(ProductIdsQueryDto $query): array
    {
        $ids = $query->ids;
        if ($ids === []) {
            return [];
        }

        $products = Product::query()
            ->with(['images', 'category'])
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->map(fn (Product $product) => $this->mapToDto($product));

        /** @var array<int, ProductDto> $productDtos */
        $productDtos = $products->all();

        if (!$query->keepOrder) {
            return $productDtos;
        }

        $mapped = [];
        foreach ($productDtos as $dto) {
            $mapped[$dto->id] = $dto;
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($mapped[$id])) {
                $ordered[] = $mapped[$id];
            }
        }

        return $ordered;
    }

    /** @return array<int, int> */
    public function getAlsoBoughtProductIds(int $productId, int $limit): array
    {
        /** @var array<int, int> $ids */
        $ids = DB::table('order_items as oi')
            ->join('order_items as oi2', 'oi.order_id', '=', 'oi2.order_id')
            ->where('oi.product_id', $productId)
            ->where('oi2.product_id', '!=', $productId)
            ->select('oi2.product_id', DB::raw('COUNT(*) as freq'))
            ->groupBy('oi2.product_id')
            ->orderByDesc('freq')
            ->limit($limit)
            ->pluck('oi2.product_id')
            ->all();

        return $ids;
    }

    /** @return array<int, int> */
    public function getSimilarFallbackIds(int $categoryId, int $excludedProductId, int $limit): array
    {
        /** @var array<int, int> $ids */
        $ids = Product::query()
            ->where('category_id', $categoryId)
            ->where('id', '!=', $excludedProductId)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->pluck('id')
            ->all();

        return $ids;
    }

    /** @return array<int, int> */
    public function getRandomActiveProductIds(RandomProductsQueryDto $queryDto): array
    {
        $query = Product::query()
            ->where('is_active', true);

        if ($queryDto->excludedIds !== []) {
            $query->whereNotIn('id', $queryDto->excludedIds);
        }

        /** @var array<int, int> $ids */
        $ids = $query
            ->inRandomOrder()
            ->limit($queryDto->limit)
            ->pluck('id')
            ->all();

        return $ids;
    }

    public function decrementStock(int $productId, int $quantity): bool
    {
        $affected = Product::query()
            ->where('id', $productId)
            ->where('quantity', '>=', $quantity)
            ->decrement('quantity', $quantity);

        return $affected > 0;
    }

    private function mapToDto(Product $product): ProductDto
    {
        return new ProductDto(
            id: $product->id,
            name: $product->name,
            description: $product->description,
            price: (float) $product->price,
            oldPrice: $product->old_price ? (float) $product->old_price : null,
            quantity: (int) $product->quantity,
            categoryId: (int) $product->category_id,
            isActive: (bool) $product->is_active,
            images: $product->images ? $product->images->pluck('image_path')->toArray() : [],
        );
    }
}
