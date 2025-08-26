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
        Schema::create('usersfix', function (Blueprint $table) {
            $table->id();
            $table->string('email_usersfix', 45);
            $table->string('password_usersfix', 100);
            $table->timestamps();
            $table->softDeletes(); // if deleted_at is used
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::dropIfExists('usersfix');
    }
};
