<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parking_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('guest_name');
            $table->string('vehicle_plate', 20);
            $table->enum('user_type', ['regular', 'pwd', 'senior']);
            $table->foreignId('parking_slot_id')->constrained();
            $table->dateTime('check_in');
            $table->dateTime('check_out')->nullable();
            $table->decimal('amount', 8, 2)->default(0);
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parking_transactions');
    }
};
