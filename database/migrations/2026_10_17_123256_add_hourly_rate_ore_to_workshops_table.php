<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshops', function (Blueprint $table): void {
            $table->unsignedInteger('hourly_rate_ore')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table): void {
            $table->dropColumn('hourly_rate_ore');
        });
    }
};
