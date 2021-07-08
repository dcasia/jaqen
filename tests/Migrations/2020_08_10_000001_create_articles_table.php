<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{

    public function up(): void
    {
        Schema::create('articles', static function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->foreignId('user_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }

}
