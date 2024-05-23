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
        Schema::create('advert', function (Blueprint $table) {
            $table->id('advertId');
            $table->string('title');
            $table->string('image');
            $table->string('description');
            $table->string('status');
            $table->string('price');
            $table->string('date');
            $table->string('location');
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_advert');
    }
};
