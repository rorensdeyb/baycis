<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyTagSequence extends Model
{
    protected $fillable = [
        'year',
        'category_code',
        'location_code',
        'last_number',
    ];
}