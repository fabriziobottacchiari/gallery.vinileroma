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
            // Drop the year+month unique index
            $table->dropUnique('events_slug_year_month_unique');

            // Add the stored day column
            $table->tinyInteger('event_day')->storedAs('DAY(event_date)')->after('event_month');

            // New composite unique including day: same slug allowed only if date differs
            $table->unique(['slug', 'event_year', 'event_month', 'event_day'], 'events_slug_year_month_day_unique');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropUnique('events_slug_year_month_day_unique');
            $table->dropColumn('event_day');
            $table->unique(['slug', 'event_year', 'event_month'], 'events_slug_year_month_unique');
        });
    }
};
