<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceUsage extends Model
{
    // Arahkan ke tabel data harian device
    protected $table = 'device_usages';
    
    protected $guarded = ['id'];
}