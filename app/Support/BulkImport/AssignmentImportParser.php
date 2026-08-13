<?php

namespace App\Support\BulkImport;

use App\Enums\SubmissionDeliveryMethod;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AssignmentImportParser
{
    public const TYPE = 'ASSIGNMENTS';

    /**
     * @return list<array{title: string, instructions: ?string, delivery_method: string, drop_folder_url: ?string, due_at: ?string, is_published: bool}>
     */
    public function parse(string $text): array
    {
        if (! preg_match('/^\s*LMS_IMPORT:\s*ASSIGNMENTS\b/im', $text)) {
            throw ValidationException::withMessages([
                'import_file' => 'This file is not an assignments template. The first line must be LMS_IMPORT: ASSIGNMENTS.',
            ]);
        }

        $body = preg_replace('/^\s*LMS_IMPORT:\s*ASSIGNMENTS\s*/i', '', $text, 1) ?? $text;
        $chunks = preg_split('/^\s*---\s*$/m', trim($body)) ?: [];

        $assignments = [];
        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }

            $assignments[] = $this->parseChunk($chunk, count($assignments) + 1);
        }

        if ($assignments === []) {
            throw ValidationException::withMessages([
                'import_file' => 'No assignments found. Separate each assignment with a --- line and include TITLE: …',
            ]);
        }

        if (count($assignments) > 30) {
            throw ValidationException::withMessages([
                'import_file' => 'Import at most 30 assignments at a time.',
            ]);
        }

        return $assignments;
    }

    /**
     * @return array{title: string, instructions: ?string, delivery_method: string, drop_folder_url: ?string, due_at: ?string, is_published: bool}
     */
    private function parseChunk(string $chunk, int $number): array
    {
        $fields = [
            'title' => null,
            'instructions' => null,
            'delivery' => 'file',
            'drop_folder_url' => null,
            'due' => null,
            'publish' => 'no',
        ];

        $lines = preg_split('/\n/', $chunk) ?: [];
        $currentKey = null;
        $buffer = [];

        $flush = function () use (&$currentKey, &$buffer, &$fields): void {
            if ($currentKey === null) {
                return;
            }
            $value = trim(implode("\n", $buffer));
            $fields[$currentKey] = $value === '' ? null : $value;
            $currentKey = null;
            $buffer = [];
        };

        foreach ($lines as $line) {
            if (preg_match('/^(TITLE|INSTRUCTIONS|DELIVERY|DROP_FOLDER_URL|DUE|PUBLISH):\s*(.*)$/i', $line, $match)) {
                $flush();
                $currentKey = strtolower($match[1]);
                $buffer = [trim($match[2])];

                continue;
            }

            if ($currentKey !== null) {
                $buffer[] = $line;
            }
        }
        $flush();

        if (! filled($fields['title'])) {
            throw ValidationException::withMessages([
                'import_file' => "Assignment {$number}: TITLE is required.",
            ]);
        }

        $delivery = strtolower((string) ($fields['delivery'] ?? 'file'));
        if (! in_array($delivery, ['file', 'link', 'both'], true)) {
            throw ValidationException::withMessages([
                'import_file' => "Assignment {$number}: DELIVERY must be file, link, or both.",
            ]);
        }

        $dropFolder = $fields['drop_folder_url'];
        if (in_array($delivery, ['link', 'both'], true) && ! filled($dropFolder)) {
            throw ValidationException::withMessages([
                'import_file' => "Assignment {$number}: DROP_FOLDER_URL is required when DELIVERY is link or both.",
            ]);
        }

        $dueAt = null;
        if (filled($fields['due'])) {
            try {
                $dueAt = Carbon::parse($fields['due'])->toDateTimeString();
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    'import_file' => "Assignment {$number}: DUE date could not be parsed (try 2026-09-15 23:59).",
                ]);
            }
        }

        $publishRaw = strtolower((string) ($fields['publish'] ?? 'no'));
        $isPublished = in_array($publishRaw, ['yes', 'y', 'true', '1', 'publish'], true);

        return [
            'title' => mb_substr((string) $fields['title'], 0, 255),
            'instructions' => $fields['instructions'],
            'delivery_method' => SubmissionDeliveryMethod::from($delivery)->value,
            'drop_folder_url' => $dropFolder,
            'due_at' => $dueAt,
            'is_published' => $isPublished,
        ];
    }
}
