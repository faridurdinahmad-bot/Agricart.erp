<?php

namespace App\Models\Catalog;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryUrlRedirect extends Model
{
    protected $table = 'catalog_category_url_redirects';

    protected $fillable = [
        'category_id',
        'old_url',
        'new_url',
        'redirect_status',
        'changed_at',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'redirect_status' => 'integer',
            'changed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
