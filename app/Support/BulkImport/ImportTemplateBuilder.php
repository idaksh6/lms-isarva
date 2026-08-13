<?php

namespace App\Support\BulkImport;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
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
}
