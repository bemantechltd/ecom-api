<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductReturnRequestInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_return_request_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_item_pk')->unsigned()->nullabel();
            $table->bigInteger('order_id')->unsigned()->nullabel();
            $table->tinyInteger('return_reason_id')->unsigned()->nullable();
            $table->text('description')->nullable();
            $table->json('photos')->nullable();
            $table->boolean('status')->default(0);
            $table->boolean('accept_status')->default(0);
            $table->text('reject_reason')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        
        Schema::table('product_return_request_infos', function(Blueprint $table){
            $table->foreign('order_item_pk')->references('id')->on('order_items_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('order_id')->references('id')->on('order_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('return_reason_id')->references('id')->on('product_return_reasons')->onDelete('set null')->onUpdate('cascade');
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
        Schema::dropIfExists('product_return_request_infos');
    }
}
