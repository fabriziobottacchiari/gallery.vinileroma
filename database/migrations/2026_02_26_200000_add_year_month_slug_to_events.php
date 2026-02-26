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
            // Drop the old globally-unique index on slug
            $table->dropUnique(['slug']);

            // Stored generated columns derived from event_date (MySQL 5.7+)
            $table->smallInteger('event_year')->storedAs('YEAR(event_date)')->after('event_date');
            $table->tinyInteger('event_month')->storedAs('MONTH(event_date)')->after('event_year');

            // Same slug is allowed in different months (e.g. two "discoteca" events in Jan vs Feb)
            $table->unique(['slug', 'event_year', 'event_month'], 'events_slug_year_month_unique');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropUnique('events_slug_year_month_unique');
            $table->dropColumn(['event_year', 'event_month']);
            $table->unique('slug');
        });
    }
};
