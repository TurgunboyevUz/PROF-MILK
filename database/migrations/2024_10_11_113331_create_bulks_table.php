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
        Schema::create('bulks', function (Blueprint $table) {
            $table->id();
            $table->integer('success')->default(0);
            $table->integer('failed')->default(0);
            $table->bigInteger('chat_id');
            $table->bigInteger('message_id');
            $table->text('reply_markup')->nullable();
            $table->string('type');
            $table->boolean('active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulks');
    }
};
