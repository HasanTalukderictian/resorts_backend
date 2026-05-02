<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyOffersTable extends Migration
{
    public function up()
    {
        Schema::create('property_offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('brand_name');
            $table->string('whatsapp_number');
            $table->text('description')->nullable();

            // JSON fields
            $table->json('features')->nullable();
            $table->json('slider_images')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_offers');
    }
}
