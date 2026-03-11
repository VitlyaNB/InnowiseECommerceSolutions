<?php

namespace Tests\Unit\DTO;

use App\DTO\UploadImageDTO;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadImageDTOTest extends TestCase
{
    public function test_upload_image_dto_instantiation()
    {
        $file = UploadedFile::fake()->image('avatar.png');
        $dto = new UploadImageDTO($file, 'avatars', 'public');

        $this->assertInstanceOf(UploadImageDTO::class, $dto);
        $this->assertEquals($file, $dto->file);
        $this->assertEquals('avatars', $dto->folder);
        $this->assertEquals('public', $dto->disk);
    }
}
