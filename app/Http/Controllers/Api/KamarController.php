<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Http\Resources\PostResource;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class KamarController extends Controller
{
    public function index()
    {
        $kamars = Kamar::latest()->paginate(5);

        return new PostResource(true, 'List Data Kamar', $kamars);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'nomor_kamar' => 'required|unique:kamars',
            'min_pax' => 'required',
            'max_pax' => 'required',
            'tipe' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/kamars', $image->hashName());

        $kamar = Kamar::create([
            'image' => $image->hashName(),
            'nomor_kamar' => $request->nomor_kamar,
            'min_pax' => $request->min_pax,
            'max_pax' => $request->max_pax,
            'tipe' => $request->tipe
        ]);

        return new PostResource(true, 'Data Kamar Berhasil Ditambahkan!', $kamar);
    }

    public function show($id)
    {
        $kamar = Kamar::find($id);

        return new PostResource(true, 'Detail Data Kamar', $kamar);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nomor_kamar' => 'required',
            'min_pax' => 'required',
            'max_pax' => 'required',
            'tipe' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        $kamar = Kamar::find($id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            Storage::delete('public/kamars/'.basename($kamar->image));

            $kamar->update([
                'image' => $image->hashName(),
                'nomor_kamar' => $request->nomor_kamar, 
                'min_pax' => $request->min_pax,
                'max_pax' => $request->max_pax,
                'tipe' => $request->tipe
            ]);
        } else {
            $kamar->update([
                'nomor_kamar'   => $request->nomor_kamar, 
                'min_pax' => $request->min_pax,
                'max_pax' => $request->max_pax,
                'tipe' => $request->tipe
            ]);
        }
        return new PostResource(true, 'Data Kamar Berhasil Diubah!', $kamar);
    }

    public function destroy($id) {
        $kamar = Kamar::find($id);

        Storage::delete('public/kamars/'.basename($kamar->image));

        $kamar->delete();

        return new PostResource(true, 'Data Kamar Berhasil Dihapus!', null);
    }
}
