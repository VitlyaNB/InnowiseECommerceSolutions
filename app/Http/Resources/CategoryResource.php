<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    /**
     * Преобразование ресурса в массив.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // Вызываем метод для формирования правильной ссылки, как в ProductResource
            'image_path' => $this->resolveImageUrl($this->image_path),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Формирует полный URL для изображения из хранилища MinIO.
     */
    private function resolveImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Если в БД уже лежит полная ссылка (начинается с http), возвращаем её как есть
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Иначе строим полный URL через диск s3 (который настроен на MinIO)
        return Storage::disk(config('filesystems.media_disk', 's3'))->url($path);
    }
}
