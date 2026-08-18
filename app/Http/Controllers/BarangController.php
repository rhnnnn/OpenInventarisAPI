<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $barangs = Barang::when($search, function ($query, $search) {
            $query->where('kode_barang', 'like', "%$search%")
                ->orWhere('nama_barang', 'like', "%$search%")
                ->orWhere('kategori', 'like', "%$search%")
                ->orWhere('lokasi', 'like', "%$search%");
        })->get();

        return response()->json([
            'status' => true,
            'message' => $barangs->isEmpty() ? 'data tidak ditemukan' : 'data found!',
            'data' => $barangs   // tetap array, walau kosong
        ], 200);  // ✅ tetap 200, bukan 404
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
                'status'=>false,
                'message'=>'data not found!'
            ],404);
        }
        
    }

    // helper func for filename
    private function generateFileName($file)
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        // ganti spasi jadi underscore, biar aman juga dari karakter aneh lain
        $cleanName = preg_replace('/\s+/', '_', $originalName);
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '', $cleanName);

        // tambahin timestamp biar gak ke-overwrite kalau ada nama file yang sama
        return $cleanName . '_' . time() . '.' . $extension;
    }

    // helper func for kode_barang
    private function generateKodeBarang()
    {
        $lastBarang = Barang::orderBy('id', 'desc')->first();

        if (!$lastBarang) {
            return 'BRG-0001';
        }

        // ambil angka dari kode terakhir, misal "BRG-0007" -> 7
        preg_match('/(\d+)$/', $lastBarang->kode_barang, $matches);
        $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;

        $nextNumber = $lastNumber + 1;

        return 'BRG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validatedBarang = $request->validate([
        'nama_barang'   => 'required',
        'kategori'      => 'required',
        'jumlah'        => 'required|numeric',
        'kondisi'       => 'required',
        'lokasi'        => 'required',
        'tanggal_masuk' => 'required|date',
        'foto'          => 'nullable|image|mimes:png,jpg,jpeg,webp',
        'dokumen'       => 'nullable|file|mimes:pdf,doc,docx,odt',
    ]);

    $fotoPath = null;
    if ($request->hasFile('foto')) {
        $fotoFile = $request->file('foto');
        $fotoName = $this->generateFileName($fotoFile);
        $fotoPath = $fotoFile->storeAs('foto', $fotoName, 'public');
    }

    $dokumenPath = null;
    if ($request->hasFile('dokumen')) {
        $dokumenFile = $request->file('dokumen');
        $dokumenName = $this->generateFileName($dokumenFile);
        $dokumenPath = $dokumenFile->storeAs('dokumen', $dokumenName, 'public');
    }

    $storeBarang = Barang::create([
        'kode_barang'   => $this->generateKodeBarang(), // auto-generate
        'nama_barang'   => $validatedBarang['nama_barang'],
        'kategori'      => $validatedBarang['kategori'],
        'jumlah'        => $validatedBarang['jumlah'],
        'kondisi'       => $validatedBarang['kondisi'],
        'lokasi'        => $validatedBarang['lokasi'],
        'tanggal_masuk' => $validatedBarang['tanggal_masuk'],
        'foto'          => $fotoPath,
        'dokumen'       => $dokumenPath,
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'data stored successfully',
        'data'    => $storeBarang,
    ]);
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
                'foto'=>'nullable|image|mimes:png,jpg,jpeg,webp',
                'dokumen'=>'nullable|file|mimes:pdf,doc,docx,odt'
            ]);

            $barang=Barang::find($id);

            if (!$barang) {
                return response()->json([
                    'status'=>false,
                    'message'=>'data not found'
                ],404);
            }

            $barang->kode_barang=$validatedBarang['kode_barang'];
            $barang->nama_barang=$validatedBarang['nama_barang'];
            $barang->kategori=$validatedBarang['kategori'];
            $barang->jumlah=$validatedBarang['jumlah'];
            $barang->kondisi=$validatedBarang['kondisi'];
            $barang->lokasi=$validatedBarang['lokasi'];
            $barang->tanggal_masuk=$validatedBarang['tanggal_masuk'];

            if ($request->hasFile('foto')) {
                if ($barang->getRawOriginal('foto') && Storage::disk('public')->exists($barang->getRawOriginal('foto'))) {
                    Storage::disk('public')->delete($barang->getRawOriginal('foto'));
                }
                $fotoFile = $request->file('foto');
                $fotoName = $this->generateFileName($fotoFile);
                $barang->foto = $fotoFile->storeAs('foto', $fotoName, 'public');
            }

            if ($request->hasFile('dokumen')) {
                if ($barang->getRawOriginal('dokumen') && Storage::disk('public')->exists($barang->getRawOriginal('dokumen'))) {
                    Storage::disk('public')->delete($barang->getRawOriginal('dokumen'));
                }
                $dokumenFile = $request->file('dokumen');
                $dokumenName = $this->generateFileName($dokumenFile);
                $barang->dokumen = $dokumenFile->storeAs('dokumen', $dokumenName, 'public');
            }

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
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'status'  => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }

        // hapus foto dari storage kalau ada
        if ($barang->getRawOriginal('foto') && Storage::disk('public')->exists($barang->getRawOriginal('foto'))) {
            Storage::disk('public')->delete($barang->getRawOriginal('foto'));
        }

        // hapus dokumen dari storage kalau ada
        if ($barang->getRawOriginal('dokumen') && Storage::disk('public')->exists($barang->getRawOriginal('dokumen'))) {
            Storage::disk('public')->delete($barang->getRawOriginal('dokumen'));
        }

        $barang->delete();

        return response()->json([
            'status'  => true,
            'message' => 'data deleted successfully',
        ]);
    }

    public function exportxlsx(){
        return Excel::download(new BarangExport,'data_barang.xlsx');
    }

    public function exportcsv(){
        return Excel::download(new BarangExport,'data_barang.csv');
    }
}
