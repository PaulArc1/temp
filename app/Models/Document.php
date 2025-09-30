<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'name',
        'file',
        'inspected_on',
        'meta',
    ];

    protected $casts = [
        'inspected_on' => 'date',
        'meta' => 'json',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            $document->uuid = \Str::uuid();
        });
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pageItems()
    {
        return $this->hasManyThrough(PageItem::class, Page::class);
    }
}
