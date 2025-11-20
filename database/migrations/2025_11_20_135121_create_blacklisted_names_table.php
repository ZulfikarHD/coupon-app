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
        Schema::create('blacklisted_names', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('reason')->nullable();
            $table->timestamps();

            // Index for faster lookups
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklisted_names');
    }
};
