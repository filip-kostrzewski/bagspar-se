<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pilot_leads', function (Blueprint $table) {
            $table->timestamp('consented_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('pilot_leads')->whereNull('consented_at')->update([
            'consented_at' => now(),
        ]);

        Schema::table('pilot_leads', function (Blueprint $table) {
            $table->timestamp('consented_at')->nullable(false)->change();
        });
    }
};
