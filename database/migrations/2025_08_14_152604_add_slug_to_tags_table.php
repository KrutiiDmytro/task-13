<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToTagsTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tags', 'slug')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->string('slug')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tags', 'slug')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
}