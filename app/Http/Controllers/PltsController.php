<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PltsGeneration;
use Illuminate\Support\Facades\DB;

class PltsController extends Controller
{
    public function index(Request $request)
    {
        // 1. Setup Data Area
        $dbLocations = PltsGeneration::distinct()->pluck('plant_name')->toArray();
        $phase1Def = ['KANTOR PUSAT', 'AREA DIKLAT', 'KANTIN'];
        $phase2Def = ['AREA PP&P', 'AREA GUDANG KCL FLAKE', 'AREA LABORATORIUM', 'AREA MANSET'];
        $allLocations = array_unique(array_merge($phase1Def, $phase2Def, $dbLocations));
        sort($allLocations);

        // 2. Filter Input
        $selectedYear = $request->input('year', 'All Years'); // Default All Years
        $selectedPhase = $request->input('phase', 'All Phases');
        $selectedLocation = $request->input('location', 'All Locations');
        $dropdownYears = range(2021, 2030);

        // 3. Query Data Mentah
        $query = PltsGeneration::query();
        
        // A. Filter Phase & Location
        if ($selectedPhase != 'All Phases' && $selectedLocation == 'All Locations') {
            $targetAreas = ($selectedPhase == 'Phase 1') ? $phase1Def : $phase2Def;
            $query->whereIn('plant_name', $targetAreas);
        }
        if ($selectedLocation != 'All Locations') {
            $query->where('plant_name', $selectedLocation);
        }

        // B. FILTER TAHUN (INI PERBAIKANNYA BOS!)
        // Kalau user pilih tahun (Bukan 'All Years'), kita filter datanya sekarang juga.
        if ($selectedYear != 'All Years') {
            $query->whereYear('record_date', $selectedYear);
        }
        
        // Ambil Data (Sekarang isinya sudah difilter sesuai tahun)
        $rawData = $query->orderBy('record_date', 'asc')->get();
        
        // 4. Hitung Data Grafik (Grouped Bar)
        $targetYears = ($selectedYear == 'All Years') ? $dropdownYears : [$selectedYear];

        // A. Main Chart Data
        $mainChartSeries = [];
        foreach($targetYears as $y) {
            $monthlyData = [];
            $yearHasData = false;
            for($m=1; $m<=12; $m++) {
                // Filter dari $rawData yang sudah bersih
                $sum = $rawData->filter(function($d) use ($y, $m) {
                    return date('Y', strtotime($d->record_date)) == $y && date('n', strtotime($d->record_date)) == $m;
                })->sum('kwh_generated');
                $monthlyData[] = $sum;
                if($sum > 0) $yearHasData = true;
            }
            // Tampilkan di grafik jika ada data, atau jika user memang memilih tahun itu
            if ($yearHasData || $selectedYear != 'All Years') {
                $mainChartSeries[$y] = $monthlyData;
            }
        }

        // B. Area Chart Data
        $areaChartSeries = [];
        foreach($allLocations as $area) {
            // Ambil data spesifik area dari rawData
            $areaSpecificData = $rawData->where('plant_name', $area);
            
            // Cek apakah area ini punya data di tahun/filter terpilih?
            if($areaSpecificData->isEmpty()) continue; 

            foreach($targetYears as $y) {
                $monthlyData = [];
                $yearHasData = false;
                for($m=1; $m<=12; $m++) {
                    $sum = $areaSpecificData->filter(function($d) use ($y, $m) {
                        return date('Y', strtotime($d->record_date)) == $y && date('n', strtotime($d->record_date)) == $m;
                    })->sum('kwh_generated');
                    $monthlyData[] = $sum;
                    if($sum > 0) $yearHasData = true;
                }
                if ($yearHasData || $selectedYear != 'All Years') {
                    $areaChartSeries[$area][$y] = $monthlyData;
                }
            }
        }

        // 5. HITUNG STATISTIK 
        // Karena $rawData sudah difilter di atas, kita bisa langsung pakai $rawData
        $totalKwh = $rawData->sum('kwh_generated');
        
        // Hitung rata-rata
        // Kalau pilih 'All Years' (akumulasi), kita bagi jumlah bulan data yang ada
        // Kalau pilih Tahun tertentu, bagi 12
        if($selectedYear != 'All Years') {
            $avgBulanan = $totalKwh / 12;
        } else {
            // Hitung ada berapa bulan data yang masuk
            $countMonth = $rawData->groupBy(function($d) { return substr($d->record_date, 0, 7); })->count();
            $avgBulanan = $countMonth > 0 ? $totalKwh / $countMonth : 0;
        }
        
        $maxRec = $rawData->sortByDesc('kwh_generated')->first();
        $bestMonthName = ($maxRec) ? date('F Y', strtotime($maxRec->record_date)) : '-';

        $hasData = $totalKwh > 0;

        $areaPhaseMap = [];
        foreach($allLocations as $loc) {
            $areaPhaseMap[$loc] = in_array($loc, $phase1Def) ? 'Phase 1' : 'Phase 2';
        }

        return view('plts.index', compact(
            'allLocations', 'selectedLocation', 'selectedYear', 'selectedPhase', 'dropdownYears',
            'mainChartSeries', 'areaChartSeries', 'totalKwh', 'avgBulanan', 'bestMonthName', 
            'rawData', 'areaPhaseMap', 'hasData',
            'targetYears' // <--- INI WAJIB DITAMBAH BOS (Array tahun yang mau ditampilkan)
        ));
    }
    public function bulkUpdate(Request $request)
    {
        $request->validate(['plant_name'=>'required', 'year'=>'required', 'kwh'=>'required|array']);
        DB::beginTransaction();
        try {
            $phase1Def = ['KANTOR PUSAT', 'AREA DIKLAT', 'KANTIN'];
            $phase = in_array(strtoupper($request->plant_name), $phase1Def) ? 'Phase 1' : 'Phase 2';
            foreach ($request->kwh as $monthIndex => $val) {
                $kwhValue = ($val === null || $val === '') ? 0 : floatval(str_replace(',', '', $val));
                PltsGeneration::updateOrCreate(
                    ['plant_name' => $request->plant_name, 'record_date' => sprintf('%s-%02d-01', $request->year, $monthIndex+1)],
                    ['kwh_generated' => $kwhValue, 'phase_group' => $phase, 'source_name' => 'Bulk Edit']
                );
            }
            DB::commit(); return back()->with('success', 'Data Tersimpan!');
        } catch (\Exception $e) { DB::rollback(); return back()->with('error', $e->getMessage()); }
    }

