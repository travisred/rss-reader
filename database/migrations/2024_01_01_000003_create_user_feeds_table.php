<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('feed_id')->constrained()->onDelete('cascade');
            $table->string('custom_title')->nullable();
            $table->string('folder')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'feed_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_feeds');
    }
};
