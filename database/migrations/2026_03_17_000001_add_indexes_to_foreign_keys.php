<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('products', 'category_id', 'products_category_id_index');
        $this->addIndexIfMissing('cart_items', 'user_id', 'cart_items_user_id_index');
        $this->addIndexIfMissing('cart_items', 'product_id', 'cart_items_product_id_index');
        $this->addIndexIfMissing('orders', 'user_id', 'orders_user_id_index');
        $this->addIndexIfMissing('order_items', 'order_id', 'order_items_order_id_index');
        $this->addIndexIfMissing('order_items', 'product_id', 'order_items_product_id_index');
        $this->addIndexIfMissing('product_images', 'product_id', 'product_images_product_id_index');
        $this->addIndexIfMissing('reviews', 'user_id', 'reviews_user_id_index');
        $this->addIndexIfMissing('reviews', 'product_id', 'reviews_product_id_index');
        $this->addIndexIfMissing('reviews', 'parent_id', 'reviews_parent_id_index');
        $this->addIndexIfMissing('review_likes', 'user_id', 'review_likes_user_id_index');
        $this->addIndexIfMissing('review_likes', 'review_id', 'review_likes_review_id_index');
        $this->addIndexIfMissing('product_views', 'user_id', 'product_views_user_id_index');
        $this->addIndexIfMissing('product_views', 'product_id', 'product_views_product_id_index');
        $this->addIndexIfMissing('chats', 'user_id', 'chats_user_id_index');
        $this->addIndexIfMissing('messages', 'chat_id', 'messages_chat_id_index');
        $this->addIndexIfMissing('messages', 'user_id', 'messages_user_id_index');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('products', 'products_category_id_index');
        $this->dropIndexIfExists('cart_items', 'cart_items_user_id_index');
        $this->dropIndexIfExists('cart_items', 'cart_items_product_id_index');
        $this->dropIndexIfExists('orders', 'orders_user_id_index');
        $this->dropIndexIfExists('order_items', 'order_items_order_id_index');
        $this->dropIndexIfExists('order_items', 'order_items_product_id_index');
        $this->dropIndexIfExists('product_images', 'product_images_product_id_index');
        $this->dropIndexIfExists('reviews', 'reviews_user_id_index');
        $this->dropIndexIfExists('reviews', 'reviews_product_id_index');
        $this->dropIndexIfExists('reviews', 'reviews_parent_id_index');
        $this->dropIndexIfExists('review_likes', 'review_likes_user_id_index');
        $this->dropIndexIfExists('review_likes', 'review_likes_review_id_index');
        $this->dropIndexIfExists('product_views', 'product_views_user_id_index');
        $this->dropIndexIfExists('product_views', 'product_views_product_id_index');
        $this->dropIndexIfExists('chats', 'chats_user_id_index');
        $this->dropIndexIfExists('messages', 'messages_chat_id_index');
        $this->dropIndexIfExists('messages', 'messages_user_id_index');
    }

    private function addIndexIfMissing(string $table, string $column, string $indexName): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($column, $indexName) {
            $blueprint->index($column, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }
};