    public function destroy(Request $request)
{
    $ids = $request->ids;
    if($ids && count($ids) > 0) {
        \App\Models\PltsGeneration::whereIn('id', $ids)->delete();
        return back()->with('success', count($ids) . ' Data berhasil dihapus!');
    }
    return back()->with('error', 'Tidak ada data yang dipilih.');
}

    public function upload(Request $request) 
    {
        $request->validate(['csv_file'=>'required', 'year'=>'required']);
        $file = $request->file('csv_file');
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            fgetcsv($handle, 1000, ","); 
            DB::beginTransaction();
            try {
                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $plantName = trim($row[1]); if(empty($plantName)) continue;
                    $phase = in_array(strtoupper($plantName), ['KANTOR PUSAT', 'AREA DIKLAT', 'KANTIN']) ? 'Phase 1' : 'Phase 2';
                    for ($m=1; $m<=12; $m++) {
                        $kwh = isset($row[$m+1]) ? floatval(str_replace(',', '', $row[$m+1])) : 0;
                        if ($kwh > 0) PltsGeneration::updateOrCreate(['record_date' => sprintf('%s-%02d-01', $request->year, $m), 'plant_name' => $plantName], ['kwh_generated' => $kwh, 'phase_group' => $phase]);
                    }
                }
                DB::commit(); return back()->with('success', 'Import Berhasil');
            } catch (\Exception $e) { DB::rollback(); return back()->with('error', 'Gagal: ' . $e->getMessage()); }
        }
        return back();
    }
}