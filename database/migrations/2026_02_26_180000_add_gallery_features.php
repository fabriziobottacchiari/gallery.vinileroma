<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cover photo per event (nullable FK to media.id — no DB constraint since
        // Spatie media can be deleted independently)
        Schema::table('events', function (Blueprint $table): void {
            $table->unsignedBigInteger('cover_media_id')->nullable()->after('has_watermark');
        });

        // Hidden flag on photo uploads (report resolution: "Nascondi foto")
        Schema::table('photo_uploads', function (Blueprint $table): void {
            $table->boolean('is_hidden')->default(false)->after('media_id')->index();
        });

        // Report status: pending → resolved (hidden) | ignored (unfounded)
        Schema::table('photo_reports', function (Blueprint $table): void {
            $table->string('status', 20)->default('pending')->after('reporter_ip')->index();
        });
    }

    public function down(): void
    {
        Schema::table('events', fn (Blueprint $t) => $t->dropColumn('cover_media_id'));
        Schema::table('photo_uploads', fn (Blueprint $t) => $t->dropColumn('is_hidden'));
        Schema::table('photo_reports', fn (Blueprint $t) => $t->dropColumn('status'));
    }
};
