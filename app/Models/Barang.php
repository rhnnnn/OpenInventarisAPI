<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $fillable = ['kode_barang','nama_barang','kategori','jumlah','kondisi','lokasi','tanggal_masuk','foto','dokumen'];

    protected function foto(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset('storage/' . $value) : null,
        );
    }

    protected function dokumen(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset('storage/' . $value) : null,
        );
    }
}
