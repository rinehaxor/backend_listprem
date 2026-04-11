<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('kategori');
            $table->string('keterangan');
            $table->decimal('nominal', 12, 2);
            $table->enum('source', ['telegram', 'whatsapp', 'dashboard'])->default('dashboard');
            $table->string('source_user')->nullable();
            $table->timestamps();

            $table->index('tanggal');
            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
