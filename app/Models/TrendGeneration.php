<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendGeneration extends Model
{
    use HasFactory;
    
    protected $table = 'trend_generations';
    
    protected $fillable = [
        'record_date', 
        'kwh_spu', 
        'kwh_ss5', 
        'source_file'
    ];
}