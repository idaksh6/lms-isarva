<?php

namespace App\Support\BulkImport;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;
use Throwable;

class ExcelImportConverter
{
    /**
     * Convert an Excel quiz / question-bank sheet into LMS_IMPORT text for McqImportParser.
     */
    public function toQuizText(UploadedFile $file): string
    {
        $rows = $this->associativeRows($file);
        if ($rows === []) {
            throw ValidationException::withMessages([
                'import_file' => 'The Excel sheet has no question rows. Use the quiz template headers: prompt, option_a, option_b, option_c, option_d, answer.',
            ]);
        }

        $lines = ['LMS_IMPORT: QUIZ', ''];
        $index = 1;

        foreach ($rows as $row) {
            $prompt = $this->value($row, ['prompt', 'question', 'q']);
            if ($prompt === null || $prompt === '') {
                continue;
            }

            $options = [];
            foreach (['a', 'b', 'c', 'd', 'e', 'f'] as $letter) {
                $label = $this->value($row, [
                    'option_'.$letter,
                    'option'.$letter,
                    $letter,
                    'choice_'.$letter,
                ]);
                if ($label !== null && $label !== '') {
                    $options[$letter] = $label;
                }
            }

            if (count($options) < 2) {
                throw ValidationException::withMessages([
                    'import_file' => "Excel row {$index}: provide at least option_a and option_b.",
                ]);
            }

            $answer = strtoupper((string) ($this->value($row, ['answer', 'correct', 'correct_answer']) ?? ''));
            $answer = preg_replace('/[^A-F1-6]/', '', $answer) ?? '';
            if ($answer !== '' && is_numeric($answer)) {
                $map = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F'];
                $answer = $map[(int) $answer] ?? '';
            }
            if ($answer === '' || ! isset($options[strtolower($answer)])) {
                throw ValidationException::withMessages([
                    'import_file' => "Excel row {$index}: set answer to A–F matching a filled option column.",
                ]);
            }

            $lines[] = "Q{$index}. {$prompt}";
            foreach ($options as $letter => $label) {
                $lines[] = strtoupper($letter).') '.$label;
            }
            $lines[] = 'ANSWER: '.strtoupper($answer);
            $lines[] = '';
            $index++;
        }

        if ($index === 1) {
            throw ValidationException::withMessages([
                'import_file' => 'No quiz questions found in the Excel file. Fill the prompt column and try again.',
            ]);
        }

        return implode("\n", $lines);
    }

    /**
     * Convert an Excel assignments sheet into LMS_IMPORT text for AssignmentImportParser.
     */
    public function toAssignmentsText(UploadedFile $file): string
    {
        $rows = $this->associativeRows($file);
        if ($rows === []) {
            throw ValidationException::withMessages([
                'import_file' => 'The Excel sheet has no assignment rows. Use headers: title, instructions, delivery, drop_folder_url, due, publish.',
            ]);
        }

        $chunks = ['LMS_IMPORT: ASSIGNMENTS', ''];

        foreach ($rows as $row) {
            $title = $this->value($row, ['title', 'assignment', 'name']);
            if ($title === null || $title === '') {
                continue;
            }

            $chunks[] = '---';
            $chunks[] = 'TITLE: '.$title;

            $instructions = $this->value($row, ['instructions', 'instruction', 'description', 'body']);
            if ($instructions !== null && $instructions !== '') {
                $chunks[] = 'INSTRUCTIONS: '.$instructions;
            }

            $delivery = strtolower((string) ($this->value($row, ['delivery', 'delivery_method', 'method']) ?? 'file'));
            $chunks[] = 'DELIVERY: '.($delivery !== '' ? $delivery : 'file');

            $drop = $this->value($row, ['drop_folder_url', 'dropfolder', 'folder_url', 'link_url', 'url']);
            if ($drop !== null && $drop !== '') {
                $chunks[] = 'DROP_FOLDER_URL: '.$drop;
            }

            $due = $this->value($row, ['due', 'due_at', 'deadline', 'due_date']);
            if ($due !== null && $due !== '') {
                $chunks[] = 'DUE: '.$due;
            }

            $publish = $this->value($row, ['publish', 'published', 'is_published']);
            $chunks[] = 'PUBLISH: '.(($publish !== null && $publish !== '') ? $publish : 'no');
            $chunks[] = '---';
            $chunks[] = '';
        }

        if (count($chunks) <= 2) {
            throw ValidationException::withMessages([
                'import_file' => 'No assignments found in the Excel file. Fill the title column and try again.',
            ]);
        }

        return implode("\n", $chunks);
    }

    /**
     * @return list<array<string, string|null>>
     */
    private function associativeRows(UploadedFile $file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (Throwable $e) {
            throw new RuntimeException('Could not read this Excel file. Save as .xlsx and try again.', 0, $e);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = (int) $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        if ($highestRow < 2) {
            return [];
        }

        $headers = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $raw = $sheet->getCell(Coordinate::stringFromColumnIndex($col).'1')->getFormattedValue();
            $headers[$col] = $this->normalizeHeader((string) $raw);
        }

        if (! array_filter($headers)) {
            throw ValidationException::withMessages([
                'import_file' => 'Excel row 1 must contain column headers (for example prompt, option_a, answer).',
            ]);
        }

        $rows = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $assoc = [];
            $empty = true;
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $key = $headers[$col] ?? '';
                if ($key === '') {
                    continue;
                }
                $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($col).$row);
                $value = $cell->getValue();
                if (ExcelDate::isDateTime($cell) && is_numeric($value)) {
                    $value = ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d H:i');
                } else {
                    $value = $cell->getFormattedValue();
                }
                $value = is_string($value) || is_numeric($value) ? trim((string) $value) : null;
                if ($value !== null && $value !== '') {
                    $empty = false;
                }
                $assoc[$key] = $value === '' ? null : $value;
            }
            if (! $empty) {
                $rows[] = $assoc;
            }
        }

        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = str_replace(['-', ' '], '_', $header);
        $header = preg_replace('/_+/', '_', $header) ?? $header;

        return trim($header, '_');
    }

    /**
     * @param  array<string, string|null>  $row
     * @param  list<string>  $keys
     */
    private function value(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return null;
    }
}
