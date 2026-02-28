<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_user_favorites', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('gallery_user_id');
            $table->unsignedBigInteger('media_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('photo_upload_id')->nullable();
            $table->timestamps();

            $table->unique(['gallery_user_id', 'media_id']);
            $table->foreign('gallery_user_id')->references('id')->on('gallery_users')->cascadeOnDelete();
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_user_favorites');
    }
};
