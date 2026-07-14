<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BorrowRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'item_id',
        'requested_date',
        'expected_return_date',
        'purpose',
        'status',
        'admin_remarks',
        'qr_code_hash',
        'return_condition', // <--- ADDED THIS
        'return_remarks'    // <--- ADDED THIS
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}