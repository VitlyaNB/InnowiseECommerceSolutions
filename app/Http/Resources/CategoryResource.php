<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // Вызываем метод для формирования правильной ссылки
            'image_path' => $this->resolveImageUrl($this->image_path),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if (!$path) return null;

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return Storage::disk(config('filesystems.media_disk', 's3'))->url($path);
    }
}
