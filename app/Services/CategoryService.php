<?php

namespace App\Services;

use App\Dto\CategoryDto;
use App\Dto\UploadImageDto;
use App\Infrastructure\Interfaces\TransactionManagerInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\Interfaces\FileServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private ProductRepositoryInterface $productRepository,
        private FileServiceInterface $fileService,
        private TransactionManagerInterface $transactionManager
    ) {}

    /** @return array<int, CategoryDto> */
    public function getAllCategories(): array
    {
        return $this->categoryRepository->getAll();
    }

    public function getCategoryById(int $id): CategoryDto
    {
        $category = $this->categoryRepository->findById($id);

        if (! $category) {
            throw new ModelNotFoundException("Category with ID {$id} not found.");
        }

        return $category;
    }

    public function createCategory(CategoryDto $data): CategoryDto
    {
        return $this->transactionManager->transaction(function () use ($data): CategoryDto {
            $imagePath = null;
            if ($data->image) {
                /** @var string $disk */
                $disk = config('filesystems.media_disk', 's3');
                $imagePath = $this->fileService->upload(new UploadImageDto(
                    file: $data->image,
                    folder: 'categories',
                    disk: $disk
                ));
            }

            $dtoToSave = new CategoryDto(
                name: $data->name,
                imagePath: $imagePath
            );

            return $this->categoryRepository->create($dtoToSave);
        });
    }

    public function updateCategory(int $id, CategoryDto $data): ?CategoryDto
    {
        $category = $this->getCategoryById($id);

        $imagePath = $category->imagePath;

        if ($data->image) {
            if ($category->imagePath) {
                $this->fileService->delete($category->imagePath);
            }

            /** @var string $disk */
            $disk = config('filesystems.media_disk', 's3');
            $imagePath = $this->fileService->upload(new UploadImageDto(
                file: $data->image,
                folder: 'categories',
                disk: $disk
            ));
        }

        $dtoToUpdate = new CategoryDto(
            name: $data->name,
            imagePath: $imagePath
        );

        $this->categoryRepository->update($id, $dtoToUpdate);

        return $this->categoryRepository->findById($id);
    }

    public function deleteCategory(int $id): bool
    {
        $category = $this->getCategoryById($id);

        $products = $this->productRepository->getByCategory($id);
        foreach ($products as $product) {
            $this->productRepository->delete((int) $product->id);
        }

        if ($category->imagePath) {
            $this->fileService->delete($category->imagePath);
        }

        return $this->categoryRepository->delete($id);
    }
}
