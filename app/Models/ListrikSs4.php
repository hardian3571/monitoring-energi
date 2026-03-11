<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListrikSs4 extends Model
{
    use HasFactory;

    // Nama Tabel di Database
    protected $table = 'listrik_ss4s';

    // Izinkan semua kolom diisi
    protected $guarded = [];
}