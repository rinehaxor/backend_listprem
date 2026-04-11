<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('aplikasi');
            $table->string('jenis');
            $table->decimal('laba', 12, 2);
            $table->enum('source', ['telegram', 'whatsapp', 'dashboard'])->default('dashboard');
            $table->string('source_user')->nullable();
            $table->timestamps();

            $table->index('tanggal');
            $table->index('aplikasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
