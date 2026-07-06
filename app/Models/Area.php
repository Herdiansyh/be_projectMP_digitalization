<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Lines yang berada di bawah area ini (relasi ditambahkan
     * saat modul Line dibuat, ada di sini sebagai referensi awal).
     */
    public function lines(): HasMany
    {
        return $this->hasMany(Line::class);
    }
}