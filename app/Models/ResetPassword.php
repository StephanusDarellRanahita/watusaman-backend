<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use app\Models\User;

class ResetPassword extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'otp',
        'status'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }
}
