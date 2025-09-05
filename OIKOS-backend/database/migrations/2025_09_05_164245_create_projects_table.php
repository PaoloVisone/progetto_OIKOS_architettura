<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('long_description')->nullable();
            $table->string('client')->nullable();
            $table->string('location')->nullable();
            $table->date('project_date')->nullable();
            // metri quadri
            $table->decimal('area', 8, 2)->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            // array di tags
            $table->json('tags')->nullable();
            $table->string('featured_image')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_featured']);
            $table->index('project_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
