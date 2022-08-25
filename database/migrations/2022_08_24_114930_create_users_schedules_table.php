<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->timestamp('booked_schedule_slot');
            $table->timestamps();
            $table->index(['user_id','booked_schedule_slot']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_schedules');
    }
};
