<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductPriceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_price_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type_title')->unique();
            $table->string('slug')->unique();
            $table->integer('cat_id')->unsigned()->nullable();
            $table->string('icon')->nullable();
            $table->tinyInteger('icon_type')->nullable();
            $table->boolean('status')->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('product_price_types', function(Blueprint $table){
            $table->foreign('cat_id')->references('id')->on('categories')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_price_types');
    }
}
