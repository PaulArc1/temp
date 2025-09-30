<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'equipment_type_id',
        'team_id',
    ];

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }
}
