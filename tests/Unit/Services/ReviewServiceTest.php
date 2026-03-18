<?php

namespace Tests\Unit\Services;

use App\Dto\ReviewDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\ReviewService;
use Mockery;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    public function test_it_creates_review(): void
    {
        $reviewRepository = Mockery::mock(ReviewRepositoryInterface::class);
        $reviewService = new ReviewService($reviewRepository);
        $dto = new ReviewDto(productId: 12, rating: 5, comment: 'Excellent');

        $reviewRepository->shouldReceive('canReview')->once()->with(9, 12)->andReturnTrue();
        $reviewRepository->shouldReceive('hasTopLevelReview')->once()->with(9, 12)->andReturnFalse();
        $reviewRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::type(ReviewDto::class))
            ->andReturn(new ReviewDto(id: 1, userId: 9, productId: 12, rating: 5, comment: 'Excellent'));

        $result = $reviewService->createReview(9, $dto);

        $this->assertSame(1, $result->id);
        $this->assertSame(5, $result->rating);
    }
}
