<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pilot_leads', function (Blueprint $table) {
            $table->id();
            $table->string('company', 160);
            $table->string('email', 254);
            $table->text('message')->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilot_leads');
    }
};
