<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use App\Models\Barang;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barangs=Barang::all();

        return response()->json([
            'status'=>true,
            'message'=>'data found!',
            'data'=>$barangs
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $barangs=Barang::find($id);

        if ($barangs) {
            return response()->json([
                'status'=>true,
                'message'=>'data found!',
                'data'=>$barangs
            ],200);
        } else {
            return response()->json([
                'status'=>true,
                'message'=>'data not found!'
            ],404);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedBarang=$request->validate([
            'kode_barang'=>'required',
            'nama_barang'=>'required',
            'kategori'=>'required',
            'jumlah'=>'required|numeric',
            'kondisi'=>'required',
            'lokasi'=>'required',
            'tanggal_masuk'=>'required|date',
            'foto'=>'nullable',
            'dokumen'=>'nullable'
        ]);

        $storeBarang=Barang::create([
            'kode_barang'=>$validatedBarang['kode_barang'],
            'nama_barang'=>$validatedBarang['nama_barang'],
            'kategori'=>$validatedBarang['kategori'],
            'jumlah'=>$validatedBarang['jumlah'],
            'kondisi'=>$validatedBarang['kondisi'],
            'lokasi'=>$validatedBarang['lokasi'],
            'tanggal_masuk'=>$validatedBarang['tanggal_masuk'],
            'foto'=>$validatedBarang['foto'],
            'dokumen'=>$validatedBarang['dokumen']
        ]);

        if ($storeBarang) {
            return response()->json([
                'status'=>true,
                'message'=>'data stored successfully',
                'storedData'=>$validatedBarang
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if ($request->isMethod('post')) {
            $validatedBarang=$request->validate([
                'kode_barang'=>'required',
                'nama_barang'=>'required',
                'kategori'=>'required',
                'jumlah'=>'required|numeric',
                'kondisi'=>'required',
                'lokasi'=>'required',
                'tanggal_masuk'=>'required|date',
                'foto'=>'nullable',
                'dokumen'=>'nullable'
            ]);
            $barang=Barang::find($id);
            $barang->kode_barang=$validatedBarang['kode_barang'];
            $barang->nama_barang=$validatedBarang['nama_barang'];
            $barang->kategori=$validatedBarang['kategori'];
            $barang->jumlah=$validatedBarang['jumlah'];
            $barang->kondisi=$validatedBarang['kondisi'];
            $barang->lokasi=$validatedBarang['lokasi'];
            $barang->tanggal_masuk=$validatedBarang['tanggal_masuk'];
            $barang->foto=$validatedBarang['foto'];
            $barang->dokumen=$validatedBarang['dokumen'];
            $barang->update();

            return response()->json([
                'status'=>true,
                'message'=>'data updated successfully',
                'storedData'=>$validatedBarang
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $barang=Barang::findOrFail($id);
        $barang->delete();

        return response()->json([
            'status'=>true,
            'message'=>'data deleted successfully',
        ]);
    }

    public function exportxlsx(){
        return Excel::download(new BarangExport,'data_barang.xlsx');
    }

    public function exportcsv(){
        return Excel::download(new BarangExport,'data_barang.csv');
    }
}
