<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XAccount extends Model
{
    protected $fillable = [
        'nama',
        'username',
        'email',
        'status',
        'link',
        'source',
        'source_user',
    ];
}
