<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KraWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_rank_id',
        'instruction_weight',
        'research_weight',
        'extension_weight',
        'professional_development_weight',
    ];

    public function facultyRank()
    {
        return $this->belongsTo(FacultyRank::class);
    }
}
