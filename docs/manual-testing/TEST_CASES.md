# ISARVA LMS — Manual test cases

**Version:** 1.0 (June 2026)  
**Application:** ISARVA Data Science LMS  
**Environment:** Local (`http://127.0.0.1:8000`)

---

## Prerequisites

Run once before Test 1:

```bash
cd "/path/to/LMS ISARVA"
composer install
cp .env.example .env   # if needed
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install && npm run build
composer serve
```

| Item | Value |
|------|--------|
| Base URL | http://127.0.0.1:8000 |
| Database | MySQL (see project `README.md` for `.env`) |
| Server for file uploads | Prefer `composer serve` (64 MB limit). Plain `php artisan serve` may cap uploads at 2 MB. |

After `composer serve`, the terminal should mention an upload limit (e.g. 64M).

---

## Demo accounts (from seeder)

| Role | Email | Password |
|------|--------|----------|
| Admin | `admin@lms.test` | `password` |
| Lecturer | `lecturer@lms.test` | `password` (Dr. Priya Sharma) |
| Student | `student1@lms.test` … `student5@lms.test` | `password` |

**Created during Test 3 (keep for later tests):**

| Field | Value |
|--------|--------|
| Email | `test.student@lms.test` |
| Password | `password` |
| Student ID | `DS2024999` |
| Name (after Test 4) | `Test Student Updated` |

---

## Seeded course & assignments (reference)

| Course | Code | Title |
|--------|------|--------|
| Primary test course | DS501 | Machine Learning Foundations |

**Pre-seeded assignments on DS501 (before Test 13):**

| Assignment | Notes |
|------------|--------|
| Linear Regression Lab | Used for submit + review tests |
| Model Evaluation Report | Second assignment on course page |

**Created in Test 13:**

| Assignment | Title |
|------------|--------|
| New assignment | Clustering Mini Project |

---

## Test execution log

| # | Test name | Tester | Date | Result | Notes |
|---|-----------|--------|------|--------|-------|
| 1 | Admin login | | | | |
| 2 | Users page | | | | |
| 3 | Add user | | | | |
| 4 | Edit user | | | | |
| 5 | Lecturer login | | | | |
| 6 | Open course (lecturer) | | | | |
| 7 | Enroll student | | | | |
| 8 | Student login & course | | | | |
| 9 | Submit assignment | | | | |
| 10 | Lecturer review | | | | |
| 11 | Student sees reviewed | | | | |
| 12 | No double submit | | | | |
| 13 | New assignment | | | | |
| 14 | Student sees new assignment | | | | |
| 15 | Admin overview | | | | |

---

## Test 1 — Admin login & access

**Role:** Admin  

**Steps:**

1. Open http://127.0.0.1:8000
2. Sign in with `admin@lms.test` / `password`
3. Confirm redirect to **Dashboard**
4. Confirm sidebar shows **Users** (admin-only)

**Pass if:** Login works and **Users** appears in the sidebar.

---

## Test 2 — Admin: Users page

**Role:** Admin (stay logged in)

**Steps:**

1. Click **Users** in the sidebar
2. Confirm user cards load (Administrator, Lecturer, Students)
3. Confirm each card has **Edit user**
4. Confirm **Add user** at top right

**Pass if:** Users list loads; **Add user** and **Edit user** are visible.

---

## Test 3 — Add a new user

**Role:** Admin

**Steps:**

1. Click **Add user**
2. Fill the form:

   | Field | Value |
   |--------|--------|
   | Full name | `Test Student` |
   | Email | `test.student@lms.test` |
   | Role | Student |
   | Student ID | `DS2024999` |
   | Password | `password` |
   | Confirm password | `password` |

3. Click **Create account**
4. Confirm return to **Users** and new user appears

**Pass if:** Success message and **Test Student** listed with ID `DS2024999`.

---

## Test 4 — Edit a user

**Role:** Admin

**Steps:**

1. On **Users**, open **Edit user** for **Test Student**
2. Change **Full name** to `Test Student Updated`
3. Leave password fields **empty**
4. Click **Save changes**
5. Confirm card shows **Test Student Updated**

**Pass if:** Name updates; optional password help text visible on edit screen.

---

## Test 5 — Lecturer login

**Role:** Lecturer

**Steps:**

1. **Logout**
2. Login `lecturer@lms.test` / `password`
3. Confirm **Dashboard** loads
4. Confirm **Courses** in sidebar
5. Confirm **Users** is **not** in sidebar

**Pass if:** Lecturer logs in; **Users** hidden.

---

## Test 6 — Lecturer: open course & tabs

**Role:** Lecturer

**Steps:**

1. Open **Courses** → **Machine Learning Foundations** (DS501)
2. Confirm course hero: code **DS501**, title, lecturer name, student/assignment counts
3. Confirm tabs: **Edit course**, **Manage students**, **New assignment**
4. Click **Manage students** — page loads; tab looks active
5. Click **Edit course** — form loads; tab active
6. Click **New assignment** — form loads; tab active

**Pass if:** Course opens; all three tabs work without errors.

---

## Test 7 — Enroll student in course

**Role:** Lecturer on DS501 → **Manage students**

**Steps:**

