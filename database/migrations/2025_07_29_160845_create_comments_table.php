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
        $table->id();
        $table->foreignId('post_id')->constrained()->onDelete('cascade');
        $table->string('author_name', 255)->nullable(); 
        $table->string('author_email', 255)->nullable(); 
        $table->text('content');
        $table->timestamps();
        
        $table->index('created_at');
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