<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });

        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->string('text');
            $table->string('type', 30);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->unique(['survey_id', 'position']);
        });

        Schema::create('survey_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('survey_questions')->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->unique(['question_id', 'position']);
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->unique(['survey_id', 'user_id']);
        });

        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('survey_responses')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('survey_questions');
            $table->text('answer_text')->nullable();
            $table->timestamps();
            $table->unique(['response_id', 'question_id']);
        });

        Schema::create('survey_answer_options', function (Blueprint $table) {
            $table->foreignId('answer_id')->constrained('survey_answers')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('survey_question_options');
            $table->primary(['answer_id', 'option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answer_options');
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_question_options');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
    }
};
