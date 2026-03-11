@extends('layouts.app')

@section('content')

@php
    // 1. Setup Data Tahun & Matrix
    $colsYear = isset($targetYears) ? $targetYears : ($selectedYear == 'All Years' ? $dropdownYears : [$selectedYear]);

    $matrix = [];
    foreach($rawData as $row) {
        $thn = date('Y', strtotime($row->record_date));
        $bln = date('n', strtotime($row->record_date));
        $matrix[$row->plant_name][$bln][$thn] = [
            'val' => $row->kwh_generated,
            'id'  => $row->id
        ];
    }
    
    // 2. Filter Lokasi
    if ($selectedPhase == 'Phase 1') { 
        $dropdownLocations = array_filter($allLocations, function($loc) use ($areaPhaseMap) { return $areaPhaseMap[$loc] == 'Phase 1'; });
    } elseif ($selectedPhase == 'Phase 2') { 
        $dropdownLocations = array_filter($allLocations, function($loc) use ($areaPhaseMap) { return $areaPhaseMap[$loc] == 'Phase 2'; });
    } else { 
        $dropdownLocations = $allLocations; 
    }
@endphp

<script>
    window.mainChartSeries = @json($mainChartSeries);
    window.areaChartSeries = @json($areaChartSeries);
    window.dbData = @json($rawData);
    window.chartColors = ['#f97316', '#94a3b8', '#facc15', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6', '#db2777', '#f43f5e', '#6366f1'];
</script>

<style>
    body.preload * { transition: none !important; }
    
    /* STYLE DASAR */
    .res-header { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0; flex-wrap: wrap; gap: 15px; }
    .res-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px; }
    
    /* TABLE BLOCK STYLING */
    .area-block { margin-bottom: 30px; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    
    /* HEADER TABEL RESPONSIF */
    .area-header { 
        padding: 12px 20px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        border-bottom: 1px solid #e2e8f0; 
        background: #fff; 
        flex-wrap: wrap; /* Agar tombol turun ke bawah di HP */
        gap: 10px;
    }
    
    .area-title { font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
    .area-actions { display: flex; gap: 5px; flex-wrap: wrap; }

    /* TABLE CONTENT */
    .main-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.85rem; }
    .main-table th { background: #1e293b; color: white; padding: 10px; text-align: center; border-bottom: 2px solid #0f172a; vertical-align: middle; white-space: nowrap; }
    .main-table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; text-align: center; vertical-align: middle; }
    .main-table td:first-child { text-align: left; background: #fff; position: sticky; left:0; font-weight: 600; color: #475569; border-right: 2px solid #f1f5f9; z-index: 10; min-width: 120px; }
    
    /* CHECKBOX COLUMN (Hidden by Default) */
    .col-check { display: none; width: 40px; text-align: center; background: #f8fafc; z-index: 11; }
    .chk-item { width: 16px; height: 16px; cursor: pointer; }
    .chk-head { width: 18px; height: 18px; cursor: pointer; accent-color: #ef4444; }

    /* Buttons */
    .btn-action { padding: 6px 12px; border-radius: 4px; font-size: 0.75rem; cursor: pointer; display: flex; align-items: center; gap: 5px; font-weight: 700; border: none; transition: 0.2s; text-transform: uppercase; white-space: nowrap; }
    .btn-edit { background: #f59e0b; color: white; } 
    .btn-graph { background: #3b82f6; color: white; } 
    .btn-csv { background: #10b981; color: white; } 
    .btn-excel { background: #15803d; color: white; }
    .btn-red { background: #ef4444; color: white; }
    .btn-select { background: #6366f1; color: white; }
    .btn-dark { background: #1e293b; color: white; }
    
    /* Responsive Media Queries */
    @media (max-width: 768px) { 
        .res-grid-3 { grid-template-columns: 1fr; } 
        .res-header { flex-direction: column; align-items: flex-start; } 
        
        /* Area Header di HP: Judul di atas, Tombol di bawah full width */
        .area-header { flex-direction: column; align-items: flex-start; }
        .area-actions { width: 100%; display: grid; grid-template-columns: 1fr 1fr; } /* Tombol jadi grid 2 kolom */
        .btn-action { justify-content: center; width: 100%; }
    }
</style>

<div style="padding: 20px;">

    <div class="res-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #2c3e50;">
                <i class="fa-solid fa-solar-panel" style="color:#f39c12;"></i> REKAP KWH PLTS
            </h1>
        </div>
        
        <form method="GET" action="{{ route('plts.index') }}" style="display: flex; gap: 10px; flex-wrap: wrap; width: 100%;">
            <select name="phase" onchange="this.form.submit()" style="flex: 1; min-width: 120px; padding: 8px; border-radius: 6px; border: 1px solid #d1d5db; background: #eff6ff; font-weight: 600; color: #1e40af;">
                <option value="All Phases" {{ $selectedPhase == 'All Phases' ? 'selected' : '' }}>Semua Phase</option>
                <option value="Phase 1" {{ $selectedPhase == 'Phase 1' ? 'selected' : '' }}>Phase 1</option>
                <option value="Phase 2" {{ $selectedPhase == 'Phase 2' ? 'selected' : '' }}>Phase 2</option>
            </select>
            <select name="location" onchange="this.form.submit()" style="flex: 1; min-width: 150px; padding: 8px; border-radius: 6px; border: 1px solid #d1d5db; background: #f9fafb;">
                <option value="All Locations">Semua Lokasi</option>
                @foreach($dropdownLocations as $loc)
                    <option value="{{ $loc }}" {{ $selectedLocation == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                @endforeach
            </select>
            <select name="year" onchange="this.form.submit()" style="flex: 1; min-width: 100px; padding: 8px; border-radius: 6px; border: 1px solid #d1d5db; background: #f9fafb;">
                <option value="All Years" {{ $selectedYear == 'All Years' ? 'selected' : '' }}>Semua Tahun</option>
                @foreach($dropdownYears as $y) 
                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option> 
                @endforeach
            </select>
            @if(auth()->user()->role == 'admin')
            <button type="button" onclick="document.getElementById('uploadModal').style.display='block'" class="btn-action btn-graph" style="height:38px; justify-content: center;">Import</button>
            @endif
        </form>
    </div>

    @if($hasData || $selectedYear != 'All Years')
        
        <div class="res-grid-3">
            <div style="background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 20px; border-radius: 12px; color: white;">
                <div style="font-size:0.8rem; font-weight:600; opacity:0.9;">TOTAL KWH</div>
                <div id="statTotal" style="font-size:2rem; font-weight:800; margin-top:5px;">{{ number_format($totalKwh, 0, ',', '.') }}</div>
            </div>
            <div style="background: linear-gradient(135deg, #10b981, #059669); padding: 20px; border-radius: 12px; color: white;">
                <div style="font-size:0.8rem; font-weight:600; opacity:0.9;">RATA-RATA BULANAN</div>
                <div id="statAvg" style="font-size:2rem; font-weight:800; margin-top:5px;">{{ number_format($avgBulanan, 0, ',', '.') }}</div>
            </div>
            <div style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 20px; border-radius: 12px; color: white;">
                <div style="font-size:0.8rem; font-weight:600; opacity:0.9;">TERTINGGI</div>
                <div id="statHigh" style="font-size:1.8rem; font-weight:800; margin-top:5px;">{{ $bestMonthName }}</div>
            </div>
        </div>

        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 30px;">
            <div style="display:flex; justify-content:space-between; align-items: center; margin-bottom: 15px; flex-wrap:wrap; gap:10px;">
                <h4 style="margin:0; color:#1e293b; font-weight:800;">GRAFIK KWH</h4>
                <button onclick="downloadMainChart()" style="border:1px solid #cbd5e1; background:#f8fafc; cursor:pointer; padding:6px 12px; border-radius:6px; color:#64748b; font-weight:600;"><i class="fa-solid fa-camera"></i> PNG</button>
            </div>
            <div style="height: 350px; position: relative; width: 100%;"><canvas id="chartPltsMain"></canvas></div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px; flex-wrap:wrap;">
            @if(auth()->user()->role == 'admin')
                <button type="button" id="btnSelectMode" onclick="toggleSelectionMode()" class="btn-action btn-select">
                    <i class="fa-regular fa-square-check"></i> Pilih Hapus
                </button>
                <button type="submit" form="bulkDeleteForm" id="btnDeleteGlobal" class="btn-action btn-red" style="display:none;" onclick="return confirm('Yakin ingin menghapus data terpilih?')">
                    <i class="fa-solid fa-trash-can"></i> Hapus Terpilih
                </button>
            @endif
            <button onclick="downloadGlobalCSV()" class="btn-action btn-dark">
                <i class="fa-solid fa-download"></i> Download Semua (CSV)
            </button>
        </div>

        <form id="bulkDeleteForm" action="{{ route('plts.delete') }}" method="POST">
        @csrf
        
        <div id="allTablesContainer">
            @foreach($areaPhaseMap as $area => $phase)
                @if( ($selectedPhase == 'All Phases' || $selectedPhase == $phase) && ($selectedLocation == 'All Locations' || $selectedLocation == $area) )
                    @php 
                        $slug = \Illuminate\Support\Str::slug($area); 
                        $areaHasData = false;
                        foreach($colsYear as $y) {
                            for($m=1; $m<=12; $m++){
                                if(isset($matrix[$area][$m][$y]) && $matrix[$area][$m][$y]['val'] > 0) { $areaHasData = true; break 2; }
                            }
                        }
                    @endphp

                    <div class="area-block">
                        <div class="area-header">
                            <div class="area-title">
                                <i class="fa-solid fa-location-dot" style="color: #64748b;"></i> {{ $area }}
                                <span style="font-size:0.7rem; background:{{ $phase=='Phase 1'?'#dbeafe':'#ffedd5' }}; color:{{ $phase=='Phase 1'?'#1e40af':'#9a3412' }}; padding:2px 8px; border-radius:4px; font-weight:800;">{{ $phase }}</span>
                            </div>
                            <div class="area-actions">
                                @if(auth()->user()->role == 'admin')
                                    <button type="button" class="btn-action btn-edit" onclick="openBulkEdit('{{ $area }}', '{{ $selectedYear }}')"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                @endif
                                @if($areaHasData)
                                    <button type="button" class="btn-action btn-graph" onclick="openChartModal('{{ $area }}')"><i class="fa-solid fa-chart-simple"></i> Grafik</button>
                                    <button type="button" class="btn-action btn-csv" onclick="exportTable('table-{{ $slug }}', '{{ $area }}.csv', 'csv')"><i class="fa-solid fa-file-csv"></i> CSV</button>
                                    <button type="button" class="btn-action btn-excel" onclick="exportTable('table-{{ $slug }}', '{{ $area }}.xls', 'excel')"><i class="fa-solid fa-file-excel"></i> Excel</button>
                                @endif
                            </div>
                        </div>

                        @if(!$areaHasData)
                            <div class="alert-empty" style="padding: 20px; background: #fffbeb; border-left: 4px solid #f59e0b; color: #92400e; display: flex; align-items: center; gap: 15px;">
                                <i class="fas fa-folder-open" style="font-size: 1.5rem;"></i>
                                <div><strong>Data Tidak Tersedia</strong><br><small>Tidak ada data di tahun ini.</small></div>
                            </div>
                        @else
                            <div style="overflow-x: auto;">
                                <table class="main-table area-table" id="table-{{ $slug }}" data-areaname="{{ $area }}">
                                    <thead>
                                        <tr>
                                            <th class="col-check" style="background:#e2e8f0; width:40px;">
                                                <input type="checkbox" class="chk-head" onclick="checkAllInTable(this)">
                                            </th>
                                            <th style="text-align: center; min-width: 150px;">Bulan</th>
                                            @foreach($colsYear as $y) <th>{{ $y }}</th> @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach([1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $num => $nama)
                                        <tr>
                                            <td class="col-check">
                                                <div style="display:flex; justify-content:center;">
                                                    <i class="fa-solid fa-arrow-right" style="color:#cbd5e1; font-size:0.8rem;"></i>
                                                </div>
                                            </td>
                                            <td>{{ $nama }}</td>
                                            @foreach($colsYear as $y)
                                                @php 
                                                    $cellData = $matrix[$area][$num][$y] ?? null;
                                                    $val = $cellData ? $cellData['val'] : 0;
                                                    $id  = $cellData ? $cellData['id'] : null;
                                                @endphp
                                                <td>
                                                    <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
                                                        @if($id && auth()->user()->role == 'admin')
                                                            <input type="checkbox" name="ids[]" value="{{ $id }}" class="chk-item col-check">
                                                        @endif
                                                        <span>{{ $val > 0 ? number_format($val, 2, ',', '.') : '-' }}</span>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                        <tr class="total-row" style="background: #f8fafc; font-weight:bold;">
                                            <td class="col-check"></td>
                                            <td>Total {{ $area }}</td>
                                            @foreach($colsYear as $y)
                                                <td>
                                                    @php $sum = 0; for($i=1; $i<=12; $i++) if(isset($matrix[$area][$i][$y])) $sum += $matrix[$area][$i][$y]['val']; @endphp
                                                    {{ $sum > 0 ? number_format($sum, 2, ',', '.') : '-' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
        </form>

    @else
        <div style="text-align: center; padding: 50px; background: white; border-radius: 8px; margin-top: 20px;">
            <i class="fa-regular fa-folder-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;"></i>
            <h3 style="color: #64748b;">Belum ada rekap data di tahun {{ $selectedYear }}</h3>
        </div>
    @endif

</div>

<div id="chartModal" class="modal-blur-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div class="modal-content-glass" style="background:#ffffff; border-radius:12px; padding:25px; width:90%; max-width:950px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="modalChartTitle" style="margin:0;"></h3>
            <button onclick="closeChartModal()" style="background:#ef4444; color:white; border:none; width:30px; height:30px; border-radius:50%; cursor:pointer;">&times;</button>
        </div>
        <div style="height:400px; position: relative; width: 100%;"><canvas id="modalCanvas"></canvas></div>
    </div>
</div>

<div id="uploadModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="background:white; width:90%; max-width:400px; margin:15vh auto; padding:20px; border-radius:10px;">
        <h3 style="text-align:center;">Import CSV</h3>
        <form action="{{ route('plts.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label>Pilih Tahun</label>
            <select name="year" style="width:100%; padding:8px; margin-bottom:15px; border-radius:4px; border:1px solid #ddd;">
                @foreach($dropdownYears as $y) <option value="{{ $y }}">{{ $y }}</option> @endforeach
            </select>
            <input type="file" name="csv_file" required style="width:100%; margin-bottom:15px;">
            <button type="submit" style="width:100%; padding:10px; background:#2563eb; color:white; border:none; border-radius:5px; cursor:pointer;">Upload</button>
        </form>
        <button onclick="document.getElementById('uploadModal').style.display='none'" style="margin-top:10px; width:100%; padding:10px; border:none; background:#f1f5f9; cursor:pointer; border-radius:5px;">Batal</button>
    </div>
</div>

<div id="bulkEditModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; backdrop-filter: blur(2px);">
    <div style="background:white; width:95%; max-width:550px; margin: 5vh auto; padding:25px; border-radius:12px; position:relative;">
        <span onclick="closeBulkEdit()" style="position:absolute; top:15px; right:20px; cursor:pointer; font-size:1.5rem;">&times;</span>
        <h3 id="bulkTitle" style="margin-top:0;">Edit Data</h3>
        <form action="{{ route('plts.bulkUpdate') }}" method="POST">
            @csrf
            <input type="hidden" name="plant_name" id="bulk_plant_name">
            <div style="margin-bottom:20px;">
                <label>Tahun</label>
                <select name="year" id="bulk_year" style="width:100%; padding:8px; border-radius:4px; border:1px solid #ddd;" onchange="loadBulkData()">
                    @foreach($dropdownYears as $y) <option value="{{ $y }}">{{ $y }}</option> @endforeach
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; max-height: 300px; overflow-y:auto;">
                @foreach([1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $m)
                <div><label>{{ $m }}</label><input type="number" step="0.01" name="kwh[]" id="input_month_{{ $i }}" class="bulk-input" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" placeholder="0"></div>
                @endforeach
            </div>
            <button type="submit" style="width:100%; padding:12px; background:#2563eb; color:white; border:none; border-radius:6px; cursor:pointer;">Simpan</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.body.classList.add('preload');
    window.addEventListener('load', function() { 
        document.body.classList.remove('preload');
        renderMainChart();
    });

    //LOGIKA UTAMA: TOGGLE CHECKBOX
    let isSelectionMode = false;
    
    function toggleSelectionMode() {
        isSelectionMode = !isSelectionMode;
        
        const cols = document.querySelectorAll('.col-check');
        const btn = document.getElementById('btnSelectMode');
        const btnDelete = document.getElementById('btnDeleteGlobal');

        cols.forEach(el => {
            if(el.classList.contains('chk-item')) {
                el.style.display = isSelectionMode ? 'inline-block' : 'none';
            } else {
                el.style.display = isSelectionMode ? 'table-cell' : 'none';
            }
        });

        if(isSelectionMode) {
            btn.innerHTML = '<i class="fa-solid fa-times"></i> Batal';
            btn.style.background = '#64748b';
        } else {
            btn.innerHTML = '<i class="fa-regular fa-square-check"></i> Pilih Hapus';
            btn.style.background = '#6366f1';
            
            document.querySelectorAll('.chk-item, .chk-head').forEach(c => c.checked = false);
            if(btnDelete) btnDelete.style.display = 'none';
        }
    }

    function checkAllInTable(source) {
        const table = source.closest('table');
        if(table) {
            const checkboxes = table.querySelectorAll('.chk-item');
            checkboxes.forEach(chk => {
                chk.checked = source.checked;
            });
            updateDeleteButton();
        }
    }

    document.addEventListener('change', function(e) {
        if(e.target.classList.contains('chk-item')) {
            updateDeleteButton();
        }
    });

    function updateDeleteButton() {
        const anyChecked = document.querySelectorAll('.chk-item:checked').length > 0;
        const btnDelete = document.getElementById('btnDeleteGlobal');
        if(btnDelete) btnDelete.style.display = anyChecked ? 'inline-flex' : 'none';
    }

    // --- SCRIPT GRAFIK & MODAL ---
    let mainChartInstance = null; let modalChartInstance = null;
    
    function createDatasets(dataSeries) {
        const datasets = []; 
        let colorIdx = 0; 
        for (const [year, data] of Object.entries(dataSeries)) { 
            datasets.push({ 
                label: year, 
                data: data, 
                backgroundColor: window.chartColors[colorIdx % window.chartColors.length], 
                borderRadius: 4, 
                borderWidth: 0 
            }); 
            colorIdx++; 
        } 
        return datasets;
    }
    
    function renderMainChart() {
        const canvas = document.getElementById('chartPltsMain'); 
        if (!canvas) return; 
        if (window.mainChartInstance) { window.mainChartInstance.destroy(); } 
        window.mainChartInstance = new Chart(canvas.getContext('2d'), { 
            type: 'bar', 
            data: { 
                labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'], 
                datasets: createDatasets(window.mainChartSeries) 
            }, 
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { position: 'top' } }, 
                scales: { y: { beginAtZero: true } } 
            } 
        });
    }
    
    function downloadMainChart() { 
        var canvas = document.getElementById('chartPltsMain'); 
        if(canvas) { 
            var link = document.createElement('a'); 
            link.href = canvas.toDataURL('image/png'); 
            link.download = 'Grafik_Global_PLTS.png'; 
            link.click(); 
        } 
    }
    
    function openChartModal(areaName) { 
        document.getElementById('modalChartTitle').innerText = areaName; 
        document.getElementById('chartModal').style.display = 'flex'; 
        const canvas = document.getElementById('modalCanvas'); 
        const areaData = window.areaChartSeries[areaName] || {}; 
        
        if (window.modalChartInstance) { window.modalChartInstance.destroy(); } 
        
        window.modalChartInstance = new Chart(canvas.getContext('2d'), { 
            type: 'bar', 
            data: { 
                labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'], 
                datasets: createDatasets(areaData) 
            }, 
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                scales: { y: { beginAtZero: true } } 
            } 
        }); 
    }
    
    function closeChartModal() { document.getElementById('chartModal').style.display = 'none'; }
    
    function exportTable(tableId, filename, type) { 
        var table = document.getElementById(tableId); 
        var rows = table.querySelectorAll('tr'); 
        var csv = []; 
        for (var i = 0; i < rows.length; i++) { 
            var row = [], cols = rows[i].querySelectorAll('td, th'); 
            for (var j = 0; j < cols.length; j++) { 
                if(cols[j].classList.contains('col-check')) continue; 
                var data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s)/gm, " "); 
                data = data.replace(/"/g, '""'); 
                row.push('"' + data + '"'); 
            } 
            csv.push(row.join(",")); 
        } 
        if (type == 'csv') { 
            var csvFile = new Blob([csv.join("\n")], {type: "text/csv"}); 
            var link = document.createElement("a"); 
            link.download = filename; 
            link.href = window.URL.createObjectURL(csvFile); 
            link.click(); 
        } else { 
            var excelFile = '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><body><table>' + table.innerHTML + '</table></body></html>'; 
            var blob = new Blob([excelFile], {type: 'application/vnd.ms-excel'}); 
            var link = document.createElement("a"); 
            link.download = filename; 
            link.href = window.URL.createObjectURL(blob); 
            link.click(); 
        } 
    }
    
    function downloadGlobalCSV() { exportTable('allTablesContainer', 'Laporan_Semua.csv', 'csv'); }
    
    let currentArea = ''; 
    function openBulkEdit(area, defaultYear) { 
        currentArea = area; 
        document.getElementById('bulkTitle').innerText = area; 
        document.getElementById('bulk_plant_name').value = area; 
        let yr = (defaultYear === 'All Years') ? new Date().getFullYear() : defaultYear; 
        document.getElementById('bulk_year').value = yr; 
        document.getElementById('bulkEditModal').style.display = 'block'; 
        loadBulkData(); 
    }
    
    function closeBulkEdit() { document.getElementById('bulkEditModal').style.display = 'none'; }
    
    function loadBulkData() { 
        let yr = document.getElementById('bulk_year').value; 
        document.querySelectorAll('.bulk-input').forEach(el => el.value = ''); 
        let filtered = window.dbData.filter(d => d.plant_name === currentArea && d.record_date.startsWith(yr)); 
        filtered.forEach(d => { 
            let date = new Date(d.record_date); 
            let month = date.getMonth() + 1; 
            let el = document.getElementById('input_month_' + month); 
            if(el) el.value = d.kwh_generated; 
        }); 
    }
</script>
@endsection