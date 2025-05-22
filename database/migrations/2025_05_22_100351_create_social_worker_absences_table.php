<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialWorkerAbsencesTable extends Migration
{
    public function up()
    {
        Schema::create('social_worker_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['vacation', 'sick_leave']);
            $table->date('from');
            $table->date('to');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('social_worker_absences');
    }
}