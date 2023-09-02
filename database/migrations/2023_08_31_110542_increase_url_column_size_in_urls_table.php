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
        Schema::table('urls', function (Blueprint $table) {
            $table->string('url', 1000)->change(); // Increase the limit to 1000 characters or more

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('urls', function (Blueprint $table) {
            $table->string('url')->change();
        });
    }
};
