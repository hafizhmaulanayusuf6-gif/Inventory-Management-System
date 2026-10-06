<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('type', 20); // in | out | adjustment
            $table->unsignedInteger('qty');
            $table->unsignedInteger('balance'); // stok akhir setelah mutasi ini
            $table->nullableMorphs('reference'); // reference_type + reference_id (+ index)
            $table->foreignId('user_id')->constrained()->restrictOnDelete();

            // Immutable: hanya created_at, tanpa updated_at.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};