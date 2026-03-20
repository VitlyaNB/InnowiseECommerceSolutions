<?php

namespace Tests\Unit\Services;

use App\Dto\ReviewDto;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Services\ReviewService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    private ReviewRepositoryInterface|MockInterface $reviewRepository;

    private ReviewService $reviewService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->reviewService = new ReviewService($this->reviewRepository);
    }

    public function test_it_creates_review(): void
    {
        $dto = new ReviewDto(productId: 12, rating: 5, comment: 'Excellent');

        $this->reviewRepository->shouldReceive('canReview')->once()->with(9, 12)->andReturnTrue();
        $this->reviewRepository->shouldReceive('hasTopLevelReview')->once()->with(9, 12)->andReturnFalse();
        $this->reviewRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::type(ReviewDto::class))
            ->andReturn(new ReviewDto(id: 1, userId: 9, productId: 12, rating: 5, comment: 'Excellent'));

        $result = $this->reviewService->createReview(9, $dto);

        $this->assertSame(1, $result->id);
        $this->assertSame(5, $result->rating);
    }
}
