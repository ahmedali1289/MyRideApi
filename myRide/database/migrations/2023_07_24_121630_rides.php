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
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->string('pick_up')->nullable();
            $table->string('drop_of')->nullable(); 
            $table->string('no_of_passenger')->nullable(); 
            $table->string('distance')->nullable();
            $table->string('estimated_time')->nullable();
            $table->string('estimated_fare')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('card_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('accept_driver_id')->nullable();
            $table->string('accept_time')->nullable();
            $table->string('code')->nullable();
            $table->timestamps();
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('card_id')->references('id')->on('cards');
            $table->foreign('accept_driver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
