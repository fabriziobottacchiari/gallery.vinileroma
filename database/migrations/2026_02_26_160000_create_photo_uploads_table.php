<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_uploads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('batch_uuid', 36)->index();
            $table->string('original_filename');
            $table->string('temp_path')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                ->default('pending')
                ->index();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('media_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_uploads');
    }
};
