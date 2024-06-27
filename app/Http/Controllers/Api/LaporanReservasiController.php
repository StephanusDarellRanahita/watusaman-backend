<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\ReservasiResource;

use Illuminate\Support\Facades\DB;

use App\Models\Reservasi;
use App\Models\User;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LaporanReservasiController extends Controller
{
    public function getPendapatanBulanan()
    {
        $pendapatanBulanan = DB::table('reservasis')
            ->selectRaw('YEAR(start_date) as year, MONTH(start_date) as month, SUM(total_harga) as total_harga')
            ->where('status', '!=', 'cancel')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json($pendapatanBulanan);
    }
}