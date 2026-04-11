<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('akun');
            $table->text('password');
            $table->string('keterangan');
            $table->enum('source', ['telegram', 'whatsapp', 'dashboard'])->default('dashboard');
            $table->string('source_user')->nullable();
            $table->timestamps();

            $table->index('akun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
