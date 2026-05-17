<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->decimal('old_price', 12, 2)->default(0);

            $table->decimal('price', 12, 2);

            $table->decimal('discount', 12, 2)->default(0);

            $table->decimal('final_price', 12, 2);

            $table->string('color')->nullable();

            $table->enum('status', ['active', 'inactive'])
                  ->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
