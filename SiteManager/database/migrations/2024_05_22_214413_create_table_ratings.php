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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id('ratingId');
            $table->unsignedBigInteger('siteManagerId');
            $table->foreign('siteManagerId')->references('siteManagerId')->on('siteManagers');
            $table->string('message')->nullable();
            $table->unsignedBigInteger('stars');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_ratings');
    }
};
