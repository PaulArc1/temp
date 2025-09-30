<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'team_id',
    ];

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function scopeMine()
    {
        return $this->where('team_id', auth()->user()->currentTeam->id);
    }
}
