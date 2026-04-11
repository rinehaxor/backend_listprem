<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = [
        'tanggal',
        'aplikasi',
        'jenis',
        'laba',
        'source',
        'source_user',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'laba' => 'decimal:2',
    ];
}
