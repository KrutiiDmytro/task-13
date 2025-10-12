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
        Schema::create('post_tag', function (Blueprint $table) {
            // foreignId() создает UNSIGNED BIGINT, который соответствует id() в Laravel
            $table->foreignId('post_id')->constrained()->onDelete('cascade'); // FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');   // FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE

            $table->primary(['post_id', 'tag_id']); // PRIMARY KEY (post_id, tag_id)
            // Индексы для post_id и tag_id создаются автоматически методом constrained()
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_tags'); // Удаление таблицы
    }
};
