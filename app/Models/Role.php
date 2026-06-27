<?php

namespace App\Models;

use App\Core\Authorization\PermissionCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_system',
        'is_active',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->slug === PermissionCatalog::SUPER_ADMIN_SLUG;
    }

    public function isProtected(): bool
    {
        return $this->is_system || $this->isSuperAdmin();
    }
}
