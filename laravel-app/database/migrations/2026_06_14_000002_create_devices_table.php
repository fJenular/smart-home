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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('topic'); // e.g., 'home/control/lamp' or 'home/status/lamp'
            $table->string('status')->default('OFF'); // e.g., 'ON', 'OFF', 'OPEN', 'LOCKED'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
