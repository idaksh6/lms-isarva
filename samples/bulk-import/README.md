# Bulk import templates (Word / PDF / TXT)

Use these templates to create quiz questions or assignments without typing them one-by-one in the LMS.

## Import types

| Header line | Used for | Upload location |
|-------------|----------|-----------------|
| `LMS_IMPORT: QUIZ` | In-LMS MCQ assessment | Assessment → Edit quiz → Bulk import |
| `LMS_IMPORT: QUESTION_BANK` | Same MCQ format (reusable bank file) | Same as quiz — apply into a quiz |
| `LMS_IMPORT: ASSIGNMENTS` | One or more coursework assignments | Course → New assignment → Bulk import |

## Supported files

- Microsoft Word: `.docx` (preferred), `.doc` when Word can save as modern format
- PDF: text-based PDF (not a scanned image)
- Plain text: `.txt` (handy for testing)

Download starter Word templates from **Bulk import templates** in the app, or copy the `.txt` samples in this folder into Word/PDF.

## Word macro

1. Open Word → `Alt+F11`
2. Import `LmsBulkImportMacro.bas`
3. Run:
   - `InsertQuizHeader` / `InsertQuizQuestion`
   - `InsertQuestionBankHeader`
   - `InsertAssignmentsHeader` / `InsertAssignmentBlock`
   - `ValidateLmsImport` before upload

Save as `.docx` (or export PDF), then upload in the LMS.

## Quiz / question bank shape

```text
LMS_IMPORT: QUIZ

Q1. Prompt…
A) …
B) …
C) …
D) …
ANSWER: B
```

- 2–6 options (`A)` … `F)`)
- One `ANSWER:` letter per question
- Up to 50 questions per import (assessment `question_count` is updated to match)

## Assignments shape

```text
LMS_IMPORT: ASSIGNMENTS

---
TITLE: …
INSTRUCTIONS: …
DELIVERY: file
DUE: 2026-09-15 23:59
PUBLISH: no
---
```

- `DELIVERY`: `file` | `link` | `both`
- `DROP_FOLDER_URL` required when delivery is `link` or `both`
- `PUBLISH`: `yes` / `no`
