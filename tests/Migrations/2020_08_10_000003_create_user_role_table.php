<?php

declare(strict_types = 1);

use DigitalCreative\Jaqen\Tests\Fixtures\Models\Role;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRoleTable extends Migration
{
    public function up(): void
    {
        Schema::create('user_role', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Role::class);
            $table->string('extra')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
}
