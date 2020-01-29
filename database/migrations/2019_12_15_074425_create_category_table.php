<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->String('name_en');
            $table->String('name_ar');
            $table->String('name_ku');
            $table->String('barcode');
            $table->String('brand');
            $table->bigInteger('fourm');
            $table->bigInteger('country');
            $table->bigInteger('company');
            $table->double('income_price');
            $table->double('price_market');
            $table->double('price_store');
            $table->String('size');
            $table->integer('carton');
            $table->double('rate')->default(0);
            $table->String('imagefile')->default('default/category.jpg');
            $table->String('batch_number');
            $table->dateTime('expiry_date');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category');
    }
}
