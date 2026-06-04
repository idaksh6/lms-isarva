# Manual testing — ISARVA LMS

This folder contains repeatable manual test cases for QA and UAT.

## Files

| File | Purpose |
|------|---------|
| [TEST_CASES.md](./TEST_CASES.md) | Full step-by-step test plan (Tests 1–15) |

## How to use

1. Set up the app (see **Prerequisites** in `TEST_CASES.md`).
2. Run tests **in order** — later tests depend on data created earlier (especially Tests 3–4 and 7).
3. After each test, mark **Pass** / **Fail** and note any screenshots or errors.
4. Use one browser profile per role, or **logout** between role switches.

## Quick reference

- **URL:** http://127.0.0.1:8000
- **Default password (all demo users):** `password`
- **Primary course for tests:** DS501 — Machine Learning Foundations

For upload tests above 2 MB locally, start the server with `composer serve` (not plain `php artisan serve`). Details are in the test plan.
