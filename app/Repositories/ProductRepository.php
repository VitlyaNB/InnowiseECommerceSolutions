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

final class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(ProductFiltersDto $filters, int $perPage = 15): PaginatedResultDto
    {
        $query = Product::query()->with(['images', 'category']);

        $paginator = (new Product)->scopeFilter($query, $filters)
            ->latest()
            ->paginate($perPage);

        /** @var array<int, ProductDto> $items */
        $items = [];
        foreach ($paginator->getCollection() as $product) {
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

    public function deleteOrderItemsByProductId(int $productId): void
    {
        OrderItem::query()->where('product_id', $productId)->delete();
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

        /** @var \Illuminate\Support\Collection<int, ProductDto> $productsById */
        $productsById = $collection->mapWithKeys(fn (Product $product): array => [
            (int) $product->id => $this->mapToDto($product),
        ]);

        if (! $query->keepOrder) {
            /** @var array<int, ProductDto> $products */
            $products = array_values($productsById->all());

            return $products;
        }

        /** @var array<int, ProductDto> $ordered */
        $ordered = collect($ids)
            ->map(fn (int $id): ?ProductDto => $productsById->get($id))
            ->filter()
            ->values()
            ->all();

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
            ->select('oi2.product_id')
            ->selectRaw('COUNT(*) as freq')
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
            quantity: (int) $product->quantity,
            categoryId: (int) $product->category_id,
            categoryName: $product->category !== null ? (string) $product->category->name : null,
            isActive: (bool) $product->is_active,
            images: $imagePaths,
        );
    }
}
