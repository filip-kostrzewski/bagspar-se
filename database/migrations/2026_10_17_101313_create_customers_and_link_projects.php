<?php

use App\Support\CustomerBackfill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('contact_name', 160)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('email', 254)->nullable();
            $table->timestamps();

            $table->index(['workshop_id', 'name']);
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->foreignId('customer_id')->nullable()->after('workshop_id')->constrained()->nullOnDelete();
        });

        CustomerBackfill::run();
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::dropIfExists('customers');
    }
};
