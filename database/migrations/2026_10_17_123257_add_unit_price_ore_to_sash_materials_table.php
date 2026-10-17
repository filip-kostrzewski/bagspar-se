<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sash_materials', function (Blueprint $table): void {
            $table->unsignedInteger('unit_price_ore')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sash_materials', function (Blueprint $table): void {
            $table->dropColumn('unit_price_ore');
        });
    }
};
