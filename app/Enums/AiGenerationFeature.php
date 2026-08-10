<?php

namespace App\Enums;

enum AiGenerationFeature: string
{
    case RemediationPack = 'remediation_pack';
    case QuizFromMaterials = 'quiz_from_materials';
    case FeedbackDraft = 'feedback_draft';
    case MaterialSummary = 'material_summary';
    case StudentDoubt = 'student_doubt';

    public function label(): string
    {
        return match ($this) {
            self::RemediationPack => 'Remediation pack',
            self::QuizFromMaterials => 'Quiz from materials',
            self::FeedbackDraft => 'Feedback draft',
            self::MaterialSummary => 'Material summary',
            self::StudentDoubt => 'Student doubt assist',
        };
    }

    public function isEnabled(): bool
    {
        return (bool) config('ai.features.'.$this->value, false);
    }
}
