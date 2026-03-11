@extends('layouts.app')

@section('content')

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<script>
    window.dbData = @json($rawData);
</script>

<style>
    /* === 1. GLOBAL LAYOUT === */
    .dashboard-container { padding: 20px; }
    
    /* Header Page */
    .res-header { 
        background: white; padding: 15px 20px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 20px;
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;
    }
    
    /* Filter Controls */
    .filter-group { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .custom-select { padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: 600; height: 38px; background: white; }

    /* === 2. MAIN GRID (CHART & STATS) === */
    .main-grid { 
        display: grid; 
        grid-template-columns: 2fr 1fr; /* Desktop: Kiri Besar, Kanan Kecil */
        gap: 20px; 
        margin-bottom: 30px; 
    }

    /* CHART BOX STYLING (FIXED) */
    .chart-box { 
        background: white; 
        border-radius: 12px; 
        padding: 20px; 
        border: 1px solid #e2e8f0; 
        height: 450px; /* Tinggi Tetap */
        display: flex; 
        flex-direction: column; /* Susun Atas-Bawah */
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        flex-shrink: 0; /* Header tidak boleh mengecil */
    }

    .chart-header h4 { margin: 0; color: #334155; font-weight: 800; font-size: 1.1rem; }

    .chart-wrapper {
        flex: 1; /* Isi ruang kosong di tengah */
        position: relative;
        width: 100%;
        min-height: 0; /* Penting untuk Chart.js resize */
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .chart-caption {
        margin-top: 10px;
        text-align: center;
        font-size: 0.9rem;
        font-weight: 600;
        color: #64748b;
        flex-shrink: 0; 
    }

    /* --- STATS STYLING --- */
    .stats-column { display: flex; flex-direction: column; gap: 20px; height: 100%; }
    .stat-card { 
        background: white; border-radius: 12px; padding: 20px; 
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); 
        text-align: center; border: none; color: white;
        flex: 1; /* Kartu stats membagi tinggi rata */
        display: flex; flex-direction: column; justify-content: center;
    }
    .stat-title { opacity: 0.9; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; }
    .stat-value { font-size: 1.6rem; font-weight: 800; }

    /* 3. TABLE STYLING*/
    .month-block { margin-top: 30px; background: white; box-shadow: 0 4px 6px -2px rgba(0,0,0,0.05); border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
    .month-header { background: #f8fafc; padding: 15px 20px; font-weight: 800; color: #334155; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
    .month-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    
    .table-wrapper { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; min-width: 600px; }
    .data-table th { background: #1e293b; color: white; padding: 12px; text-align: center; font-weight: 600; white-space: nowrap; }
    .data-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; text-align: center; color: #475569; }
    .data-table tr:hover { background: #f8fafc; }
    .data-table tr:last-child td { font-weight: bold; background: #eff6ff; color: #1e3a8a; border-top: 2px solid #cbd5e1; }

    /* Buttons */
    .btn-action { padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; transition: 0.2s; color: white; text-decoration: none; }
    .btn-blue { background: #3b82f6; } .btn-blue:hover { background: #2563eb; }
    .btn-green { background: #10b981; } .btn-green:hover { background: #059669; }
    .btn-indigo { background: #6366f1; } .btn-indigo:hover { background: #4f46e5; }
    .btn-red { background: #ef4444; } .btn-red:hover { background: #dc2626; }
    .btn-gray { background: #64748b; } .btn-gray:hover { background: #475569; }
    .btn-orange { background: #f59e0b; } .btn-orange:hover { background: #d97706; }

    .pct-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; min-width: 45px; }
    .pct-spu { background: #dbeafe; color: #1e40af; }
    .pct-ss5 { background: #fee2e2; color: #991b1b; }

    /* 4. RESPONSIVE MEDIA QUERIES */
    @media (max-width: 992px) {
        .main-grid { grid-template-columns: 1fr; } /* Stack 1 Kolom */
        .chart-box { height: 400px; }
        .stats-column { display: grid; grid-template-columns: repeat(3, 1fr); height: auto; } /* Stats jejer 3 */
    }

    @media (max-width: 768px) {
        .res-header { flex-direction: column; align-items: flex-start; }
        .filter-group { width: 100%; }
        .filter-group select { flex: 1; }
        .stats-column { grid-template-columns: 1fr; } /* Stats tumpuk ke bawah */
        .chart-box { height: 350px; padding: 15px; }
        .month-header { flex-direction: column; align-items: flex-start; }
        .month-actions { width: 100%; justify-content: space-between; }
    }
</style>

<div class="dashboard-container">

    <div class="res-header">
        <h1 style="margin:0; font-size:1.4rem; font-weight:800; color:#1e293b; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-chart-pie" style="color:#6366f1;"></i> PERSENTASE SUPLAI
        </h1>
        <form method="GET" action="{{ route('trend.index') }}" class="filter-group">
            <select name="year" class="custom-select" onchange="this.form.submit()">
                @foreach($years as $y) <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option> @endforeach
            </select>
            <select name="month" class="custom-select" onchange="this.form.submit()">
                <option value="All" {{ $selectedMonth == 'All' ? 'selected' : '' }}>Semua Bulan</option>
                @foreach($indoMonths as $k => $v) <option value="{{ $k }}" {{ $selectedMonth == $k ? 'selected' : '' }}>{{ $v }}</option> @endforeach
            </select>
            
            @if(auth()->user()->role == 'admin')
            <button type="button" onclick="document.getElementById('addModal').style.display='flex'" class="btn-action btn-green" style="height:38px;">
                <i class="fa-solid fa-plus"></i> Tambah
            </button>
            <button type="button" onclick="document.getElementById('uploadModal').style.display='flex'" class="btn-action btn-blue" style="height:38px;">
                <i class="fa-solid fa-upload"></i> Import
            </button>
            @endif
        </form>
    </div>

    @if(session('success')) <div style="background:#dcfce7; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0;"><i class="fa-solid fa-check"></i> {{ session('success') }}</div> @endif
    @if(session('error')) <div style="background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #fecaca;"><i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}</div> @endif

    <div class="main-grid">
        
        <div class="chart-box">
            <div class="chart-header">
                <h4>PROPORSI TOTAL ({{ $selectedYear }})</h4>
                <button onclick="downloadChart('donutChart', 'Grafik_Proporsi_{{ $selectedYear }}.png')" class="btn-action btn-indigo">
                    <i class="fa-solid fa-camera"></i> PNG
                </button>
            </div>

            <div class="chart-wrapper">
                <canvas id="donutChart"></canvas>
            </div>

            <div class="chart-caption">
                <span style="color:#3b82f6;">● SPU ({{ $totalSpu + $totalSs5 > 0 ? number_format(($totalSpu / ($totalSpu + $totalSs5)) * 100, 1) : 0 }}%)</span> 
                <span style="margin: 0 10px; color:#cbd5e1;">|</span>
                <span style="color:#ef4444;">● SS-5 ({{ $totalSpu + $totalSs5 > 0 ? number_format(($totalSs5 / ($totalSpu + $totalSs5)) * 100, 1) : 0 }}%)</span>
            </div>
        </div>

        <div class="stats-column">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="stat-title">TOTAL SPU</div>
                <div class="stat-value">{{ number_format($totalSpu, 0, ',', '.') }} <span style="font-size:0.9rem">kWh</span></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                <div class="stat-title">TOTAL SS-5</div>
                <div class="stat-value">{{ number_format($totalSs5, 0, ',', '.') }} <span style="font-size:0.9rem">kWh</span></div>
            </div>
             <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="stat-title">TOTAL ENERGI</div>
                <div class="stat-value">{{ number_format($totalSpu + $totalSs5, 0, ',', '.') }} <span style="font-size:0.9rem">kWh</span></div>
            </div>
        </div>
    </div>

    <h3 style="margin-bottom: 15px; color: #334155; border-left: 5px solid #6366f1; padding-left: 10px;">DETAIL DATA HARIAN</h3>

    <form action="{{ route('trend.destroy') }}" method="POST" id="mainDeleteForm">
        @csrf
        @method('DELETE')
        
        @if($monthlyTables->count() > 0)
            @foreach($monthlyTables as $monthNum => $rows)
            <div class="month-block" id="block-{{ $monthNum }}">
                <div class="month-header">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <i class="fa-regular fa-calendar-days"></i> {{ $indoMonths[$monthNum] ?? 'Bulan '.$monthNum }} {{ $selectedYear }}
                    </div>
                    <div class="month-actions">
                        <button type="button" class="btn-action btn-indigo" onclick="downloadTableAsPng('block-{{ $monthNum }}', 'Tabel_{{ $indoMonths[$monthNum] }}')">
                            <i class="fa-solid fa-camera"></i>
                        </button>
                        <button type="button" class="btn-action btn-green" onclick="downloadTableCSV('tbl-data-{{ $monthNum }}', 'Laporan_{{ $indoMonths[$monthNum] }}')">
                            <i class="fa-solid fa-file-csv"></i>
                        </button>
                        <button type="button" class="btn-action btn-green" style="background:#15803d;" onclick="downloadTableExcel('tbl-data-{{ $monthNum }}', 'Laporan_{{ $indoMonths[$monthNum] }}')">
                            <i class="fa-solid fa-file-excel"></i>
                        </button>
                        
                        @if(auth()->user()->role == 'admin')
                        <button type="button" id="btn-select-{{ $monthNum }}" class="btn-action btn-blue" onclick="toggleSelect('{{ $monthNum }}')">
                            <i class="fa-regular fa-square-check"></i> Pilih
                        </button>
                        <button type="button" id="btn-delete-{{ $monthNum }}" class="btn-action btn-red" onclick="submitDelete()" style="display:none;">
                            <i class="fa-solid fa-trash-can"></i> Hapus
                        </button>
                        <button type="button" class="btn-action btn-orange" onclick="openEditModal('{{ $selectedYear }}', '{{ $monthNum }}', '{{ $indoMonths[$monthNum] }}')">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </button>
                        @endif

                        <button type="button" onclick="toggleTable('wrapper-{{ $monthNum }}')" style="background:none; border:none; cursor:pointer; color:#64748b;"><i class="fa-solid fa-chevron-down"></i></button>
                    </div>
                </div>
                
                <div id="wrapper-{{ $monthNum }}" class="table-wrapper">
                    <table class="data-table" id="tbl-data-{{ $monthNum }}">
                        <thead>
                            <tr>
                                <th class="col-check" style="display:none; width:40px;">
                                    <input type="checkbox" id="chk-all-{{ $monthNum }}" class="chk-all" onchange="checkAll('{{ $monthNum }}')">
                                </th>
                                <th style="text-align:left; padding-left:20px;">Tanggal</th>
                                <th>SPU (kWh)</th>
                                <th style="width: 80px;">% SPU</th>
                                <th>SS-5 (kWh)</th>
                                <th style="width: 80px;">% SS-5</th>
                                <th>Total (kWh)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                            @php 
                                $hTotal = $row->kwh_spu + $row->kwh_ss5; 
                                $pSpu = $hTotal > 0 ? ($row->kwh_spu / $hTotal * 100) : 0;
                                $pSs5 = $hTotal > 0 ? ($row->kwh_ss5 / $hTotal * 100) : 0;
                            @endphp
                            <tr>
                                <td class="col-check" style="display:none;">
                                    <input type="checkbox" name="ids[]" value="{{ $row->id }}" class="chk-item" onchange="checkItem('{{ $monthNum }}')">
                                </td>
                                <td style="text-align:left; padding-left:20px;">{{ date('d F Y', strtotime($row->record_date)) }}</td>
                                <td>{{ number_format($row->kwh_spu, 2, ',', '.') }}</td>
                                <td><span class="pct-badge pct-spu">{{ number_format($pSpu, 1) }}%</span></td>
                                <td>{{ number_format($row->kwh_ss5, 2, ',', '.') }}</td>
                                <td><span class="pct-badge pct-ss5">{{ number_format($pSs5, 1) }}%</span></td>
                                <td style="font-weight:bold; color:#1e293b;">{{ number_format($hTotal, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            
                            @php
                                $sumSpu = $rows->sum('kwh_spu'); $sumSs5 = $rows->sum('kwh_ss5'); $sumTot = $sumSpu + $sumSs5;
                                $ptSpu = $sumTot > 0 ? ($sumSpu / $sumTot * 100) : 0;
                                $ptSs5 = $sumTot > 0 ? ($sumSs5 / $sumTot * 100) : 0;
                            @endphp
                            <tr>
                                <td class="col-check" style="display:none;"></td>
                                <td style="text-align:left; padding-left:20px;">TOTAL {{ strtoupper($indoMonths[$monthNum]) }}</td>
                                <td>{{ number_format($sumSpu, 2, ',', '.') }}</td>
                                <td><span class="pct-badge pct-spu">{{ number_format($ptSpu, 1) }}%</span></td>
                                <td>{{ number_format($sumSs5, 2, ',', '.') }}</td>
                                <td><span class="pct-badge pct-ss5">{{ number_format($ptSs5, 1) }}%</span></td>
                                <td>{{ number_format($sumTot, 2, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        @else
            <div style="text-align:center; padding:40px; background:white; border-radius:12px; border:1px solid #e2e8f0; color:#94a3b8;">
                <p>Belum ada data untuk tahun {{ $selectedYear }}.</p>
            </div>
        @endif
    </form> 
</div>

<div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:white; padding:25px; border-radius:12px; width:90%; max-width:400px; box-shadow:0 20px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0;">Tambah Data</h3>
        <form action="{{ route('trend.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:15px;"><label>Tanggal</label><input type="date" name="record_date" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;"></div>
            <div style="margin-bottom:15px;"><label>SPU (kWh)</label><input type="number" step="0.01" name="kwh_spu" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;"></div>
            <div style="margin-bottom:20px;"><label>SS-5 (kWh)</label><input type="number" step="0.01" name="kwh_ss5" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;"></div>
            <div style="display:flex; gap:10px;">
                <button type="button" onclick="document.getElementById('addModal').style.display='none'" class="btn-action btn-gray" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action btn-green" style="flex:2; justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="uploadModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:white; padding:25px; border-radius:12px; width:90%; max-width:400px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 style="margin-top:0;">Import CSV</h3>
        <form action="{{ route('trend.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="csv_file" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; margin-bottom:20px;">
            <div style="display:flex; gap:10px;">
                <button type="button" onclick="document.getElementById('uploadModal').style.display='none'" class="btn-action btn-gray" style="flex:1; justify-content:center;">Batal</button>
                <button type="submit" class="btn-action btn-blue" style="flex:2; justify-content:center;">Upload</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:white; padding:0; border-radius:12px; width:95%; max-width:600px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 20px 25px rgba(0,0,0,0.2);">
        <div style="padding:20px; border-bottom:1px solid #eee; display:flex; justify-content:space-between;"><h3 style="margin:0;">Edit Data</h3><button onclick="document.getElementById('editModal').style.display='none'" style="border:none; background:none; font-size:1.5rem;">&times;</button></div>
        <div style="padding:20px; overflow-y:auto; flex:1;">
            <form action="{{ route('trend.bulkUpdate') }}" method="POST" id="editForm">
                @csrf
                <input type="hidden" name="year" id="edit_year"><input type="hidden" name="month" id="edit_month">
                <div id="editInputsContainer"></div>
                <div style="margin-top:20px; text-align:right;"><button type="submit" class="btn-action btn-blue">Simpan Perubahan</button></div>
            </form>
        </div>
    </div>
</div>

<script>
    // CHART CONFIG
    Chart.register(ChartDataLabels);
    const ctxDonut = document.getElementById('donutChart').getContext('2d');
    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: ['SPU Incoming', 'SS-5 Incoming'],
            datasets: [{
                data: [{{ $totalSpu }}, {{ $totalSs5 }}],
                backgroundColor: ['#3b82f6', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { display: false },
                datalabels: {
                    color: '#fff', font: { weight: 'bold', size: 14 },
                    formatter: (value, ctx) => {
                        let total = ctx.chart.getDatasetMeta(0).total;
                        if(total === 0) return '0%';
                        let percentage = (value * 100 / total).toFixed(1) + "%";
                        return percentage;
                    }
                }
            }
        }
    });

    // FUNCTIONS
    function downloadChart(chartId, filename) {
        const c = document.getElementById(chartId);
        const l = document.createElement('a'); 
        l.download = filename; 
        l.href = c.toDataURL('image/png'); 
        l.click();
    }

    function toggleTable(id) { let el = document.getElementById(id); el.style.display = (el.style.display==='none')?'block':'none'; }
    
    function toggleSelect(monthNum) {
        const wrapper = document.getElementById('wrapper-' + monthNum);
        const btnSelect = document.getElementById('btn-select-' + monthNum);
        const btnDelete = document.getElementById('btn-delete-' + monthNum);
        const cols = wrapper.querySelectorAll('.col-check');
        const isHidden = cols.length > 0 && (cols[0].style.display === 'none' || cols[0].style.display === '');
        cols.forEach(el => { el.style.display = isHidden ? 'table-cell' : 'none'; });
        
        if (isHidden) {
            btnSelect.innerHTML = '<i class="fa-solid fa-times"></i> Batal';
            btnSelect.className = 'btn-action btn-gray';
        } else {
            btnSelect.innerHTML = '<i class="fa-regular fa-square-check"></i> Pilih';
            btnSelect.className = 'btn-action btn-blue';
            btnDelete.style.display = 'none';
            wrapper.querySelectorAll('.chk-item').forEach(c => c.checked = false);
            const master = document.getElementById('chk-all-' + monthNum);
            if(master) master.checked = false;
        }
    }

    function checkAll(monthNum) {
        const wrapper = document.getElementById('wrapper-' + monthNum);
        const master = document.getElementById('chk-all-' + monthNum);
        wrapper.querySelectorAll('.chk-item').forEach(c => c.checked = master.checked);
        checkItem(monthNum);
    }

    function checkItem(monthNum) {
        const wrapper = document.getElementById('wrapper-' + monthNum);
        const anyChecked = wrapper.querySelectorAll('.chk-item:checked').length > 0;
        const btnDelete = document.getElementById('btn-delete-' + monthNum);
        btnDelete.style.display = anyChecked ? 'inline-flex' : 'none';
    }

    function submitDelete() {
        if(confirm('Yakin ingin menghapus data yang dipilih?')) {
            document.getElementById('mainDeleteForm').submit();
        }
    }

    // DOWNLOAD TABLE FUNCTIONS
    function downloadTableAsPng(divId, filename) {
        html2canvas(document.getElementById(divId), { scale: 2, backgroundColor: "#ffffff" }).then(canvas => {
            let link = document.createElement('a'); link.download = filename + '.png'; link.href = canvas.toDataURL("image/png"); link.click();
        });
    }

    function downloadTableCSV(tableId, filename) {
        let table = document.getElementById(tableId);
        let rows = table.querySelectorAll('tr');
        let csv = [];
        let headers = [];
        let ths = table.querySelectorAll('th');
        ths.forEach(th => {
            if(th.style.display !== 'none' && !th.classList.contains('col-check')) headers.push('"' + th.innerText + '"');
        });
        csv.push(headers.join(','));
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll('td');
            if(cols.length === 0) continue; 
            for (let j = 0; j < cols.length; j++) {
                if(cols[j].classList.contains('col-check')) continue; 
                let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s)/gm, " ").replace(/"/g, '""');
                row.push('"' + text + '"');
            }
            csv.push(row.join(","));
        }
        let csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
        let link = document.createElement("a"); link.download = filename + '.csv'; link.href = window.URL.createObjectURL(csvFile); link.click();
    }

    function downloadTableExcel(tableId, filename) {
        let originalTable = document.getElementById(tableId);
        let tableClone = originalTable.cloneNode(true);
        tableClone.querySelectorAll('.col-check').forEach(el => el.remove());
        let html = tableClone.outerHTML;
        let excelFile = '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8"></head><body>' + html + '</body></html>';
        let blob = new Blob([excelFile], { type: 'application/vnd.ms-excel' });
        let link = document.createElement("a"); link.href = URL.createObjectURL(blob); link.download = filename + ".xls"; link.click();
    }
    
    function openEditModal(year, month, monthName) {
        document.getElementById('edit_year').value = year;
        document.getElementById('edit_month').value = month;
        document.getElementById('editModal').style.display = 'flex';
        let container = document.getElementById('editInputsContainer'); container.innerHTML = '';
        let daysInMonth = new Date(year, month, 0).getDate();
        let monthData = window.dbData.filter(d => new Date(d.record_date).getMonth()+1 == month);
        let tableHtml = '<table style="width:100%; border-collapse:collapse;"><thead><tr style="background:#f1f5f9;"><th style="padding:8px; text-align:left;">Tgl</th><th style="padding:8px;">SPU</th><th style="padding:8px;">SS-5</th></tr></thead><tbody>';
        for(let d=1; d<=daysInMonth; d++) {
            let row = monthData.find(r => new Date(r.record_date).getDate() == d);
            let v1 = row ? row.kwh_spu : 0; let v2 = row ? row.kwh_ss5 : 0;
            tableHtml += `<tr><td style="padding:5px; font-weight:bold; border-bottom:1px solid #eee;">${d}</td><td style="padding:5px; border-bottom:1px solid #eee;"><input type="number" step="0.01" name="spu[${d}]" value="${v1}" style="width:100%; padding:5px; border:1px solid #ddd; border-radius:4px;"></td><td style="padding:5px; border-bottom:1px solid #eee;"><input type="number" step="0.01" name="ss5[${d}]" value="${v2}" style="width:100%; padding:5px; border:1px solid #ddd; border-radius:4px;"></td></tr>`;
        }
        tableHtml += '</tbody></table>';
        container.innerHTML = tableHtml;
    }
</script>
@endsection