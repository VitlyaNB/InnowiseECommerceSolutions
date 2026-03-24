<?php

namespace App\Repositories;

use App\Dto\PaginatedResultDto;
use App\Dto\ProductDto;
use App\Dto\ProductFiltersDto;
use App\Dto\ProductIdsQueryDto;
use App\Dto\RandomProductsQueryDto;
use App\Models\OrderItem;
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

        $collection = $paginator->getCollection();
        /** @var array<int, ProductDto> $items */
        $items = [];
        foreach ($collection as $product) {
            /** @var Product $product */
            $items[] = $this->mapToDto($product);
        }

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
        $collection = Product::query()
            ->with(['images', 'category'])
            ->where('category_id', $categoryId)
            ->get();

        /** @var array<int, ProductDto> $result */
        $result = [];
        foreach ($collection as $product) {
            /** @var Product $product */
            $result[] = $this->mapToDto($product);
        }

        return $result;
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
        $product = Product::query()->create($data->toArray());

        return $this->mapToDto($product->load(['images', 'category']));
    }

    public function update(int $id, ProductDto $data): bool
    {
        /** @var Product|null $product */
        $product = Product::query()->find($id);

        if (! $product) {
            return false;
        }

        return $product->update($data->toArray());
    }

    public function delete(int $id): bool
    {
        /** @var Product|null $product */
        $product = Product::query()->find($id);

        if (! $product) {
            return false;
        }

        return (bool) $product->delete();
    }

    public function saveImage(int $productId, string $imagePath): void
    {
        ProductImage::query()->create([
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

        $collection = Product::query()
            ->with(['images', 'category'])
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get();

        /** @var array<int, ProductDto> $products */
        $products = [];
        foreach ($collection as $product) {
            /** @var Product $product */
            $products[] = $this->mapToDto($product);
        }

        if (! $query->keepOrder) {
            return $products;
        }

        $mapped = [];
        foreach ($products as $dto) {
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
        $ids = OrderItem::query()
            ->from('order_items as oi')
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
    public function getRandomActiveProductIds(RandomProductsQueryDto $query): array
    {
        $builder = Product::query()
            ->where('is_active', true);

        if ($query->excludedIds !== []) {
            $builder->whereNotIn('id', $query->excludedIds);
        }

        /** @var array<int, int> $ids */
        $ids = $builder
            ->inRandomOrder()
            ->limit($query->limit)
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
        /** @var array<int, string> $imagePaths */
        $imagePaths = $product->images ? $product->images->pluck('image_path')->all() : [];

        return new ProductDto(
            id: $product->id,
            name: $product->name,
            description: $product->description,
            price: (float) $product->price,
            oldPrice: $product->old_price !== null ? (float) $product->old_price : null,
            quantity: (int) $product->quantity,
            categoryId: (int) $product->category_id,
            isActive: (bool) $product->is_active,
            images: $imagePaths,
        );
    }
}
