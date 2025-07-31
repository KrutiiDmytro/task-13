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
        Schema::create('comments', function (Blueprint $table) {
            $table->id(); // id INT AUTO_INCREMENT PRIMARY KEY
            $table->foreignId('post_id')->constrained()->onDelete('cascade'); // FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
            $table->string('author', 255); // VARCHAR(255) NOT NULL
            $table->text('content'); // TEXT NOT NULL
            // created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            // Laravel автоматически управляет created_at и updated_at при использовании timestamps()
            // Если вам нужен только created_at без updated_at, используйте $table->timestamp('created_at')->useCurrent();
            // Но обычно для комментариев достаточно timestamps()
            $table->timestamps();

            // Индексы
            // $table->index('post_id'); // Индекс idx_post_id создается constrained()
            $table->index('created_at'); // INDEX idx_created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments'); // Удаление таблицы
    }
};