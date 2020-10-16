<?php

declare(strict_types = 1);

use DigitalCreative\Dashboard\Tests\Fixtures\Models\Role;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('role_user', static function(Blueprint $table) {
            $table->increments('id');
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Role::class);
            $table->string('extra')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
}
