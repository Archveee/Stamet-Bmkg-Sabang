<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class GtsMessage extends Model
{
    use Userstamps;
    
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'timestamp_data' => 'datetime',
        'timestamp_sent_data' => 'datetime',
    ];
    
}
