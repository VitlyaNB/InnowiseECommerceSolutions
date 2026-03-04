<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function __construct(protected ReviewService $service)
    {
    }

    public function index($productId): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getProductReviews($productId)
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:reviews,id'
        ]);

        try {
            $review = $this->service->createReview($request->user()->id, $request->all());
            return response()->json(['message' => 'Отзыв опубликован', 'data' => $review], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function like(Request $request, $id): JsonResponse
    {
        $isLiked = $this->service->toggleLike($request->user()->id, $id);
        return response()->json(['liked' => $isLiked]);
    }

    // Проверка, может ли юзер оставить отзыв (для UI)
    public function checkPermission(Request $request, $productId): JsonResponse
    {
        $can = $this->service->canReview($request->user()->id, $productId);
        return response()->json(['can_review' => $can]);
    }
}
