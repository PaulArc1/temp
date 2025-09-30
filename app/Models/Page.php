<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'page_number',
        'uuid',
        'json',
    ];

    protected $casts = [
        'json' => 'json',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function pageItems()
    {
        return $this->hasMany(PageItem::class);
    }
}
