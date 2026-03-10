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
            'image_path' => $this->resolveImageUrl($this->image_path),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    //форматирование УРЛ для Минио
    private function resolveImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Если в БД уже лежит полная ссылка возвращаем её как есть
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // если нет то строим полный URL через диск s3
        return Storage::disk(config('filesystems.media_disk', 's3'))->url($path);
    }
}
