<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshops', function (Blueprint $table): void {
            $table->string('subscription_plan', 10)->nullable()->after('trial_ends_at');
            $table->timestamp('subscription_ordered_at')->nullable()->after('subscription_plan');
            $table->timestamp('paid_until')->nullable()->after('subscription_ordered_at');
            $table->string('billing_email', 254)->nullable()->after('paid_until');
            $table->string('billing_reference', 80)->nullable()->after('billing_email');
            $table->timestamp('access_reminder_sent_at')->nullable()->after('billing_reference');
        });
    }

    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table): void {
            $table->dropColumn([
                'subscription_plan',
                'subscription_ordered_at',
                'paid_until',
                'billing_email',
                'billing_reference',
                'access_reminder_sent_at',
            ]);
        });
    }
};
