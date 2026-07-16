<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'line_id',

    ];
     public function line()
    {
        return $this->belongsTo(Line::class);
    }

    // Opsional, kalau butuh akses area langsung dari station
    public function area()
    {
        return $this->hasOneThrough(Area::class, Line::class, 'id', 'id', 'line_id', 'area_id');
    }
}