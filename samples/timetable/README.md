# Sample timetable CSV files (local testing)

Use these when testing **Class schedule → Import semester timetable** on a course.

## Before you import

1. Log in as **lecturer** (or admin).
2. Open the course → **Edit course** → set **Semester** to `2026-S1` → Save.
3. Go to **Class schedule** on that same course.

## Files

| File | Purpose |
|------|---------|
| `sample-timetable-2026-S1.csv` | Full test: 5 sessions + 1 row for semester `2026-S2` (should be skipped) |
| `sample-timetable-minimal.csv` | Single online session — quick smoke test |

## CSV columns

```
title, starts_at, ends_at, mode, meeting_link, location, semester, notes
```

- **starts_at** / **ends_at** — required format: `YYYY-MM-DD HH:MM:SS`
- **mode** — `online` or `offline`
- **meeting_link** — use for online rows
- **location** — use for offline rows
- **semester** — must match the course semester or the row is skipped

## Expected result (full sample on course with semester `2026-S1`)

- **5** class sessions created
- **1** row skipped (2026-S2 row)
- Success message: `Timetable imported: 5 class sessions created, 1 rows skipped.`
