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
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('car_make')->nullable();
            $table->string('car_model')->nullable();
            $table->string('car_year')->nullable();
            $table->string('car_color')->nullable();
            $table->string('car_capacity')->nullable();
            $table->string('service')->nullable();
            $table->string('driver_liscence')->nullable();
            $table->string('car_registration')->nullable();
            $table->string('car_insurance')->nullable();
            $table->string('liscence_picture')->nullable();
            $table->string('car_picture')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_documents');
    }
};