1. In **Add students**, tick **Test Student Updated** (`DS2024999`)
2. Click **Enroll selected**
3. Confirm user appears under **Enrolled students**
4. (Optional) Click **Remove**, then enroll again so they remain for later tests

**Pass if:** Enroll and remove work; student stays enrolled for Tests 8+.

---

## Test 8 — Student login & course access

**Role:** Student

**Steps:**

1. **Logout**
2. Login `test.student@lms.test` / `password`
3. Sidebar: **Users** must **not** appear; **Dashboard** and **Courses** appear
4. Open **Courses** → **Machine Learning Foundations** (DS501)
5. Confirm **no** lecturer tabs (Edit / Manage students / New assignment)
6. Confirm **Assignments** list with published assignments
7. Confirm **Submit work** (or similar) on assignments not yet submitted

**Pass if:** Student sees DS501 and assignments without admin/lecturer controls.

---

## Test 9 — Submit an assignment

**Role:** Student

**Steps:**

1. Open **Linear Regression Lab** → **Submit work**
2. **Notes:** `Submitted my lab notebook for review.`
3. **Upload:** small file (e.g. PDF or TXT; ≤ 2 MB if using default PHP limits, or ≤ 20 MB with `composer serve`)
4. Click **Submit assignment**
5. Return to DS501 course page
6. Confirm assignment shows **View submission** / status badge (not only **Submit work**)

**Pass if:** Submission saves; course page reflects submission.

**If upload fails:** Restart with `composer serve` and retry.

---

## Test 10 — Lecturer reviews submission

**Role:** Lecturer

**Steps:**

1. **Logout** → login `lecturer@lms.test` / `password`
2. **Courses** → DS501 → **Linear Regression Lab**
3. In **Student submissions**, click **View submission** for **Test Student Updated**
4. Confirm **Details** (student, assignment, date, notes)
5. Confirm **Submitted file**: **View in app**, **Download**, PDF preview if PDF
6. Click **Mark as reviewed**
7. Confirm success message and **Reviewed** badge; **Mark as reviewed** button gone

**Pass if:** Lecturer can open file and mark reviewed.

---

## Test 11 — Student sees reviewed status

**Role:** Student

**Steps:**

1. **Logout** → `test.student@lms.test` / `password`
2. DS501 → **Linear Regression Lab**
3. Confirm **Your submission** / **Your work** shows **Reviewed** (green)
4. Open full submission page
5. Confirm **Reviewed** badge; **no** **Mark as reviewed** button

**Pass if:** Student sees reviewed status end-to-end.

---

## Test 12 — Student cannot submit twice

**Role:** Student

**Steps:**

1. Stay logged in as `test.student@lms.test`
2. For **Linear Regression Lab**, try **Submit work** or open:  
   `http://127.0.0.1:8000/assignments/{id}/submit`  
   (use assignment ID for Linear Regression Lab, often `1` on fresh seed)
3. Confirm you are **not** shown a new upload form — existing submission or clear “already submitted” behavior

**Pass if:** No second submission allowed.

---

## Test 13 — Lecturer creates assignment

**Role:** Lecturer

**Steps:**

1. **Logout** → `lecturer@lms.test` / `password`
2. DS501 → **New assignment**
3. Fill:

   | Field | Value |
   |--------|--------|
   | Title | `Clustering Mini Project` |
   | Instructions | `Submit a short report on k-means clustering with one visualization.` |
   | Due date | Any future date/time |
   | Published | Checked |

4. Optional: attach a small PDF
5. Save / publish
6. Confirm success message and assignment on DS501 (**3 assignments** total)
7. Open new assignment; confirm instructions and **Published**

**Pass if:** Assignment created and visible to lecturer.

---

## Test 14 — Student sees new assignment

**Role:** Student

**Steps:**

1. **Logout** → `test.student@lms.test` / `password`
2. DS501 — confirm **3** assignments including **Clustering Mini Project**
3. Open **Clustering Mini Project**
4. Confirm instructions and **Submit your work**
5. Confirm **Linear Regression Lab** still **Reviewed** / view submission

**Pass if:** New assignment visible; old submission unchanged.

---

## Test 15 — Admin overview

**Role:** Admin

**Steps:**

1. **Logout** → `admin@lms.test` / `password`
2. Sidebar: **Users** and **Courses**
3. **Users** — confirm **Test Student Updated** and other accounts (expect **8+** after Test 3)
4. **Courses** — DS501 shows **3 assignments** (or open DS501 and count)
5. Open DS501 — loads without error; lecturer tabs visible for admin

**Pass if:** Admin can browse users and courses including new assignment.

---

## Optional extra checks (not in core 15)

| Area | Quick check |
|------|-------------|
| Search | Top bar search on Courses (if implemented) |
| Profile | Profile page loads for each role |
| Logout | Logout returns to login |
| Unauthorized | Student cannot open `/admin/users` |
| Media | Assignment resource **View in app** on Clustering Mini Project |

---

## Known limitations (local)

- Word/PPT in-browser preview may not work on `127.0.0.1` (download still works).
- Sidebar items marked **SOON** are placeholders, not bugs.
- Upload max on submit page reflects PHP limit (`composer serve` vs `php artisan serve`).

---

## Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| QA / Tester | | | |
| Product owner | | | |
