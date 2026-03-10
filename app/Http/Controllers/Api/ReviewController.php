<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function __construct(protected ReviewService $service)
    {
    }

    public function index(int $productId): JsonResponse
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

        /** @var User $user */
        $user = $request->user();

        try {
            /** @var array<string, mixed> $data */
            $data = $request->all();
            $review = $this->service->createReview($user->id, $data);
            return response()->json(['message' => 'Отзыв опубликован', 'data' => $review], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function like(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        
        $isLiked = $this->service->toggleLike($user->id, $id);
        return response()->json(['liked' => $isLiked]);
    }

    public function checkPermission(Request $request, int $productId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        
        $can = $this->service->canReview($user->id, $productId);
        return response()->json(['can_review' => $can]);
    }
}
