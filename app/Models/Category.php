<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model {
    protected $fillable = [
        'name',
        'ppe_sub_major',
        'gl_ledger_acct',
    ];
    public function items(): HasMany { return $this->hasMany(Item::class); }

    public function tags(): HasMany { return $this->hasMany(AssetTag::class); }
}