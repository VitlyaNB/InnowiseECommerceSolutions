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
            'image_path' => $this->image_path
                ? Storage::disk(config('filesystems.media_disk', 's3'))->url($this->image_path)
                : null,
            'created_at' => $this->created_at,
        ];
    }
}
