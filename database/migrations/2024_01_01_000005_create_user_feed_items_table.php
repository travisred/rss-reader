<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_feed_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('feed_item_id')->constrained()->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('starred_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'feed_item_id']);
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'is_starred']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_feed_items');
    }
};
