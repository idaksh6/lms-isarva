<?php

namespace App\Support\BulkImport;

use Illuminate\Validation\ValidationException;

class McqImportParser
{
    public const TYPE_QUIZ = 'QUIZ';

    public const TYPE_QUESTION_BANK = 'QUESTION_BANK';

    /**
     * @return list<array{prompt: string, options: list<array{label: string}>, correct: int}>
     */
    public function parse(string $text): array
    {
        $type = $this->detectType($text);
        if (! in_array($type, [self::TYPE_QUIZ, self::TYPE_QUESTION_BANK], true)) {
            throw ValidationException::withMessages([
                'import_file' => 'This file is not a quiz/question-bank template. The first line must be LMS_IMPORT: QUIZ or LMS_IMPORT: QUESTION_BANK.',
            ]);
        }

        $body = preg_replace('/^\s*LMS_IMPORT:\s*(QUIZ|QUESTION_BANK)\s*/i', '', $text, 1) ?? $text;
        $blocks = preg_split('/(?=^Q\d+\.\s)/mi', trim($body)) ?: [];

        $questions = [];
        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '' || ! preg_match('/^Q\d+\./i', $block)) {
                continue;
            }

            $questions[] = $this->parseBlock($block, count($questions) + 1);
        }

        if ($questions === []) {
            throw ValidationException::withMessages([
                'import_file' => 'No questions found. Use blocks like: Q1. Prompt / A) … / B) … / ANSWER: A',
            ]);
        }

        if (count($questions) > 50) {
            throw ValidationException::withMessages([
                'import_file' => 'A quiz can have at most 50 questions. Split the file and import in batches.',
            ]);
        }

        return $questions;
    }

    public function detectType(string $text): ?string
    {
        if (! preg_match('/^\s*LMS_IMPORT:\s*([A-Z_]+)/im', $text, $match)) {
            return null;
        }

        return strtoupper($match[1]);
    }

    /**
     * @return array{prompt: string, options: list<array{label: string}>, correct: int}
     */
    private function parseBlock(string $block, int $number): array
    {
        $lines = preg_split('/\n+/', $block) ?: [];
        $promptLine = array_shift($lines) ?? '';
        if (! preg_match('/^Q\d+\.\s*(.+)$/is', trim($promptLine), $promptMatch)) {
            throw ValidationException::withMessages([
                'import_file' => "Question {$number}: missing prompt after Q{$number}.",
            ]);
        }

        $promptParts = [trim($promptMatch[1])];
        $options = [];
        $answer = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^ANSWER:\s*([A-F])\s*$/i', $line, $answerMatch)) {
                $answer = strtoupper($answerMatch[1]);

                continue;
            }

            if (preg_match('/^([A-F])[\)\.\:\-]\s*(.+)$/i', $line, $optionMatch)) {
                $options[] = [
                    'letter' => strtoupper($optionMatch[1]),
                    'label' => trim($optionMatch[2]),
                ];

                continue;
            }

            if ($options === [] && $answer === null) {
                $promptParts[] = $line;
            }
        }

        if (count($options) < 2 || count($options) > 6) {
            throw ValidationException::withMessages([
                'import_file' => "Question {$number}: provide between 2 and 6 options (A) … F).",
            ]);
        }

        if ($answer === null) {
            throw ValidationException::withMessages([
                'import_file' => "Question {$number}: add ANSWER: A (or B, C, …) on its own line.",
            ]);
        }

        $letters = array_column($options, 'letter');
        $correctIndex = array_search($answer, $letters, true);
        if ($correctIndex === false) {
            throw ValidationException::withMessages([
                'import_file' => "Question {$number}: ANSWER {$answer} does not match an option letter.",
            ]);
        }

        return [
            'prompt' => trim(implode("\n", $promptParts)),
            'options' => array_map(fn (array $opt) => ['label' => $opt['label']], $options),
            'correct' => $correctIndex + 1,
        ];
    }
}
