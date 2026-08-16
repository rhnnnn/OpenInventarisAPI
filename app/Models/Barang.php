<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $fillable = ['kode_barang','nama_barang','kategori','jumlah','kondisi','lokasi','tanggal_masuk','foto','dokumen'];
}
