<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListrikSs4; 
use Illuminate\Support\Facades\DB;

class ListrikSs4Controller extends Controller
{
    public function index(Request $request)
    {
        // 1. SETUP TAHUN
        $startYear = 2021;
        $endYear = 2030;
        $dropdownYears = range($startYear, $endYear);
        
        $selectedYear = $request->input('year', 'All Years');

        // 2. QUERY DATA
        $query = ListrikSs4::query();
        
        // --- LOGIKA FILTER ---
        if ($selectedYear != 'All Years') {
            // Kalau pilih tahun spesifik, ambil tahun itu saja
            $query->whereYear('record_date', $selectedYear);
            $targetYears = [$selectedYear];
        } else {
            // Kalau All Years, ambil range 2021-2030
            $query->whereYear('record_date', '>=', $startYear)
                  ->whereYear('record_date', '<=', $endYear);
            $targetYears = $dropdownYears;
        }
        
        $rawData = $query->orderBy('record_date', 'asc')->get();

        // 3. SIAPKAN STRUKTUR DATA
        $chartSeries = [];
        $matrix = [];
        $tableData = []; 
        $totalPerTahun = [];

        // Init Array Kosong
        foreach($targetYears as $yr) {
            $totalPerTahun[$yr] = 0;
            $chartSeries[$yr] = array_fill(0, 12, 0); 
            // Init Matrix (Hanya dipake kalau All Years)
            for($m=1; $m<=12; $m++) {
                $matrix[$m][$yr] = ['val' => 0, 'id' => null];
            }
        }

        // PENGISIAN DATA
        
        // A. Jika Mode Detail (Tahun Spesifik)
        if ($selectedYear != 'All Years') {
            $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            
            foreach($months as $idx => $mName) {
                $monthNum = $idx + 1;
                // Cari data di bulan ini (PHP 7.3 Compatible)
                $row = $rawData->first(function($item) use ($monthNum) {
                    return date('n', strtotime($item->record_date)) == $monthNum;
                });

                $pkt = $row ? $row->kwh_pkt : 0;
                $kdm = $row ? $row->kwh_kdm : 0;
                $tot = $pkt + $kdm;

                $tableData[] = [
                    'id'    => $row ? $row->id : null,
                    'label' => $mName,
                    'pkt'   => $pkt,
                    'kdm'   => $kdm,
                    'total' => $tot
                ];

                $chartSeries[$selectedYear][$idx] = $tot;
            }
        } 
        
        // B. Jika Mode Matrix (All Years)
        else {
            foreach($rawData as $row) {
                $y = date('Y', strtotime($row->record_date));
                $m = date('n', strtotime($row->record_date)); 
                $mIdx = $m - 1; 
                $val = $row->kwh_pkt + $row->kwh_kdm; 

                if(isset($matrix[$m][$y])) {
                    $matrix[$m][$y] = ['val' => $val, 'id' => $row->id];
                }
                if(isset($chartSeries[$y])) {
                    $chartSeries[$y][$mIdx] = $val;
                }
                if(isset($totalPerTahun[$y])) {
                    $totalPerTahun[$y] += $val;
                }
            }
        }

        // 4. STATISTIK
        $grandTotal = 0;
        if ($selectedYear != 'All Years') {
            // PHP 7.3: array_column belum tentu support object collection langsung di versi lama tertentu, 
            // tapi karena $tableData array murni, ini aman.
            $grandTotal = array_sum(array_column($tableData, 'total'));
        } else {
            $grandTotal = array_sum($totalPerTahun);
        }
        
        // Hitung Rata-rata
        $divider = 0;
        if ($selectedYear != 'All Years') {
            // PERBAIKAN DI SINI UNTUK PHP 7.3
            // Ganti fn($v) => $v > 0 dengan function($v) { return $v > 0; }
            $divider = count(array_filter(array_column($tableData, 'total'), function($v) {
                return $v > 0;
            }));
        } else {
            foreach($chartSeries as $yr => $months) {
                foreach($months as $val) { if($val > 0) $divider++; }
            }
        }
        $avgBulanan = $divider > 0 ? $grandTotal / $divider : 0;

        // Cari Tertinggi
        $bestMonthName = '-';
        $maxVal = 0;
        
        if ($selectedYear != 'All Years') {
            foreach($tableData as $row) {
                if($row['total'] > $maxVal) { $maxVal = $row['total']; $bestMonthName = $row['label']; }
            }
        } else {
            $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            foreach($matrix as $m => $yearsData) {
                foreach($yearsData as $y => $data) {
                    if($data['val'] > $maxVal) { $maxVal = $data['val']; $bestMonthName = $monthNames[$m-1] . " " . $y; }
                }
            }
        }

        $hasData = $grandTotal > 0;

        return view('listrik-ss4.index', compact(
            'dropdownYears', 'selectedYear', 'grandTotal', 'avgBulanan', 'bestMonthName',
            'chartSeries', 'matrix', 'tableData', 'hasData', 'rawData', 'totalPerTahun'
        ));
    }

