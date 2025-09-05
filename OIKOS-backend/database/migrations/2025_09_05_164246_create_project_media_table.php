<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['image', 'video']);
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            // in bytes
            $table->unsignedBigInteger('file_size');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            // per video compressi
            $table->string('compressed_path')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            // per video in secondi
            $table->integer('duration')->nullable();
            $table->text('alt_text')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'type']);
            $table->index(['project_id', 'is_featured']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_media');
    }
};
