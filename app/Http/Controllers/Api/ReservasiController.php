<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\ReservasiResource;

use App\Models\Reservasi;
use App\Models\User;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservasiController extends Controller
{
    public function index()
    {
        $reservasis = Reservasi::latest()->paginate(5);

        return new PostResource(true, 'List Data Reservasi', $reservasis);
    }

    public function reservasiByUser($id)
    {
        $findReservasis = Reservasi::where('id_user', $id)->get();

        return new PostResource(true, 'List Data Reservasi', $findReservasis);
    }
    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|unique:reservasis',
            'end_date' => 'required|unique:reservasis',
            'dewasa' => 'required',
            'anak' => 'required',
            'nomor_telepon' => 'required|regex:/^0\d{9,11}$/',
            'nama' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }
        if ($request->dewasa == 0) {
            return response()->json([
                'message' => 'Orang Dewasa Tidak Boleh Nol!'
            ], 422);
        }

        $user = User::find($id);
        $user->update([
            'nomor_telepon' => $request->nomor_telepon
        ]);

        $reservasi = Reservasi::create([
            'id_user' => $id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'dewasa' => $request->dewasa,
            'anak' => $request->anak,
            'nomor_telepon' => $request->nomor_telepon,
            'nama' => $request->nama,
            'status' => 'PEMBAYARAN'
        ]);

        $reservasi->load('user');

        return new ReservasiResource(true, 'Reservasi Berhasil Ditambahkan!', $reservasi);
    }

    public function checkDate(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($startDate && $endDate) {
            $conflictingReservations = Reservasi::where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })->get();

            if ($conflictingReservations->isNotEmpty()) {
                $conflictingDates = $conflictingReservations->map(function ($reservasi) {
                    return [
                        'start_date' => $reservasi->start_date,
                        'end_date' => $reservasi->end_date
                    ];
                });

                return new PostResource(true, 'Homestay Penuh', $conflictingDates);
            } else {
                $conflictingDates = null;

                return new PostResource(true, 'Homestay Tersedia', $conflictingDates);
            }
        } else {
            $check = null;
            return new PostResource(true, '', $check);
        }
    }
    public function update(Request $request, $id, $startDate) {
        
        $reservasi = Reservasi::where([['id_user',$id], ['start_date', $startDate]])->first();

        $validator = Validator::make($request->all(), [
            'dewasa' => 'required',
            'anak' => 'required',
            'nomor_telepon' => 'required',
            'nama' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        $reservasi->update([
            'dewasa' => $request->dewasa,
            'anak' => $request->anak,
            'nomor_telepon' => $request->nomor_telepon,
            'nama' => $request->nama
        ]);

        return new PostResource(true, 'Update Berhasil', $reservasi);
    }
}
