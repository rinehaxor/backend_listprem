<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Email extends Model
{
    protected $fillable = [
        'akun',
        'password',
        'keterangan',
        'source',
        'source_user',
    ];

    protected $hidden = [
        'password',
    ];

    // Encrypt password saat simpan
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    // Decrypt password saat ambil
    public function getPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}
