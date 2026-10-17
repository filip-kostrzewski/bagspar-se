<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('client_status_token', 64)->nullable()->unique();
            $table->boolean('client_status_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropUnique(['client_status_token']);
            $table->dropColumn(['client_status_token', 'client_status_enabled']);
        });
    }
};
