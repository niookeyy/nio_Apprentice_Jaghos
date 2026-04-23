<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    public function extensions()
    {
        return $this->belongsToMany(Extension::class, 'extension_categories');
    }
}