<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserDana extends Authenticatable
{
    use HasFactory, HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'nama',
        'email',
        'nomor_telepon',
        'password',
        'balance',
        'pin',
        'otp',
        'email_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];
}
