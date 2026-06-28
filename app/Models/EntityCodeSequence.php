<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityCodeSequence extends Model
{
    protected $primaryKey = 'prefix';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'prefix',
        'last_number',
    ];

    protected function casts(): array
    {
        return [
            'last_number' => 'integer',
        ];
    }
}
