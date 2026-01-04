<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained()->onDelete('cascade');
            $table->string('guid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('url');
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('categories')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->timestamps();

            $table->index(['feed_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_items');
    }
};
