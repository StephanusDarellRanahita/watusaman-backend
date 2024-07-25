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
    public function index($tahun)
    {
        $reservasis = Reservasi::whereYear('start_date', $tahun)
            ->orderBy('start_date', 'asc')->paginate(10);

        return new PostResource(true, 'List Data Reservasi', $reservasis);
    }

    public function showAll() {
        $reservasis = Reservasi::all();
        return new PostResource(true, 'List Data Reservasi', $reservasis);
    }

    public function reservasiByUser($id)
    {
        $findReservasis = Reservasi::where('id_user', $id)->get();

        if (!$findReservasis) {
            return response()->json([
                'message' => 'Reservasi Tidak Ditemukan!',
                'data' => null
            ], 422);
        }

        return new PostResource(true, 'Data Reservasi', $findReservasis);
    }
    public function reservasiByid($id)
    {
        $findReservasis = Reservasi::where('id', $id)->first();
        return new PostResource(true, 'Data Reservasi', $findReservasis);
    }
    public function reservasiByUserPayed($id)
    {
        $findReservasis = Reservasi::where([['id_user', $id], ['status', 'INVOICE']])->first();

        if (!$findReservasis) {
            return response()->json([
                'message' => 'Reservasi Tidak Ditemukan!'
            ], 422);
        }
        return new PostResource(true, 'Reservasi Didapatkan', $findReservasis);
    }

    public function reservasiUserPayment($id)
    {
        $findReservasis = Reservasi::where([['id_user', $id], ['status', 'PEMBAYARAN']])->first();

        if (!$findReservasis) {
            return response()->json([
                'message' => 'Reservasi Tidak Ditemukan!'
            ], 422);
        }
        return new PostResource(true, 'Reservasi Didapatkan', $findReservasis);
    }
    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|unique:reservasis',
            'end_date' => 'required|unique:reservasis',
            'dewasa' => 'required',
            'anak' => 'required',
            'nomor_telepon' => 'required|regex:/^0\d{9,11}$/',
            'nama' => 'required',
            'total_harga' => 'required|numeric'
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
            'status' => 'PEMBAYARAN',
            'total_harga' => $request->total_harga,
            'sisa_pembayaran' => $request->total_harga
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

                return new PostResource(true, 'Villa Penuh', $conflictingDates);
            } else {
                $conflictingDates = null;

                return new PostResource(true, 'Villa Tersedia', $conflictingDates);
            }
        } else {
            $check = null;
            return new PostResource(true, '', $check);
        }
    }
    public function update(Request $request, $id, $startDate)
    {

        $reservasi = Reservasi::where([['id_user', $id], ['start_date', $startDate]])->first();

        $validator = Validator::make($request->all(), [
            'dewasa' => 'required',
            'anak' => 'required',
            'nomor_telepon' => 'required',
            'nama' => 'required',
            'total_harga' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        $reservasi->update([
            'dewasa' => $request->dewasa,
            'anak' => $request->anak,
            'nomor_telepon' => $request->nomor_telepon,
            'nama' => $request->nama,
            'total_harga' => $request->total_harga
        ]);

        return new PostResource(true, 'Update Berhasil', $reservasi);
    }
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'from_status' => 'required',
            'to_status' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        $reservasi = Reservasi::where([['id_user', $id], ['status', $request->from_status]])->first();
        if (!$reservasi) {
            return response()->json([
                'message' => 'Data Tidak Ditemukan!'
            ], 200);
        }
        $reservasi->update([
            'status' => $request->to_status
        ]);
        return new PostResource(true, 'Status Berhasil Diubah', $reservasi);
    }

    public function cancel($id)
    {
        $reservasi = Reservasi::find($id);
        if(!$reservasi) {
            return response()->json([
                'message' => 'Data Tidak Ditemukan!'
            ], 200);
        }

        $reservasi->update([
            'status' => 'BATAL'
        ]);
        return new PostResource(true, 'Status Berhasil Diubah', $reservasi);
    }

    public function destroy($id)
    {
        $reservasi = Reservasi::find($id);
        if (!$reservasi) {
            return new PostResource(false, 'Reservasi tidak ditemukan', null);
        }
        $reservasi->delete();

        return new PostResource(true, 'Reservasi Telah Didelete!', null);
    }
}
