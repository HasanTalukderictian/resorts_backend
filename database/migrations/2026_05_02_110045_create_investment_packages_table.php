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
        Schema::create('investment_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('price')->nullable(); // String রাখা ভালো কারণ আপনি কমা (,) সহ ডাটা সেভ করতে পারেন
            $table->string('discount')->nullable();
            $table->string('land')->nullable();
            $table->string('building')->nullable();
            $table->string('total_size')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_sold_out')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_packages');
    }
};
