<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PltsGeneration;
use App\Models\ListrikSs4;
use App\Models\TrendGeneration;
use App\Models\User; 
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. UPDATE STATUS LOGIN
        if (auth()->check()) {
            auth()->user()->touch(); 
        }

        // 2. SETUP FILTER (TAHUN & BULAN)
        $startYear = 2015;
        $endYear = 2030;
        $dropdownYears = range($startYear, $endYear); // Variable untuk dropdown
        $years = $dropdownYears; // Variable alias untuk view trend
        
        $selectedYear = $request->input('year', 2025);
        $selectedMonth = $request->input('month', 'All');

        // ==========================================
        // BAGIAN 1: LOGIKA TREND MULTI (Untuk Mengatasi Error)
        // ==========================================
        $queryTrend = TrendGeneration::whereYear('record_date', $selectedYear);
        
        if ($selectedMonth != 'All') {
            $queryTrend->whereMonth('record_date', $selectedMonth);
        }
        
        $trendData = $queryTrend->orderBy('record_date', 'asc')->get();

        // Hitung Total Trend
        $totalSpu = $trendData->sum('kwh_spu');
        $totalSs5 = $trendData->sum('kwh_ss5');

        // Grouping Data Harian per Bulan (Untuk Tabel Rinci)
        $monthlyTables = $trendData->groupBy(function($item) {
            return Carbon::parse($item->record_date)->format('n');
        });

        // Data Mentah untuk JS & Tabel
        $rawData = $trendData; 
        
        // List Nama Bulan Indonesia
        $indoMonths = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];


        // ==========================================
        // BAGIAN 2: LOGIKA LAMA (PLTS & SS4)
        // ==========================================
        
        // Setup Target Years untuk Loop
        if ($selectedYear != 'All Years' && is_numeric($selectedYear)) {
            $targetYears = [$selectedYear];
        } else {
            $targetYears = $dropdownYears;
        }

        // Query Data Energi Lama
        $queryPlts = PltsGeneration::query();
        $querySs4 = ListrikSs4::query();

        if (is_numeric($selectedYear)) {
            $queryPlts->whereYear('record_date', $selectedYear);
            $querySs4->whereYear('record_date', $selectedYear);
        } else {
            $queryPlts->whereYear('record_date', '>=', $startYear)->whereYear('record_date', '<=', $endYear);
            $querySs4->whereYear('record_date', '>=', $startYear)->whereYear('record_date', '<=', $endYear);
        }

        $rawPlts = $queryPlts->get();
        $rawSs4 = $querySs4->get();

        // Siapkan Data Chart Lama
        $pltsPerMonth = array_fill(0, 12, 0);
        $ss4PerMonth  = array_fill(0, 12, 0);
        $totalPlts = 0;
        $totalSs4 = 0;

        foreach ($rawPlts as $row) {
            $y = date('Y', strtotime($row->record_date));
            if (in_array($y, $targetYears)) {
                $mIdx = date('n', strtotime($row->record_date)) - 1; 
                $pltsPerMonth[$mIdx] += $row->kwh_generated;
                $totalPlts += $row->kwh_generated;
            }
        }

        foreach ($rawSs4 as $row) {
            $y = date('Y', strtotime($row->record_date));
            if (in_array($y, $targetYears)) {
                $mIdx = date('n', strtotime($row->record_date)) - 1;
                $val = $row->kwh_pkt + $row->kwh_kdm;
                $ss4PerMonth[$mIdx] += $val;
                $totalSs4 += $val;
            }
        }

        $grandTotal = $totalPlts + $totalSs4;


        // ==========================================
        // BAGIAN 3: DATA USER LOGIN
        // ==========================================
        $recentUsers = User::whereNotNull('updated_at')
                            ->orderBy('updated_at', 'desc')
                            ->take(5)
                            ->get();

        // KIRIM SEMUA VARIABLE KE VIEW
        return view('dashboard', compact(
            // Data Filter
            'dropdownYears', 'years', 'selectedYear', 'selectedMonth', 'indoMonths',
            
            // Data Trend Baru (Penyebab Error)
            'rawData', 'monthlyTables', 'totalSpu', 'totalSs5',
            
            // Data PLTS & SS4 Lama
            'pltsPerMonth', 'ss4PerMonth', 
            'totalPlts', 'totalSs4', 'grandTotal',
            
            // Data User
            'recentUsers'
        ));
    }
}