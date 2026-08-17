<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class BarangExport implements FromCollection
{
    public function collection(): Collection
    {
        return Barang::select('kode_barang','nama_barang','kategori','jumlah','kondisi','lokasi','tanggal_masuk','foto','dokumen')->get();
    }

    public function map($barang):array{
        return [
            $barang->kode_barang,
            $barang->nama_barang,
            $barang->kategori,
            $barang->jumlah,
            $barang->kondisi,
            $barang->lokasi,
            $barang->tanggal_masuk,
            $barang->foto,
            $barang->dokumen
        ];
    }

    public function headings():array{
        return ['kode_barang','nama_barang','kategori','jumlah','kondisi','lokasi','tanggal_masuk','foto','dokumen'];
    }
}
