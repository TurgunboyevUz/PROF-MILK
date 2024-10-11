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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->string('name_uz');
            $table->string('name_ru');
            $table->text('description_uz');
            $table->text('description_ru');
            $table->string('image');
            $table->decimal('price', 10, 2);
            $table->string('code');
            $table->integer('vat_percent');
            $table->string('package_code');
            $table->softDeletes();
            $table->timestamps();

            $table->index('name_uz');
            $table->index('name_ru');
            $table->index('price');
            $table->index('code');
            $table->index('vat_percent');
            $table->index('package_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
