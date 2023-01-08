<?php

declare(strict_types = 1);

use DigitalCreative\Jaqen\Tests\Fixtures\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhonesTable extends Migration
{
    public function up(): void
    {
        Schema::create('phones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->foreignIdFor(User::class);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
}
