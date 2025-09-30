<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageItem extends Model
{
    protected $fillable = [
        'uuid',
        'page_id',
        //'label',
        'values',
        'data',
    ];

    protected $casts = [
        'data' => 'json',
        'values' => 'json',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
