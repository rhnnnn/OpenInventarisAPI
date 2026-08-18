<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class BarangExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection() : Collection
    {
        $query = Barang::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('kode_barang', 'like', "%{$this->search}%")
                  ->orWhere('nama_barang', 'like', "%{$this->search}%")
                  ->orWhere('kategori', 'like', "%{$this->search}%")
                  ->orWhere('lokasi', 'like', "%{$this->search}%");
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Jumlah',
            'Kondisi',
            'Lokasi',
            'Tanggal Masuk',
        ];
    }

    public function map($barang): array
    {
        return [
            $barang->kode_barang,
            $barang->nama_barang,
            $barang->kategori,
            $barang->jumlah,
            $barang->kondisi,
            $barang->lokasi,
            $barang->tanggal_masuk,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // jumlah kolom = 7 (A sampai G), sesuai jumlah heading
                $lastColumn = 'G';

                // ==========================================
                // 1. Sisipkan 1 baris kosong di paling atas
                //    biar ada tempat buat judul, geser semua ke bawah
                // ==========================================
                $sheet->insertNewRowBefore(1, 1);

                // ==========================================
                // 2. Isi judul di baris 1, merge dari A1 sampai G1
                // ==========================================
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->setCellValue('A1', 'Data Barang Inventaris – OpenInventaris');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                // ==========================================
                // 3. Styling baris header (sekarang jadi baris 2,
                //    karena baris 1 dipakai judul)
                // ==========================================
                $headerRow = 2;
                $headerRange = "A{$headerRow}:{$lastColumn}{$headerRow}";

                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFFF00'); // kuning

                // ==========================================
                // 4. Kasih border tipis ke seluruh tabel data
                //    (dari baris header sampai baris data terakhir)
                // ==========================================
                $lastDataRow = $headerRow + $this->collection()->count();
                $sheet->getStyle("A{$headerRow}:{$lastColumn}{$lastDataRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // ==========================================
                // 5. Auto-size lebar kolom biar gak kepotong
                // ==========================================
                foreach (range('A', $lastColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}