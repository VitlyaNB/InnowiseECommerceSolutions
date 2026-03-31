<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductView;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'balance' => 1000.00,
            ]
        );

        /** @var Collection<int, User> $users */
        $users = User::factory(5)->create();
        $users->push($admin);

        /** @var Collection<int, Category> $categories */
        $categories = Category::factory(6)->create();
        /** @var Collection<int, Product> $products */
        $products = Product::factory(20)->create([
            'category_id' => fn () => $categories->random()->id,
        ]);

        foreach ($products as $product) {
            ProductImage::factory(rand(1, 3))->create([
                'product_id' => $product->id,
            ]);
        }

        /** @var Collection<int, Order> $orders */
        $orders = Order::factory(12)->create([
            'user_id' => fn () => $users->random()->id,
        ]);

        foreach ($orders as $order) {
            OrderItem::factory(rand(1, 4))->create([
                'order_id' => $order->id,
                'product_id' => $products->random()->id,
            ]);
        }

        foreach ($users as $user) {
            CartItem::factory(rand(1, 4))->create([
                'user_id' => $user->id,
                'product_id' => $products->random()->id,
            ]);
        }

        /** @var Collection<int, Review> $reviews */
        $reviews = Review::factory(30)->create([
            'user_id' => fn () => $users->random()->id,
            'product_id' => fn () => $products->random()->id,
            'parent_id' => null,
        ]);

        foreach ($reviews->take(10) as $review) {
            Review::factory(rand(1, 2))->create([
                'user_id' => $users->random()->id,
                'product_id' => $review->product_id,
                'parent_id' => $review->id,
                'rating' => null,
            ]);
        }

        foreach ($reviews as $review) {
            /** @var \Illuminate\Support\Collection<int, int> $likers */
            $likers = $users->random(rand(0, min(3, $users->count())))->pluck('id');
            foreach ($likers as $userId) {
                ReviewLike::query()->firstOrCreate([
                    'user_id' => $userId,
                    'review_id' => $review->id,
                ]);
            }
        }

        foreach ($users as $user) {
            Chat::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['last_message_at' => now()]
            );
        }

        /** @var Collection<int, Chat> $chats */
        $chats = Chat::query()->get();
        foreach ($chats as $chat) {
            Message::factory(rand(1, 4))->create([
                'chat_id' => $chat->id,
                'user_id' => $chat->user_id,
            ]);
        }

        foreach ($users as $user) {
            $viewedProducts = $products->random(rand(2, 6));
            foreach ($viewedProducts as $product) {
                ProductView::query()->updateOrCreate(
                    ['user_id' => $user->id, 'product_id' => $product->id],
                    ['session_id' => null, 'viewed_at' => now()->subMinutes(rand(1, 600))]
                );
            }
        }
    }
}
