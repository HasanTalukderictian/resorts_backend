<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_price_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('package_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->decimal('old_price', 12, 2);

            $table->decimal('new_price', 12, 2);

            $table->decimal('discount', 12, 2)->default(0);

            $table->decimal('final_price', 12, 2);

            $table->timestamp('changed_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_price_histories');
    }
};
