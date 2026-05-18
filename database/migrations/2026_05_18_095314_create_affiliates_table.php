<?php
// database/migrations/2024_01_01_000000_create_affiliates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('website');
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->integer('click_count')->default(0);
            $table->timestamps();

            // Indexes for better performance
            $table->index('status');
            $table->index('sort_order');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliates');
    }
}
