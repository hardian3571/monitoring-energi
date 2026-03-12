@extends('layouts.app')

@section('content')

<script>
    // Data Grafik dikirim per tahun (Series)
    window.chartSeries = @json($chartSeries);
    // Warna warni grafik (sama seperti PLTS)
    window.chartColors = ['#f97316', '#94a3b8', '#facc15', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6', '#db2777', '#f43f5e', '#6366f1'];
    // Data Raw untuk Edit
    window.dbDataRaw = @json($rawData);
</script>

<style>
    body.preload * { transition: none !important; }
    
    /* STYLE UTAMA (Mirip PLTS) */
    .res-header { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
    .res-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px; }
    
    /* TABEL MATRIX */
    .ss4-table { width: 100%; border-collapse: separate; border-spacing: 0; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .ss4-table th { background: #1e293b; color: white; padding: 12px; text-align: center; font-size: 0.85rem; border-right: 1px solid #334155; }
    .ss4-table td { padding: 10px; border-bottom: 1px solid #eee; text-align: center; color: #334155; border-right: 1px solid #f1f5f9; font-size: 0.9rem; }
    .ss4-table tr:last-child td { font-weight: bold; background: #f8fafc; border-top: 2px solid #cbd5e1; }
    /* Sticky Column Kiri (Bulan) */
    .ss4-table td:first-child, .ss4-table th:first-child { position: sticky; left: 0; background: #fff; z-index: 10; border-right: 2px solid #e2e8f0; }
    .ss4-table th:first-child { background: #1e293b; } /* Fix header sticky warna */

    /* BUTTONS */
    .btn-action { padding: 6px 12px; border-radius: 4px; font-size: 0.75rem; cursor: pointer; display: flex; align-items: center; gap: 5px; font-weight: 700; border: none; transition: 0.2s; text-transform: uppercase; color: white; }
    .btn-edit { background: #f59e0b; }
    .btn-csv { background: #10b981; }
    .btn-excel { background: #15803d; }
    .btn-red { background: #ef4444; }
    .btn-select { background: #6366f1; }
    .btn-blue { background: #2563eb; }

    /* CHECKBOX */
    .col-check { display: none; width: 40px; text-align: center; vertical-align: middle; }
    .chk-item { width: 15px; height: 15px; cursor: pointer; transform: scale(1.2); }
    #chkSelectAll { width: 15px; height: 15px; cursor: pointer; transform: scale(1.2); }

    /* EMPTY STATE */
    .empty-state-box { padding: 40px; background: #fff; border: 2px dashed #cbd5e1; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 15px; text-align: center; margin-top: 20px; }

    /* MODAL */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 999; justify-content: center; align-items: center; }
    .modal-box { background: white; padding: 25px; border-radius: 10px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }

    @media (max-width: 768px) { .res-grid-3 { grid-template-columns: 1fr; } }
</style>

<div style="padding: 20px;">
    
    @if(session('success')) <div style="background:#d1fae5; color:#065f46; padding:15px; border-radius:8px; margin-bottom:20px;"><b>SUKSES:</b> {{ session('success') }}</div> @endif
    @if(session('error')) <div style="background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px; margin-bottom:20px;"><b>GAGAL:</b> {{ session('error') }}</div> @endif

    <div class="res-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #2c3e50;"><i class="fa-solid fa-industry" style="color:#facc15;"></i> REKAP LISTRIK SS-4</h1>
        </div>
        <form method="GET" action="{{ route('ss4.index') }}" style="display: flex; gap: 10px;">
            <select name="year" onchange="this.form.submit()" style="padding: 8px; border-radius: 6px; border: 1px solid #d1d5db;">
                <option value="All Years">Semua Tahun</option>
                @foreach($dropdownYears as $y) 
                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option> 
                @endforeach
            </select>
            @if(auth()->user()->role == 'admin')
            <button type="button" onclick="document.getElementById('uploadModal').style.display='flex'" class="btn-action btn-blue">Import</button>
            @endif
        </form>
    </div>

    @if($hasData)
        <div class="res-grid-3">
            <div style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); padding: 20px; border-radius: 12px; color: white;">
                <div style="font-size:0.8rem; font-weight:600; opacity:0.9;">TOTAL SUPLAI (All Time)</div>
                <div style="font-size:2rem; font-weight:800; margin-top:5px;">{{ number_format($grandTotal, 0, ',', '.') }} <span style="font-size:1rem;">kWh</span></div>
            </div>
            <div style="background: linear-gradient(135deg, #10b981, #059669); padding: 20px; border-radius: 12px; color: white;">
                <div style="font-size:0.8rem; font-weight:600; opacity:0.9;">RATA-RATA BULANAN</div>
                <div style="font-size:2rem; font-weight:800; margin-top:5px;">{{ number_format($avgBulanan, 0, ',', '.') }} <span style="font-size:1rem;">kWh</span></div>
            </div>
            <div style="background: linear-gradient(135deg, #f97316, #ea580c); padding: 20px; border-radius: 12px; color: white;">
                <div style="font-size:0.8rem; font-weight:600; opacity:0.9;">TERTINGGI DI</div>
                <div style="font-size:1.8rem; font-weight:800; margin-top:5px;">{{ $bestMonthName }}</div>
            </div>
        </div>

        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 30px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:15px;">
                <h4 style="margin:0; color:#334155;">GRAFIK KWH (Perbandingan Tahunan)</h4>
                <button onclick="downloadChart()" style="border:1px solid #ddd; background:#f8fafc; padding:5px 10px; border-radius:4px; cursor:pointer;"><i class="fa-solid fa-camera"></i> PNG</button>
            </div>
            <div style="height: 400px;">
                <canvas id="chartSs4"></canvas>
            </div>
        </div>

        <form action="{{ route('ss4.delete') }}" method="POST" id="deleteForm" onsubmit="return confirm('Yakin ingin menghapus data?')">
        @csrf
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0;">
            <div style="display:flex; justify-content:space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="margin:0; color:#334155;">Detail Data (Matrix)</h4>
                <div style="display: flex; gap: 5px;">
                    <button type="button" onclick="downloadTable('csv')" class="btn-action btn-csv"><i class="fa-solid fa-file-csv"></i> CSV</button>
                    <button type="button" onclick="downloadTable('excel')" class="btn-action btn-excel"><i class="fa-solid fa-file-excel"></i> Excel</button>
                    
                    @if(auth()->user()->role == 'admin')
                        <button type="button" id="btnSelectMode" onclick="toggleSelectionMode()" class="btn-action btn-select"><i class="fa-regular fa-square-check"></i> Pilih</button>
                        <button type="submit" class="btn-action btn-red" id="btnDelete" style="display:none;"><i class="fa-solid fa-trash-can"></i> Hapus</button>
                        <button type="button" onclick="openEditModal()" class="btn-action btn-edit"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                    @endif
                </div>
            </div>

            <div style="overflow-x: auto;">
    @if($selectedYear == 'All Years')
        <table class="ss4-table" id="tableSs4">
            <thead>
                <tr>
                    <th class="col-check" style="background:#f1f5f9; z-index:20;">
                        <input type="checkbox" id="chkSelectAll" title="Pilih Semua">
                    </th>
                    <th style="min-width:120px; z-index:20;">Bulan</th>
                    @foreach($dropdownYears as $yr) <th>{{ $yr }}</th> @endforeach
                </tr>
            </thead>
            <tbody>
                @php $months = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
                @foreach($months as $num => $mName)
                    <tr>
                        <td class="col-check"><i class="fa-solid fa-arrow-right" style="color:#cbd5e1;"></i></td>
                        <td style="text-align:left; font-weight:600;">{{ $mName }}</td>
                        @foreach($dropdownYears as $yr)
                            @php 
                                $val = $matrix[$num][$yr]['val'] ?? 0; 
                                $id  = $matrix[$num][$yr]['id'] ?? null;
                            @endphp
                            <td>
                                <div style="display:flex; justify-content:center; align-items:center; gap:5px;">
                                    @if($id && auth()->user()->role == 'admin') <input type="checkbox" name="ids[]" value="{{ $id }}" class="chk-item col-check"> @endif
                                    <span style="{{ $val==0 ? 'color:#cbd5e1;' : '' }}">{{ $val > 0 ? number_format($val, 0, ',', '.') : '-' }}</span>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                <tr style="background: #f8fafc;">
                    <td class="col-check"></td>
                    <td style="text-align:left; color:#1e293b;">TOTAL TAHUNAN</td>
                    @foreach($dropdownYears as $yr)
                        <td style="font-weight:900; color:#2563eb;">
                            {{ isset($totalPerTahun[$yr]) && $totalPerTahun[$yr] > 0 ? number_format($totalPerTahun[$yr], 0, ',', '.') : '-' }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>

    @else
        <table class="ss4-table" id="tableSs4">
            <thead>
                <tr>
                    <th class="col-check" style="background:#f1f5f9; z-index:20;">
                        <input type="checkbox" id="chkSelectAll" title="Pilih Semua">
                    </th>
                    <th style="min-width:120px; z-index:20;">Bulan</th>
                    <th>PKT (kWh)</th>
                    <th>KDM (kWh)</th>
                    <th>Total (kWh)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tableData as $row)
                    <tr>
                        <td class="col-check">
                            @if($row['id'] && auth()->user()->role == 'admin') 
                                <div style="display:flex; justify-content:center;">
                                    <input type="checkbox" name="ids[]" value="{{ $row['id'] }}" class="chk-item col-check">
                                </div>
                            @else
                                <i class="fa-solid fa-arrow-right" style="color:#cbd5e1;"></i>
                            @endif
                        </td>
                        <td style="text-align:left; font-weight:600;">{{ $row['label'] }} {{ $selectedYear }}</td>
                        <td>{{ $row['pkt'] > 0 ? number_format($row['pkt'], 0, ',', '.') : '-' }}</td>
                        <td>{{ $row['kdm'] > 0 ? number_format($row['kdm'], 0, ',', '.') : '-' }}</td>
                        <td style="font-weight:bold; color:#2563eb;">{{ $row['total'] > 0 ? number_format($row['total'], 0, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach
                <tr style="background: #f8fafc;">
                    <td class="col-check"></td>
                    <td style="text-align:left; color:#1e293b;">TOTAL {{ $selectedYear }}</td>
                    <td style="font-weight:bold;">{{ number_format(array_sum(array_column($tableData, 'pkt')), 0, ',', '.') }}</td>
                    <td style="font-weight:bold;">{{ number_format(array_sum(array_column($tableData, 'kdm')), 0, ',', '.') }}</td>
                    <td style="font-weight:900; color:#2563eb;">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</div>

<div id="uploadModal" class="modal-overlay">
    <div class="modal-box">
        <h3 style="text-align:center;">Import Data SS4</h3>
        <form action="{{ route('ss4.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label>Pilih Tahun</label>
            <select name="year" style="width:100%; padding:8px; margin-bottom:15px; border:1px solid #ddd;">
                @foreach($dropdownYears as $y) <option value="{{ $y }}">{{ $y }}</option> @endforeach
            </select>
            <input type="file" name="csv_file" required style="width:100%; margin-bottom:15px;">
            <button type="submit" class="btn-action btn-blue" style="width:100%; justify-content:center;">Upload</button>
        </form>
        <button onclick="document.getElementById('uploadModal').style.display='none'" style="margin-top:10px; width:100%; border:none; background:#f1f5f9; padding:10px; cursor:pointer;">Batal</button>
    </div>
</div>

<div id="editModal" class="modal-overlay">
    <div class="modal-box" style="max-width:700px;">
        <h3 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">Input / Edit Data</h3>
        <form action="{{ route('ss4.bulkUpdate') }}" method="POST">
            @csrf
            <div style="margin-bottom:15px;">
                <label>Pilih Tahun yang mau diedit:</label>
                <select name="year" id="edit_year" style="width:100%; padding:8px; border:1px solid #ddd;" onchange="loadEditData()">
                    @foreach($dropdownYears as $y) <option value="{{ $y }}">{{ $y }}</option> @endforeach
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 0.5fr 1fr 1fr; gap: 10px; font-weight:bold; margin-bottom:5px; text-align:center; font-size:0.9rem;">
                <div>Bulan</div><div style="color:#2563eb;">PKT</div><div style="color:#ea580c;">KDM</div>
            </div>
            <div style="max-height:400px; overflow-y:auto; padding-right:5px;">
                @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $idx => $m)
                <div style="display: grid; grid-template-columns: 0.5fr 1fr 1fr; gap: 10px; margin-bottom: 8px; align-items:center;">
                    <div style="font-size:0.9rem;">{{ $m }}</div>
                    <input type="number" step="0.01" name="pkt[]" id="input_pkt_{{ $idx }}" class="edit-input" placeholder="0" style="padding:6px; border:1px solid #ddd; border-radius:4px;">
                    <input type="number" step="0.01" name="kdm[]" id="input_kdm_{{ $idx }}" class="edit-input" placeholder="0" style="padding:6px; border:1px solid #ddd; border-radius:4px;">
                </div>
                @endforeach
            </div>
            <div style="display:flex; gap:10px; margin-top:20px; border-top:1px solid #eee; padding-top:15px;">
                <button type="button" onclick="document.getElementById('editModal').style.display='none'" style="flex:1; padding:10px; background:#f1f5f9; border:none; cursor:pointer;">Batal</button>
                <button type="submit" class="btn-action btn-blue" style="flex:2; justify-content:center;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. SETUP CHART
    const ctx = document.getElementById('chartSs4');
    if(ctx && window.chartSeries) {
        const datasets = [];
        let colorIdx = 0;
        for (const [year, dataArr] of Object.entries(window.chartSeries)) {
            datasets.push({
                label: year,
                data: dataArr,
                backgroundColor: window.chartColors[colorIdx % window.chartColors.length],
                borderRadius: 4,
                borderWidth: 0,
                barPercentage: 0.7,
                categoryPercentage: 0.8
            });
            colorIdx++;
        }

        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                datasets: datasets 
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { position: 'top', align: 'end' } }, 
                scales: { 
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } } 
                },
                interaction: { mode: 'index', intersect: false }
            }
        });
    }

    // 2. LOGIC PILIH DATA & SELECT ALL
    let isSelectionMode = false;
    
    // Toggle Tombol "Pilih"
    function toggleSelectionMode() {
        isSelectionMode = !isSelectionMode;
        const cols = document.querySelectorAll('.col-check');
        const btn = document.getElementById('btnSelectMode');
        const btnDelete = document.getElementById('btnDelete');
        const checkAll = document.getElementById('chkSelectAll');

        // Reset status checkbox
        if(!isSelectionMode) {
            document.querySelectorAll('.chk-item').forEach(c => c.checked = false);
            if(checkAll) checkAll.checked = false;
        }

        // Show/Hide Column
        cols.forEach(el => {
            if(el.classList.contains('chk-item') || el.id === 'chkSelectAll') { 
                el.style.display = isSelectionMode ? 'inline-block' : 'none'; 
            } else { 
                // Jika itu cell kosong (bukan input), atur displaynya
                el.style.display = isSelectionMode ? 'table-cell' : 'none'; 
            }
        });

        // Ganti Tampilan Tombol
        if(isSelectionMode) {
            btn.innerHTML = '<i class="fa-solid fa-times"></i> Batal';
            btn.style.background = '#64748b';
        } else {
            btn.innerHTML = '<i class="fa-regular fa-square-check"></i> Pilih';
            btn.style.background = '#6366f1';
            if(btnDelete) btnDelete.style.display = 'none';
        }
    }

    // LOGIC CHECKBOX INDIVIDUAL
    document.addEventListener('change', function(e) {
        if(e.target.classList.contains('chk-item')) {
            updateDeleteButton();
        }
    });

    // LOGIC CHECKBOX MASTER (SELECT ALL)
    const checkAllBox = document.getElementById('chkSelectAll');
    if(checkAllBox) {
        checkAllBox.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.chk-item').forEach(c => {
                // Hanya centang yang terlihat (visible)
                if(c.offsetParent !== null) {
                    c.checked = isChecked;
                }
            });
            updateDeleteButton();
        });
    }

    // FUNGSI UPDATE TOMBOL DELETE
    function updateDeleteButton() {
        const any = document.querySelectorAll('.chk-item:checked').length > 0;
        const btnDelete = document.getElementById('btnDelete');
        if(btnDelete) btnDelete.style.display = any ? 'inline-flex' : 'none';
    }

    // 3. UTILS LAINNYA
    function downloadChart() { const c = document.getElementById('chartSs4'); const l = document.createElement('a'); l.download = 'Grafik_SS4.png'; l.href = c.toDataURL(); l.click(); }
    
    function openEditModal() { 
        document.getElementById('editModal').style.display = 'flex'; 
        loadEditData(); 
    }
    
    function loadEditData() {
        const yr = document.getElementById('edit_year').value;
        document.querySelectorAll('.edit-input').forEach(e => e.value = '');
        if(window.dbDataRaw) {
            const filtered = window.dbDataRaw.filter(row => row.record_date.startsWith(yr));
            filtered.forEach(row => {
                let d = new Date(row.record_date);
                let mIdx = d.getMonth();
                if(document.getElementById('input_pkt_' + mIdx)) {
                    document.getElementById('input_pkt_' + mIdx).value = row.kwh_pkt;
                    document.getElementById('input_kdm_' + mIdx).value = row.kwh_kdm;
                }
            });
        }
    }

    function downloadTable(t) {
        let table = document.getElementById('tableSs4');
        let rows = table.querySelectorAll('tr');
        let csv = [];
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll('td, th');
            for (let j = 0; j < cols.length; j++) {
                if(cols[j].classList.contains('col-check')) continue;
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s)/gm, " ");
                data = data.replace(/"/g, '""');
                row.push('"' + data + '"');
            }
            csv.push(row.join(","));
        }
        if (t == 'csv') {
            let csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
            let link = document.createElement("a");
            link.download = 'Laporan_SS4.csv';
            link.href = window.URL.createObjectURL(csvFile);
            link.click();
        } else {
            let excelFile = '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><body><table>' + table.innerHTML + '</table></body></html>';
            let blob = new Blob([excelFile], {type: 'application/vnd.ms-excel'});
            let link = document.createElement("a");
            link.download = 'Laporan_SS4.xls';
            link.href = window.URL.createObjectURL(blob);
            link.click();
        }
    }
</script>
@endsection