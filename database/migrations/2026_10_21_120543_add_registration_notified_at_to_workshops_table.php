<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
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
        Schema::table('workshops', function (Blueprint $table) {
            $table->timestamp('registration_notified_at')->nullable()->after('onboarding_nudge_sent_at');
        });

        // Workshops whose owner verified before this column existed are not new signups.
        DB::table('workshops')
            ->whereExists(fn (Builder $query) => $query
                ->select(DB::raw(1))
                ->from('users')
                ->whereColumn('users.workshop_id', 'workshops.id')
                ->where('users.role', 'owner')
                ->whereNotNull('users.email_verified_at'))
            ->update(['registration_notified_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('registration_notified_at');
        });
    }
};
