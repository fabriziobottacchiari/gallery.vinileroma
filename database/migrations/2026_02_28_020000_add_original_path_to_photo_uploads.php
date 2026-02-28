<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_uploads', function (Blueprint $table): void {
            $table->string('original_path')->nullable()->after('temp_path');
        });
    }

    public function down(): void
    {
        Schema::table('photo_uploads', function (Blueprint $table): void {
            $table->dropColumn('original_path');
        });
    }
};
