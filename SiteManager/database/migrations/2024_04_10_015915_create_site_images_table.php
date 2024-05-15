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
        Schema::create('siteImages', function (Blueprint $table) {
            $table->id('imageId');
            $table->string('name');
            $table->unsignedBigInteger('projectId');
            $table->foreign('projectId')->references('projectId')->on('projects');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siteImages');
    }
};
