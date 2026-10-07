<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyQuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'label',
        'position',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class);
    }
}
