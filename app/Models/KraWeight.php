<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KraWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'rank_category',
        'kra1_weight',
        'kra2_weight',
        'kra3_weight',
        'kra4_weight',
    ];

    public function facultyRank()
    {
        return $this->belongsTo(FacultyRank::class);
    }
}
