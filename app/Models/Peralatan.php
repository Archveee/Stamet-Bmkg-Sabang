<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Peralatan extends Model
{
    use HasUuids;

    protected $table = 'peralatan';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'last_update' => 'datetime',
    ];
    
}
