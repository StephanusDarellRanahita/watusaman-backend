<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\ReservasiResource;
use App\Http\Resources\PaymentResource;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Payment;
use App\Models\Reservasi;
use App\Models\UserDana;

class DanaPaymentsController extends Controller
{
    public function userDana(Request $request) {
        $validator = Validator::make($request->all(), [
            'nomor_telepon' => 'required'
        ]);
        if($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }
        $user = UserDana::where('nomor_telepon',$request->nomor_telepon)->first();
        if(!$user) {
            return response()->json([
                'message' => 'User Not Found!'
            ], 404);
        }

        return new PostResource(true, 'User Ditemukan!', $user);
    }
}
