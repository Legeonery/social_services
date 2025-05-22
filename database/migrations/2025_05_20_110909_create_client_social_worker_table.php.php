<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientSocialWorkerTable extends Migration
{
    public function up()
    {
        Schema::create('client_social_worker', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('social_worker_id');
            $table->boolean('temporary')->default(false);
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->timestamps();

            $table->primary(['client_id', 'social_worker_id']);

            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('social_worker_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_social_worker');
    }
}
