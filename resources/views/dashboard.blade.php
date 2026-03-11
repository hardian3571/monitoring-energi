@extends('layouts.app')

@section('content')

<style>
    /* HEADER & STATS */
    .dash-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 25px; 
        background: white; 
        padding: 15px 20px; 
        border-radius: 8px; 
        border: 1px solid #e2e8f0; 
        gap: 15px;
    }
    
    /* DROPDOWN TAHUN (MODIFIKASI PRESISI) */
    .year-select {
        /* Reset tampilan bawaan */
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        outline: none;
        border: none;

        /* Ukuran & Padding */
        height: 40px; /* Tinggi fix biar rapi */
        padding: 0 40px 0 15px; /* Kanan 40px (space panah), Kiri 15px (space teks) */
        
        /* Typography & Warna */
        font-weight: 700; 
        font-size: 0.9rem;
        color: white; 
        background-color: #3b82f6; /* Warna Biru */
        border-radius: 8px; 
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3); /* Shadow halus aja */
        cursor: pointer; 
        
        /* ALIGNMENT: DIBUAT KIRI BIAR LURUS SAMA ISI OPSI SAAT DIKLIK */
        text-align: left; 
        line-height: 40px; /* Vertikal tengah */

        /* Ikon Panah Custom */
        background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23FFFFFF%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center; /* Posisi Panah Presisi */
        background-size: 10px auto;
    }

    .year-select:hover {
        background-color: #2563eb; /* Biru gelap saat hover */
    }

    /* Opsi Dropdown */
    .year-select option {
        background: white;
        color: #333;
        padding: 10px;
    }

    /* GRID LAYOUT */
    .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
    .stat-card { padding: 20px; border-radius: 12px; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .stat-card h5 { margin: 0; font-size: 0.85rem; opacity: 0.9; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-card h2 { margin: 10px 0 0 0; font-size: 2rem; font-weight: 800; }
    .stat-icon { position: absolute; right: 15px; bottom: 15px; font-size: 3.5rem; opacity: 0.2; }

    /* CHART & BOX */
    .chart-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
    .box-container { background: white; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
    .box-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
    .box-title { font-weight: 700; color: #334155; display: flex; align-items: center; gap: 10px; }
    
    .btn-download { border: 1px solid #cbd5e1; background: #f8fafc; padding: 5px 12px; border-radius: 6px; cursor: pointer; font-weight: 700; color: #475569; font-size: 0.8rem; transition: 0.2s; display: flex; align-items: center; gap: 5px; }
    .btn-download:hover { background: #e2e8f0; color: #1e293b; }

    /* HISTORY LIST */
    .history-section { margin-top: 20px; }
    .user-list { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; }
    .user-item { display: flex; align-items: center; gap: 10px; padding: 15px; border: 1px solid #f1f5f9; border-radius: 8px; background: #f8fafc; }
    .avatar { width: 40px; height: 40px; background: #3b82f6; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 1rem; }
    .user-info h6 { margin: 0; font-size: 0.95rem; color: #1e293b; font-weight: 700; }
    .user-info small { color: #64748b; font-size: 0.8rem; display: block; }

    /* RESPONSIVE */
    @media (max-width: 992px) { 
        .stat-grid { grid-template-columns: 1fr; } 
        .chart-row { grid-template-columns: 1fr; }
        
        .dash-header { 
            flex-direction: column; 
            align-items: stretch; 
        }
        .year-select {
            width: 100%; 
            background-position: right 15px center;
        }
    }
</style>

<div>
    <div class="dash-header">
        <div>
            <h2 style="margin:0; font-size:1.5rem; color:#1e293b; font-weight:800;">
                <i class="fa-solid fa-gauge-high" style="color:#6366f1;"></i> DASHBOARD ENERGI
            </h2>
            <small style="color:#64748b;">Ringkasan Monitoring PLTS & Listrik SS-4</small>
        </div>
        <form method="GET" action="{{ route('dashboard') }}">
            {{-- DROPDOWN FIX --}}
            <select name="year" onchange="this.form.submit()" class="year-select">
                <option value="All Years" {{ $selectedYear == 'All Years' ? 'selected' : '' }}>Semua Tahun</option>
                @foreach($dropdownYears as $y) 
                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option> 
                @endforeach
            </select>
        </form>
    </div>

    <div class="stat-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <h5>Total PLTS ({{ $selectedYear == 'All Years' ? 'Semua Tahun' : $selectedYear }})</h5>
            <h2>{{ number_format($totalPlts, 0, ',', '.') }} <span style="font-size:1rem;">kWh</span></h2>
            <i class="fa-solid fa-solar-panel stat-icon"></i>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
            <h5>Total SS-4 ({{ $selectedYear == 'All Years' ? 'Semua Tahun' : $selectedYear }})</h5>
            <h2>{{ number_format($totalSs4, 0, ',', '.') }} <span style="font-size:1rem;">kWh</span></h2>
            <i class="fa-solid fa-industry stat-icon"></i>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
            <h5>Total Energi Gabungan</h5>
            <h2>{{ number_format($grandTotal, 0, ',', '.') }} <span style="font-size:1rem;">kWh</span></h2>
            <i class="fa-solid fa-bolt stat-icon"></i>
        </div>
    </div>

    <div class="chart-row">
        
        <div class="box-container">
            <div class="box-header">
                <div class="box-title">
                    <i class="fa-solid fa-chart-column" style="color:#6366f1;"></i>
                    GRAFIK BULANAN
                </div>
                <button onclick="downloadChart('stackedChart', 'Grafik_Volume_Stacked.png')" class="btn-download">
                    <i class="fa-solid fa-camera"></i> PNG
                </button>
            </div>
            <div style="height: 350px; position: relative;">
                <canvas id="stackedChart"></canvas>
            </div>
        </div>

        <div class="box-container">
            <div class="box-header">
                <div class="box-title">
                    <i class="fa-solid fa-chart-line" style="color:#ef4444;"></i>
                    TREND PLTS DAN SS-4
                </div>
                <button onclick="downloadChart('lineChart', 'Grafik_Trend_Line.png')" class="btn-download">
                    <i class="fa-solid fa-camera"></i> PNG
                </button>
            </div>
            <div style="height: 350px; position: relative;">
                <canvas id="lineChart"></canvas>
            </div>
        </div>

    </div>

    @if(auth()->user()->role == 'admin')
    <div class="box-container history-section">
        <div class="box-header">
            <div class="box-title">
                <i class="fa-solid fa-clock-rotate-left" style="color:#f59e0b;"></i>
                AKTIVITAS LOGIN TERBARU
            </div>
            <a href="{{ route('users.index') }}" style="font-size: 0.85rem; text-decoration: none; color: #3b82f6; font-weight: 600;">Lihat Semua &rarr;</a>
        </div>
        
        <ul class="user-list">
            @if(isset($recentUsers) && count($recentUsers) > 0)
                @foreach($recentUsers as $user)
                <li class="user-item">
                    <div class="avatar">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="user-info">
                        <h6>{{ $user->name }}</h6>
                        <small>
                            <i class="fa-regular fa-clock"></i> 
                            {{ \Carbon\Carbon::parse($user->updated_at)->diffForHumans() }}
                        </small>
                    </div>
                </li>
                @endforeach
            @else
                <li style="padding:20px; color:#94a3b8; width:100%;">Belum ada aktivitas.</li>
            @endif
        </ul>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. SIAPKAN DATA
    window.pltsData = @json($pltsPerMonth);
    window.ss4Data  = @json($ss4PerMonth);

    // Hitung Total Gabungan
    window.totalData = window.pltsData.map((value, index) => {
        let plts = parseFloat(value) || 0;
        let ss4  = parseFloat(window.ss4Data[index]) || 0;
        return plts + ss4;
    });

    // 2. CONFIG UMUM
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { 
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) { label += ': '; }
                        if (context.parsed.y !== null) {
                            label += context.parsed.y.toLocaleString('id-ID') + ' kWh';
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            x: { grid: { display: false } },
            y: { 
                beginAtZero: true, 
                grid: { color: '#f1f5f9' }, 
                ticks: { callback: function(val) { return val.toLocaleString('id-ID'); } } 
            }
        }
    };

    // CHART 1: STACKED
    const ctxStacked = document.getElementById('stackedChart');
    if (ctxStacked) {
        new Chart(ctxStacked.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                datasets: [
                    { label: 'PLTS', data: window.pltsData, backgroundColor: '#f59e0b', stack: 'Stack 0' },
                    { label: 'SS-4', data: window.ss4Data, backgroundColor: '#3b82f6', stack: 'Stack 0' }
                ]
            },
            options: {
                ...commonOptions,
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: function(val) { return val.toLocaleString('id-ID'); } } }
                }
            }
        });
    }

    // CHART 2: LINE + TOTAL
    const ctxLine = document.getElementById('lineChart');
    if (ctxLine) {
        new Chart(ctxLine.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                datasets: [
                    { 
                        label: 'Total Gabungan', 
                        data: window.totalData, 
                        borderColor: '#10b981', 
                        backgroundColor: 'rgba(16, 185, 129, 0.1)', 
                        tension: 0.3, fill: false, borderWidth: 3, pointRadius: 4, borderDash: [5, 5] 
                    },
                    { 
                        label: 'PLTS', 
                        data: window.pltsData, 
                        borderColor: '#f59e0b', 
                        backgroundColor: 'rgba(245, 158, 11, 0.1)', 
                        tension: 0.3, fill: true, pointRadius: 3 
                    },
                    { 
                        label: 'SS-4', 
                        data: window.ss4Data, 
                        borderColor: '#3b82f6', 
                        backgroundColor: 'rgba(59, 130, 246, 0.1)', 
                        tension: 0.3, fill: true, pointRadius: 3 
                    }
                ]
            },
            options: commonOptions
        });
    }

    // DOWNLOAD
    function downloadChart(canvasId, fileName) {
        const c = document.getElementById(canvasId);
        if(c) {
            const l = document.createElement('a'); 
            l.download = fileName; 
            l.href = c.toDataURL(); 
            l.click();
        }
    }
</script>
@endsection