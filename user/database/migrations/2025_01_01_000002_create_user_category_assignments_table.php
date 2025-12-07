<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_category_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('category_id')->nullable(); // Foreign to category atom
            $table->unsignedBigInteger('subcategory_id')->nullable(); // Foreign to category atom
            $table->timestamps();
            
            $table->unique(['user_id', 'category_id', 'subcategory_id'], 'user_cat_subcat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_category_assignments');
    }
};

