<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->unsignedBigInteger('views_count')->default(0)->after('cover_media_id');
        });

        Schema::table('photo_uploads', function (Blueprint $table): void {
            $table->unsignedBigInteger('downloads_count')->default(0)->after('is_hidden');
        });
    }

    public function down(): void
    {
        Schema::table('events', fn (Blueprint $t) => $t->dropColumn('views_count'));
        Schema::table('photo_uploads', fn (Blueprint $t) => $t->dropColumn('downloads_count'));
    }
};
