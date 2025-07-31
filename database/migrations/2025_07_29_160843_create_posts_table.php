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
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); // id INT AUTO_INCREMENT PRIMARY KEY
            $table->string('title', 255); // VARCHAR(255) NOT NULL
            $table->text('content'); // TEXT NOT NULL
            $table->date('date'); // DATE NOT NULL
            $table->string('image', 255)->nullable(); // VARCHAR(255), nullable()

            // Внешний ключ для category_id
            // foreignId('column_name') создает UNSIGNED BIGINT
            // constrained('table_name') создает внешний ключ на id указанной таблицы
            // onDelete('set null') указывает действие при удалении связанной категории
            $table->foreignId('category_id')
                  ->nullable() // Разрешает NULL, так как в SQL-схеме category_id может быть NULL
                  ->constrained('categories') // Ссылка на таблицу 'categories', столбец 'id'
                  ->onDelete('set null'); // ON DELETE SET NULL

            $table->timestamps(); // created_at и updated_at

            $table->index('date'); // INDEX idx_date
            $table->index('title'); // INDEX idx_title
            // Индекс для category_id создается автоматически методом constrained()
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts'); // Удаление таблицы
    }
};