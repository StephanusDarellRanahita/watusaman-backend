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
    public function getPendapatanBulanan($tahun)
    {
        $pendapatanBulanan = DB::table('reservasis')
            ->selectRaw('YEAR(start_date) as year, MONTH(start_date) as month, SUM(total_harga) as total_harga')
            ->where('status', '!=', 'batal')
            ->where('status', '!=', 'pembayaran')
            ->whereYear('start_date',$tahun)
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        
            if(!$pendapatanBulanan) {
                return response()->json([
                    'message' => 'Tahun Tidak Valid'
                ], 422);
            }

        return response()->json($pendapatanBulanan);
    }
}