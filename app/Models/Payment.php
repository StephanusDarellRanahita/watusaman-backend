<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Reservasi;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'id_reservasi',
        'total_bayar',
        'tanggal_bayar',
        'status',
        'tipe'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function reservasi() {
        return $this->belongsTo(Reservasi::class, 'id_reservasi');
    }
}
