<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectricitySupply extends Model
{
    // Arahkan ke tabel data listrik SS-4
    protected $table = 'electricity_supplies_ss4';
    
    protected $guarded = ['id'];
}