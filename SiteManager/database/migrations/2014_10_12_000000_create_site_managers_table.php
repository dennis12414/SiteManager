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
        Schema::create('siteManagers', function (Blueprint $table) { 
            $table->id('siteManagerId');
            $table->string('name');
            $table->string('email');
            $table->string('phoneNumber');
            $table->string('otp')->nullable();
            $table->string('password')->nullable();
            $table->softDeletes(); 
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::table('siteManagers', function (Blueprint $table) {
            $table->boolean('phoneVerified')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siteManagers');
    }
};
