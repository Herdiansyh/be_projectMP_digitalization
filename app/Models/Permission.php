<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['key', 'group', 'label'];

    public function roleLevels(): BelongsToMany
    {
        return $this->belongsToMany(RoleLevel::class, 'role_level_permissions');
    }
}