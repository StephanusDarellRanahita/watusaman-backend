<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\ReservasiResource;
use App\Http\Resources\PaymentResource;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\Payment;
use App\Models\Reservasi;
use App\Models\User;
use App\Models\UserDana;

class PaymentsController extends Controller
{
    public function index()
    {
        $payments = Payment::latest()->paginate(5);

        return new PostResource(true, 'List Data Payment', $payments);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required',
            'id_reservasi' => 'required',
            'total_bayar' => 'required',
            'tipe' => 'required',
            'nomor_telepon' => 'required',
            'pin' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }
        $user = User::find($request->id_user);
        if (!$user) {
            return response()->json([
                'message' => 'User Not Found!'
            ], 404);
        }

        $reservasi = Reservasi::find($request->id_reservasi);
        if (!$reservasi) {
            return response()->json([
                'message' => 'Reservasi Not Found!'
            ], 404);
        }
        
        $userDana = UserDana::where('nomor_telepon',$request->nomor_telepon)->first();
        if(!Hash::check($request->pin,$userDana['pin'])) {
            return response()->json([
                'message' => 'Pin Salah!'
            ], 401);
        }
        if($userDana->balance < $request->total_bayar) {
            return response()->json([
                'message' => 'Saldo Tidak Mencukupi!'
            ], 401);
        }
        $newBalance = $userDana->balance - $request->total_bayar;
        $userDana->update([
            'balance' => $newBalance
        ]);
        
        $sisa = $reservasi->total_harga - $request->total_bayar;
        $reservasi->update([
            'status' => 'INVOICE',
            'sisa_pembayaran' => $sisa
        ]);
        $payment = Payment::create([
            'id_user' => $request->id_user,
            'id_reservasi' => $request->id_reservasi,
            'total_bayar' => $request->total_bayar,
            'status' => $reservasi->status,
            'tipe' => $request->tipe
        ]);

        $payment->load('user', 'reservasi');

        return new PaymentResource(true, 'Payment Berhasil! Silahkan Tunggu Konfirmasi Dari Admin', $payment);
    }
}
