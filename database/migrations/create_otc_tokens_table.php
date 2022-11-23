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
        Schema::create('otc_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('related_id');
            $table->string('related_type');
            $table->string('code')->nullable();
            $table->dateTime('code_valid_until')->nullable();
            $table->string('token')->nullable();
            $table->dateTime('token_valid_until')->nullable();
            $table->string('ip')->nullable();
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
        Schema::dropIfExists('otc_tokens');
    }
};
