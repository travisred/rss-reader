<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('site_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamp('last_modified_at')->nullable();
            $table->string('etag')->nullable();
            $table->integer('fetch_interval')->default(15); // minutes
            $table->boolean('is_active')->default(true);
            $table->text('error_message')->nullable();
            $table->integer('error_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
