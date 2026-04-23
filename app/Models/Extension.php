<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    protected $fillable = [
        'extension',
        'group',
        'register_price',
        'transfer_price',
        'gimmick_price',
        'terms_and_conditions',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'extension_categories');
    }
}