<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PltsGeneration extends Model
{
    // Arahkan ke nama tabel yang kita buat di SQL kemarin
    protected $table = 'plts_generations'; 
    
    // Izinkan semua kolom diisi secara otomatis
    protected $guarded = ['id'];

    // LOGIC CERDAS: Deteksi Phase berdasarkan Nama Plant
    // Jadi Admin tidak perlu repot pilih phase manual
    public static function getPhaseGroup($plantName)
    {
        // Daftar keyword untuk Phase 1
        $phase1Keywords = ['KANTOR PUSAT', 'KANTIN', 'DIKLAT'];
        
        foreach ($phase1Keywords as $keyword) {
            // Jika nama plant mengandung kata kunci di atas...
            if (stripos($plantName, $keyword) !== false) {
                return 'Phase 1';
            }
        }
        
        // Sisanya otomatis dianggap Phase 2 (PPNP, Flake, Lab, Manset, dll)
        return 'Phase 2'; 
    }
}