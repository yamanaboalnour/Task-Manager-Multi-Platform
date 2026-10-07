<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SurveyAnswer extends Model
{
    protected $fillable = [
        'response_id',
        'question_id',
        'answer_text',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(
            SurveyQuestionOption::class,
            'survey_answer_options',
            'answer_id',
            'option_id'
        )->orderBy('survey_question_options.position');
    }
}
