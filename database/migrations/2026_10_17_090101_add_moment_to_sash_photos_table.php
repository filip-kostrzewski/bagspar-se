<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sash_photos', function (Blueprint $table): void {
            $table->string('moment', 20)->nullable()->after('sash_id');
        });
    }

    public function down(): void
    {
        Schema::table('sash_photos', function (Blueprint $table): void {
            $table->dropColumn('moment');
        });
    }
};
