<?php

namespace App\Support\BulkImport;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportTemplateBuilder
{
    public function quizPlainText(): string
    {
        return <<<'TXT'
LMS_IMPORT: QUIZ

Q1. Which statement best describes a relational database primary key?
A) It may be null on every row
B) It uniquely identifies each row
C) It must be a foreign key from another table
D) It is only used for sorting reports
ANSWER: B

Q2. What does SQL stand for?
A) Structured Query Language
B) Simple Quick Lookup
C) System Queue Logic
D) Secure Query Layer
ANSWER: A
TXT;
    }

    public function questionBankPlainText(): string
    {
        return str_replace('LMS_IMPORT: QUIZ', 'LMS_IMPORT: QUESTION_BANK', $this->quizPlainText());
    }

    public function assignmentsPlainText(): string
    {
        return <<<'TXT'
LMS_IMPORT: ASSIGNMENTS

---
TITLE: Lab 1 — Data cleaning
INSTRUCTIONS: Clean the sample dataset and submit a short report with screenshots of your steps.
DELIVERY: file
DUE: 2026-09-15 23:59
PUBLISH: no
---
TITLE: Essay — Ethics in AI
INSTRUCTIONS: Write 500–700 words on fairness in automated decision systems. Cite at least two sources.
DELIVERY: both
DROP_FOLDER_URL: https://drive.google.com/drive/folders/example
DUE: 2026-09-22 23:59
PUBLISH: yes
---
TXT;
    }

    public function download(string $kind, string $format): StreamedResponse
    {
        return match ($format) {
            'txt' => $this->downloadTxt($kind),
            'xlsx', 'xls', 'excel' => $this->downloadXlsx($kind),
            default => $this->downloadDocx($kind),
        };
    }

    public function downloadDocx(string $kind): StreamedResponse
    {
        [$filename, $body] = match ($kind) {
            'quiz' => ['lms-quiz-template.docx', $this->quizPlainText()],
            'question-bank' => ['lms-question-bank-template.docx', $this->questionBankPlainText()],
            'assignments' => ['lms-assignments-template.docx', $this->assignmentsPlainText()],
            default => abort(404),
        };

        $phpWord = new PhpWord;
        $section = $phpWord->addSection();
        $section->addText(
            'LMS bulk import template — keep the LMS_IMPORT line and field labels exactly as shown.',
            ['italic' => true, 'size' => 10]
        );
        $section->addTextBreak();

        foreach (preg_split("/\n/", $body) ?: [] as $line) {
            $section->addText($line === '' ? ' ' : $line, ['name' => 'Consolas', 'size' => 11]);
        }

        $temp = tempnam(sys_get_temp_dir(), 'lms-tpl-');
        if ($temp === false) {
            abort(500, 'Could not create temporary template file.');
        }
        $docxPath = $temp.'.docx';
        @unlink($temp);

        IOFactory::createWriter($phpWord, 'Word2007')->save($docxPath);

        return response()->streamDownload(function () use ($docxPath): void {
            readfile($docxPath);
            @unlink($docxPath);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    public function downloadTxt(string $kind): StreamedResponse
    {
        [$filename, $body] = match ($kind) {
            'quiz' => ['lms-quiz-template.txt', $this->quizPlainText()],
            'question-bank' => ['lms-question-bank-template.txt', $this->questionBankPlainText()],
            'assignments' => ['lms-assignments-template.txt', $this->assignmentsPlainText()],
            default => abort(404),
        };

        return response()->streamDownload(function () use ($body): void {
            echo $body;
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function downloadXlsx(string $kind): StreamedResponse
    {
        [$filename, $headers, $rows] = match ($kind) {
            'quiz', 'question-bank' => [
                $kind === 'question-bank' ? 'lms-question-bank-template.xlsx' : 'lms-quiz-template.xlsx',
                ['prompt', 'option_a', 'option_b', 'option_c', 'option_d', 'answer'],
                [
                    [
                        'Which statement best describes a relational database primary key?',
                        'It may be null on every row',
                        'It uniquely identifies each row',
                        'It must be a foreign key from another table',
                        'It is only used for sorting reports',
                        'B',
                    ],
                    [
                        'What does SQL stand for?',
                        'Structured Query Language',
                        'Simple Quick Lookup',
                        'System Queue Logic',
                        'Secure Query Layer',
                        'A',
                    ],
                ],
            ],
            'assignments' => [
                'lms-assignments-template.xlsx',
                ['title', 'instructions', 'delivery', 'drop_folder_url', 'due', 'publish'],
                [
                    [
                        'Lab 1 — Data cleaning',
                        'Clean the sample dataset and submit a short report with screenshots of your steps.',
                        'file',
                        '',
                        '2026-09-15 23:59',
                        'no',
                    ],
                    [
                        'Essay — Ethics in AI',
                        'Write 500–700 words on fairness in automated decision systems. Cite at least two sources.',
                        'both',
                        'https://drive.google.com/drive/folders/example',
                        '2026-09-22 23:59',
                        'yes',
                    ],
                ],
            ],
            default => abort(404),
        };

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($kind === 'assignments' ? 'Assignments' : 'Questions');
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);

        $temp = tempnam(sys_get_temp_dir(), 'lms-xlsx-');
        if ($temp === false) {
            abort(500, 'Could not create temporary Excel template.');
        }
        $xlsxPath = $temp.'.xlsx';
        @unlink($temp);

        (new XlsxWriter($spreadsheet))->save($xlsxPath);

        return response()->streamDownload(function () use ($xlsxPath): void {
            readfile($xlsxPath);
            @unlink($xlsxPath);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