    // FUNGSI UPDATE/UPLOAD/DELETE
    public function destroy(Request $request) {
        $ids = $request->ids;
        if($ids && count($ids) > 0) {
            ListrikSs4::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . ' Data berhasil dihapus!');
        }
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

   public function upload(Request $request) 
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
            'year' => 'required'
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        // 1. Deteksi Delimiter
        $handle = fopen($path, 'r');
        $firstLine = fgets($handle);
        fclose($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

        // 2. Baca File
        $rows = array_map(function($line) use ($delimiter) {
            return str_getcsv($line, $delimiter);
        }, file($path));

        // 3. Ambil Header
        $header = array_shift($rows);
        $header = array_map(function($h) {
            return strtolower(trim(str_replace(['"', "'"], '', $h))); 
        }, $header);

        // 4. Cari Index Kolom
        $idxDate = array_search('tanggal', $header);
        if($idxDate === false) $idxDate = array_search('bulan', $header);
        
        $idxPkt = array_search('pkt', $header);
        if($idxPkt === false) $idxPkt = array_search('kwh_pkt', $header);

        $idxKdm = array_search('kdm', $header);
        if($idxKdm === false) $idxKdm = array_search('kwh_kdm', $header);

        // Validasi
        if ($idxDate === false || $idxPkt === false || $idxKdm === false) {
            return back()->with('error', 'Format CSV Salah! Header wajib ada: Bulan, PKT, KDM');
        }

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($rows as $row) {
                if (count($row) < 3) continue;

                $rawDate = trim($row[$idxDate]);
                $monthNum = 0;

                // Parsing Bulan (Jan-25, Januari, dll)
                if (preg_match('/[a-zA-Z]/', $rawDate)) {
                     $mStr = strtolower($rawDate);
                     $months = ['jan'=>1, 'feb'=>2, 'mar'=>3, 'apr'=>4, 'mei'=>5, 'may'=>5, 'jun'=>6, 'jul'=>7, 'agu'=>8, 'aug'=>8, 'sep'=>9, 'okt'=>10, 'oct'=>10, 'nov'=>11, 'des'=>12, 'dec'=>12];
                     foreach($months as $k => $v) { 
                        if(strpos($mStr, $k) !== false) { $monthNum = $v; break; } 
                     }
                } else {
                    try {
                        $d = \Carbon\Carbon::parse(str_replace('/', '-', $rawDate));
                        $monthNum = $d->month;
                    } catch (\Exception $e) { $monthNum = 0; }
                }

                if ($monthNum > 0) {
                   
                    
                    // Ambil nilai mentah
                    $rawPkt = $row[$idxPkt];
                    $rawKdm = $row[$idxKdm];

                    // Fungsi pembersih Format Indonesia (1.000.000,00)
                    // 1. Hapus titik ribuan (.)
                    // 2. Ganti koma desimal (,) jadi titik (.)
                    
                    $cleanPkt = str_replace('.', '', $rawPkt); // Hapus titik (1.752.950 -> 1752950)
                    $cleanPkt = str_replace(',', '.', $cleanPkt); // Koma jadi titik (jika ada desimal)

                    $cleanKdm = str_replace('.', '', $rawKdm);
                    $cleanKdm = str_replace(',', '.', $cleanKdm);

                    $pkt = (float) $cleanPkt;
                    $kdm = (float) $cleanKdm;

                    ListrikSs4::updateOrCreate(
                        ['record_date' => sprintf('%s-%02d-01', $request->year, $monthNum)],
                        ['kwh_pkt' => $pkt, 'kwh_kdm' => $kdm]
                    );
                    $count++;
                }
            }
            
            DB::commit();
            return back()->with('success', 'Berhasil import ' . $count . ' data. Total Jutaan kWh aman!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    public function bulkUpdate(Request $request) {
        $request->validate(['year'=>'required', 'pkt'=>'array', 'kdm'=>'array']);
        DB::beginTransaction();
        try {
            foreach ($request->pkt as $idx => $valPkt) {
                $pkt = ($valPkt === null) ? 0 : floatval($valPkt);
                $kdm = ($request->kdm[$idx] === null) ? 0 : floatval($request->kdm[$idx]);
                ListrikSs4::updateOrCreate(
                    ['record_date' => sprintf('%s-%02d-01', $request->year, $idx+1)],
                    ['kwh_pkt' => $pkt, 'kwh_kdm' => $kdm]
                );
            }
            DB::commit(); return back()->with('success', 'Data Tersimpan!');
        } catch (\Exception $e) { DB::rollback(); return back()->with('error', $e->getMessage()); }
    }
}