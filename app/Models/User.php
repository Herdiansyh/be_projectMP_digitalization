<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'npk',
        'department_id',
        'section_id',
        'role_level_id',
        'username',
        'photo',
        'director_id',
        'is_admin',
        'can_view_manpower',
        'approver_manager_id',
        'approver_section_head_id',
        'approver_division_id',
        'approver_director_id',
        'area_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
 protected function casts(): array
{
    return [
        'password'             => 'hashed',
        'last_login_at'        => 'datetime',
        'can_view_manpower'    => 'boolean',
        'is_admin'             => 'boolean',
        'department_id'        => 'integer',
        'section_id'           => 'integer',
        'role_level_id'        => 'integer',
        'director_id'          => 'integer',
        'approver_manager_id'  => 'integer',
        'approver_section_head_id' => 'integer',
        'approver_division_id' => 'integer',
        'approver_director_id' => 'integer',
        'area_id'              => 'integer',
    ];
}

    /**
     * Get the department that owns the user.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the section that owns the user.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the role level that owns the user.
     */
    public function roleLevel(): BelongsTo
    {
        return $this->belongsTo(RoleLevel::class);
    }

    public function area(): BelongsTo
{
    return $this->belongsTo(Area::class);
}
    /**
     * Get the director that owns the user.
     */
    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Get the approver manager for the user.
     */
    public function approverManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_manager_id');
    }

    /**
     * Get the approver division for the user.
     */
    public function approverDivision(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_division_id');
    }

    /**
     * Get the approver section head for the user.
     */
    public function approverSectionHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_section_head_id');
    }

    /**
     * Get the approver director for the user.
     */
    public function approverDirector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_director_id');
    }

    /**
     * Get the subordinates for the user.
     */
    public function subordinates()
    {
        return $this->hasMany(User::class, 'director_id');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function roleLevels(): BelongsToMany
{
    return $this->belongsToMany(RoleLevel::class, 'user_role_levels');
}
}
