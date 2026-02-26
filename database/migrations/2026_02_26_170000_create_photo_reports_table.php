<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('photo_upload_id')->constrained('photo_uploads')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 50);
            $table->text('comment')->nullable();
            $table->string('reporter_ip', 45);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_reports');
    }
};
