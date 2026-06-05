# Data Science LMS

A focused learning management system for a Data Science academic programme — built with **Laravel 13**, **Breeze**, and **Tailwind CSS**.

## Features (v1)

- **Roles:** Admin, Lecturer, Student
- **Courses:** Lecturers create courses; enroll students per course
- **Assignments:** Lecturers upload instructions and optional attachments with due dates
- **Submissions:** Students upload their work; lecturers review submissions

## Requirements

- PHP 8.3+ with `pdo_mysql` extension
- **MySQL** 8+ (XAMPP, MAMP, or Homebrew `mysql`)
- Composer
- Node.js 18+ and npm

## MySQL setup (XAMPP)

1. Start **XAMPP** and turn on **MySQL** (Apache is optional — Laravel uses `php artisan serve`).
2. Create the database in phpMyAdmin or run:

```sql
CREATE DATABASE ds_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Set credentials in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ds_lms
DB_USERNAME=root
DB_PASSWORD=
```

## Quick start

```bash
composer install
cp .env.example .env   # if needed
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000) — you will go straight to the **sign-in** page.

```bash
composer run dev
```

## Demo logins (after seeding)

| Role     | Email               | Password  |
|----------|---------------------|-----------|
| Admin    | admin@lms.test      | password  |
| Lecturer | lecturer@lms.test   | password  |
| Student  | student1@lms.test   | password  |

Students `student2` … `student5` use `@lms.test`.

## Manual testing

Step-by-step UAT test cases (Tests 1–15) are in [docs/manual-testing/TEST_CASES.md](docs/manual-testing/TEST_CASES.md).

## Deploy to server (CyberPanel)

- **CI/CD pipeline guide (Word):** [docs/deploy/CI_CD_PIPELINE_INTEGRATION.docx](docs/deploy/CI_CD_PIPELINE_INTEGRATION.docx)
- **CI/CD pipeline guide (Markdown):** [docs/deploy/CI_CD_PIPELINE_INTEGRATION.md](docs/deploy/CI_CD_PIPELINE_INTEGRATION.md)
- **Already uploaded zip?** [docs/deploy/ZIP_TO_AUTO_DEPLOY.md](docs/deploy/ZIP_TO_AUTO_DEPLOY.md)
- **GitHub Actions reference:** [docs/deploy/GITHUB_AUTO_DEPLOY.md](docs/deploy/GITHUB_AUTO_DEPLOY.md)

## Project structure

- `app/Http/Controllers/` — Course, assignment, submission, admin user flows
- `app/Policies/` — Access control per role
- `resources/views/` — Blade UI with `layouts/lms.blade.php` sidebar layout
