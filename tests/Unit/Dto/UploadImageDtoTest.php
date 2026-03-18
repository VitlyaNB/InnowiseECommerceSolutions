<?php

namespace Tests\Unit\Dto;

use App\Dto\UploadImageDto;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadImageDtoTest extends TestCase
{
    public function test_upload_image_dto_maps_values(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $dto = new UploadImageDto(file: $file, folder: 'avatars', disk: 'public');

        $this->assertSame('avatars', $dto->folder);
        $this->assertSame('public', $dto->disk);
    }
}
