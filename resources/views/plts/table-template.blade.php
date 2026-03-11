<div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
        <thead>
            <tr style="background: #f8fafc;">
                <th colspan="{{ $years->count() + 1 }}" class="area-title" style="padding: 10px 15px; text-align: left; border-bottom: 2px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">{{ $locName }}</span>
                        
                        @if(auth()->user()->role == 'admin')
                            <div style="display: flex; gap: 8px;">
                                <button type="button" onclick="openEditModal('{{ $locName }}', '{{ $currentPhase }}')" style="background: #f59e0b; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.75rem; display: flex; align-items: center; gap: 6px; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.1); transition: all 0.2s ease;">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit Data
                                </button>
                                
                                <button type="button" onclick="deleteAreaFull('{{ $locName }}', '{{ $currentPhase }}')" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.75rem; display: flex; align-items: center; gap: 6px; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.1); transition: all 0.2s ease;">
                                    <i class="fa-solid fa-trash"></i> Hapus Area
                                </button>
                            </div>
                        @endif
                    </div>
                </th>
            </tr>
            <tr style="background: #f1f5f9;">
                <th style="padding: 10px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; width: 120px; font-weight: 600; color: #475569;">Bulan</th>
                @foreach($years as $y)
                    <th style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: center; font-weight: 600; color: #475569;">{{ $y }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @for($m = 1; $m <= 12; $m++)
                <tr style="border-bottom: 1px solid #f1f5f9; hover: background-color: #f8fafc; transition: background-color 0.15s ease;">
                    <td style="padding: 8px 15px; font-weight: 500; color: #64748b;">{{ $bulanIndo[$m] }}</td>
                    
                    @foreach($years as $y)
                        <td style="padding: 8px; text-align: center; color: #334155; font-family: 'Segoe UI Mono', monospace; font-weight: 500;">
                            @if(isset($matrix[$locName][$m][$y]))
                                {{ number_format($matrix[$locName][$m][$y], 0, ',', '.') }}
                            @else
                                <span style="color: #cbd5e1;">-</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endfor
            
            <tr style="background: #f8fafc; font-weight: bold; color: #1e293b; border-top: 2px solid #cbd5e1;">
                <td style="padding: 12px 15px;">TOTAL</td>
                @foreach($years as $y)
                    <td style="padding: 12px 8px; text-align: center; font-size: 0.9rem;">
                        @php
                            $sum = 0;
                            for($i=1; $i<=12; $i++) {
                                // PERBAIKAN BUG ARRAY OFFSET DI SINI JUGA
                                if(isset($matrix[$locName][$i][$y])) {
                                    $sum += $matrix[$locName][$i][$y];
                                }
                            }
                        @endphp
                        {{ number_format($sum, 0, ',', '.') }}
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>
</div>