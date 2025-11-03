<?php

namespace App\Models;

use App\Observers\SubmissionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([SubmissionObserver::class])]
class Submission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'application_id',
        'category',
        'type',
        'data',
        'filename',
        'google_drive_file_id',
        'score',
        'status',
        'raw_score', // Make sure you added this from the migration
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'google_drive_file_id' => 'array',
    ];

    /**
     * Get the user that owns the submission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the application that the submission belongs to.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
