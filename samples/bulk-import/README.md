# Bulk import templates (Excel / Word / PDF / TXT)

Use these templates to create quiz questions or assignments without typing them one-by-one in the LMS.

## Import types

| Format | Used for | Upload location |
|--------|----------|-----------------|
| Excel quiz / question bank | In-LMS MCQ assessment | Assessment → Edit quiz → Bulk import |
| Excel assignments | Coursework assignments | Course → New assignment → Bulk import |
| Word/TXT `LMS_IMPORT: QUIZ` / `QUESTION_BANK` | Same MCQs | Same as quiz |
| Word/TXT `LMS_IMPORT: ASSIGNMENTS` | Same assignments | Same as assignment import |

## Supported files

- **Excel:** `.xlsx` (preferred), `.xls`
- Microsoft Word: `.docx` (preferred), `.doc` when readable
- PDF: text-based PDF (not a scanned image)
- Plain text: `.txt` (handy for testing)

Download starter templates from **Bulk import** in the app.

## Excel quiz / question bank columns

| prompt | option_a | option_b | option_c | option_d | answer |
|--------|----------|----------|----------|----------|--------|
| Question text | Choice A | Choice B | Choice C | Choice D | `B` |

- Optional `option_e` / `option_f`
- `answer` must be `A`–`F` (or `1`–`6`) matching a filled option
- Up to 50 questions per import

## Excel assignments columns

| title | instructions | delivery | drop_folder_url | due | publish |
|-------|--------------|----------|-----------------|-----|---------|
| Lab 1 | … | `file` / `link` / `both` | required for link/both | `2026-09-15 23:59` | `yes` / `no` |

## Word / TXT quiz shape

```text
LMS_IMPORT: QUIZ

Q1. Prompt…
A) …
B) …
C) …
D) …
ANSWER: B
```

## Word macro

1. Open Word → `Alt+F11`
2. Import `LmsBulkImportMacro.bas`
3. Run `InsertQuizQuestion` / `InsertAssignmentBlock`, then `ValidateLmsImport`
4. Save as `.docx` (or export PDF) and upload

Excel does not need the macro — fill the spreadsheet columns directly.
