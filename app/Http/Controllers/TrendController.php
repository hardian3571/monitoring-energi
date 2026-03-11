<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrendGeneration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrendController extends Controller
{
    public function index(Request $request)
    {
        // 1. FILTER
        $years = range(2024, 2030);
        $selectedYear = $request->input('year', 2025); 
        $selectedMonth = $request->input('month', 'All');

        // 2. QUERY DATA
        $query = TrendGeneration::whereYear('record_date', $selectedYear);
        if ($selectedMonth != 'All') {
            $query->whereMonth('record_date', $selectedMonth);
        }
        $data = $query->orderBy('record_date', 'asc')->get();

        // 3. DATA CHART & TOTAL
        $labels = [];
        $dataSpu = [];
        $dataSs5 = [];
        $indoMonths = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        if ($selectedMonth == 'All') {
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = $indoMonths[$m];
                $monthData = $data->filter(function($row) use ($m) {
                    return Carbon::parse($row->record_date)->format('n') == $m;
                });
                $dataSpu[] = $monthData->sum('kwh_spu');
                $dataSs5[] = $monthData->sum('kwh_ss5');
            }
        } else {
            foreach($data as $row) {
                $labels[] = date('d', strtotime($row->record_date));
                $dataSpu[] = $row->kwh_spu;
                $dataSs5[] = $row->kwh_ss5;
            }
        }

        $totalSpu = $data->sum('kwh_spu');
        $totalSs5 = $data->sum('kwh_ss5');

        // 4. GROUPING DATA UNTUK TABEL
        $monthlyTables = $data->groupBy(function($item) {
            return Carbon::parse($item->record_date)->format('n');
        });

        // Data mentah untuk JS
        $rawData = $data;

        return view('trend.index', compact(
            'years', 'selectedYear', 'selectedMonth', 
            'labels', 'dataSpu', 'dataSs5', 
            'totalSpu', 'totalSs5', 
            'monthlyTables', 'indoMonths', 'rawData'
        ));
    }

    // FUNGSI BARU: TAMBAH DATA MANUAL
    public function store(Request $request)
    {
        $request->validate([
            'record_date' => 'required|date',
            'kwh_spu' => 'required|numeric',
            'kwh_ss5' => 'required|numeric',
        ]);

        // Pakai updateOrCreate biar kalau tanggalnya sudah ada, dia otomatis update (tidak dobel)
        TrendGeneration::updateOrCreate(
            ['record_date' => $request->record_date],
            [
                'kwh_spu' => $request->kwh_spu,
                'kwh_ss5' => $request->kwh_ss5,
                'source_file' => 'Manual Input' // Penanda kalau ini data manual
            ]
        );

        return back()->with('success', 'Data Harian berhasil ditambahkan/diupdate!');
    }

    public function upload(Request $request)
    {
        $request->validate(['csv_file' => 'required']);
        $file = $request->file('csv_file');
        
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            DB::beginTransaction();
            try {
                // Cek Header dulu (Untuk mendeteksi CSV Bersih)
                $header = fgetcsv($handle, 1000, ",");
                
                // === LOGIKA TAMBAHAN: Cek jika ini CSV Format Bersih (record_date,...) ===
                if ($header && isset($header[0]) && trim($header[0]) == 'record_date') {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if (!isset($row[0]) || empty($row[0])) continue;
                        
                        TrendGeneration::updateOrCreate(
                            ['record_date' => $row[0]], 
                            [
                                'kwh_spu' => floatval($row[1] ?? 0), 
                                'kwh_ss5' => floatval($row[2] ?? 0),
                                'source_file' => 'Import CSV Clean'
                            ]
                        );
                    }
                    DB::commit();
                    return back()->with('success', 'Sukses Import CSV Format Standar!');
                }
                
            
                // Reset pointer file karena header sudah dibaca di atas
                rewind($handle);
                
                $detectedMonth = null; $detectedYear = null;
                for ($i = 0; $i < 20; $i++) {
                    $line = fgetcsv($handle, 1000, ",");
                    if ($line === FALSE) break;
                    if (isset($line[3]) && str_contains($line[3], 'Period :')) {
                        $cleanStr = trim(str_replace(["Period :", "\n", "\r"], "", $line[3]));
                        $parts = explode(' ', preg_replace('/\s+/', ' ', $cleanStr));
                        if (count($parts) >= 2) {
                            try {
                                $date = Carbon::parse($parts[1] . '-' . $parts[0] . '-01');
                                $detectedMonth = $date->format('m');
                                $detectedYear = $date->format('Y');
                            } catch (\Exception $e) {}
                        }
                    }
                }
                
                rewind($handle); // Reset lagi untuk baca data
                
                if (!$detectedMonth || !$detectedYear) {
                    // Error message
                    return back()->with('error', 'Gagal deteksi format! Pastikan file adalah Raw Report (ada "Period:") atau CSV Bersih (header record_date).');
                }

                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $tgl = isset($row[2]) ? trim($row[2]) : '';
                    if (is_numeric($tgl) && $tgl >= 1 && $tgl <= 31) {
                        $fullDate = sprintf('%s-%s-%02d', $detectedYear, $detectedMonth, $tgl);
                        $valSpu = isset($row[3]) ? floatval(str_replace(',', '', $row[3])) : 0;
                        $valSs5 = isset($row[6]) ? floatval(str_replace(',', '', $row[6])) : 0;
                        
                        TrendGeneration::updateOrCreate(
                            ['record_date' => $fullDate], 
                            [
                                'kwh_spu' => $valSpu, 
                                'kwh_ss5' => $valSs5,
                                'source_file' => 'Import Raw Report'
                            ]
                        );
                    }
                }
                DB::commit();
                return back()->with('success', "Sukses Import Data: $detectedMonth-$detectedYear");
                
            } catch (\Exception $e) {
                DB::rollback();
                return back()->with('error', 'Error: ' . $e->getMessage());
            }
        }
        return back();
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'year' => 'required', 'month' => 'required', 'spu' => 'array', 'ss5' => 'array',
        ]);
        $year = $request->year; $month = $request->month;
        $spuData = $request->spu; $ss5Data = $request->ss5;

        DB::beginTransaction();
        try {
            foreach ($spuData as $day => $valSpu) {
                $valSs5 = $ss5Data[$day] ?? 0;
                $fullDate = sprintf('%s-%s-%02d', $year, $month, $day);
                TrendGeneration::updateOrCreate(['record_date' => $fullDate], ['kwh_spu' => $valSpu, 'kwh_ss5' => $valSs5]);
            }
            DB::commit();
            return back()->with('success', 'Data berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal Update: ' . $e->getMessage());
        }
    }
    
    public function destroy(Request $request)
    {
        // Cek apakah ada ID yang dikirim
        if ($request->has('ids') && is_array($request->ids)) {
            // Hapus data berdasarkan array ID yang dicentang
            \App\Models\TrendGeneration::whereIn('id', $request->ids)->delete();
            
            return back()->with('success', 'Data yang dipilih berhasil dihapus!');
        }

        return back()->with('error', 'Tidak ada data yang dipilih untuk dihapus.');
    }
}