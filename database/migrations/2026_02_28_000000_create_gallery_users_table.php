<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_users', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 30);
            $table->smallInteger('birth_year');
            $table->enum('gender', ['male', 'female', 'prefer_not_to_say']);
            $table->string('instagram_handle')->nullable();
            $table->boolean('privacy_accepted')->default(false);
            $table->boolean('newsletter_consent')->default(false);
            $table->boolean('marketing_consent')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('gallery_password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_password_reset_tokens');
        Schema::dropIfExists('gallery_users');
    }
};
