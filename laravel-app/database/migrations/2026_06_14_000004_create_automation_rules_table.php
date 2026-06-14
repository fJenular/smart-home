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
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('condition'); // JSON format representing conditions (e.g., {"sensor": "temperature", "operator": ">", "value": 30.0})
            $table->text('action');    // JSON format representing actions (e.g., {"device_id": 2, "status": "ON"})
            $table->boolean('status')->default(true); // Active / Inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
