<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use Twilio\Rest\Client;

use App\Http\Resources\PostResource;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WhatsAppController extends Controller
{
    public function sendMessage(Request $request)
    {

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $number = env('TWILIO_WHATSAPP_NUMBER');
        $twilio = new Client($sid, $token);

        $messageBody = "Reservasi Hotel ($request->start_date - $request->end_date) \n\n";
        $messageBody .= "Nama\t\t: $request->nama \n";
        $messageBody .= "Nomor Telepon\t: $request->nomor_telepon \n";
        $messageBody .= "Dewasa\t\t: $request->dewasa \n";
        $messageBody .= "Anak\t\t: $request->anak \n";
        $messageBody .= "Melakukan Reservasi \n";

        $message = $twilio->messages->create(
            "whatsapp:+6281225542701",
            [
                "from" => $number,
                "body" => $messageBody
            ]
        );

        return "Message sent successfully!";
    }
    public function sendCancel($id)
    {
        $reservasi = Reservasi::find($id);

        if (!$reservasi) {
            return response()->json([
                'message' => 'Reservasi Tidak Ditemukan'
            ],422);
        }

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $number = env('TWILIO_WHATSAPP_NUMBER');
        $twilio = new Client($sid, $token);


        $messageBody = "Reservasi Hotel ($reservasi->start_date - $reservasi->end_date) \n\n";
        $messageBody .= "Nama\t\t: $reservasi->nama \n";
        $messageBody .= "Nomor Telepon\t: $reservasi->nomor_telepon \n";
        $messageBody .= "Dewasa\t\t: $reservasi->dewasa \n";
        $messageBody .= "Anak\t\t: $reservasi->anak \n";
        $messageBody .= "Melakukan Batal Reservasi \n";

        $message = $twilio->messages->create(
            "whatsapp:+6281225542701",
            [
                "from" => $number,
                "body" => $messageBody
            ]
        );

        return response()->json([
            'message' => "Pembatalan sedang diajukan!" 
        ]);
    }
}
