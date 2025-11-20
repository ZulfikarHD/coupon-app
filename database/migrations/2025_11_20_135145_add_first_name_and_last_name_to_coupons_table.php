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
        Schema::table('coupons', function (Blueprint $table) {
            // Add first_name and last_name columns after customer_name
            $table->string('first_name')->after('customer_name')->nullable();
            $table->string('last_name')->after('first_name')->nullable();

            // Index for searching
            $table->index(['first_name', 'last_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex(['first_name', 'last_name']);
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
