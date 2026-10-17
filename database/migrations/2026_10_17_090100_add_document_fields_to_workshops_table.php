<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshops', function (Blueprint $table): void {
            $table->string('organization_number', 20)->nullable()->after('name');
            $table->string('contact_email', 254)->nullable()->after('organization_number');
            $table->string('address', 255)->nullable()->after('contact_email');
            $table->string('logo_path')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table): void {
            $table->dropColumn([
                'organization_number',
                'contact_email',
                'address',
                'logo_path',
            ]);
        });
    }
};
