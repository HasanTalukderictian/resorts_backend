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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();

            $table->string('author');

            $table->string('category');

            $table->enum('status', ['Draft', 'Published'])->default('Draft');

            $table->text('excerpt');

            $table->string('image')->nullable();

            $table->longText('introduction');

            $table->json('sections')->nullable();

            $table->longText('conclusion')->nullable();

            $table->integer('views')->default(0);

            $table->integer('likes')->default(0);

            $table->string('read_time')->default('5 min read');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
