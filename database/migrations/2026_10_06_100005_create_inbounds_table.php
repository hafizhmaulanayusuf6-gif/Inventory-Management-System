<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbounds', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('reference_no', 50)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('completed');
            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbounds');
    }
};