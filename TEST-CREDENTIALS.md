# Test logins (delete this file before production)

Password for every account: **password**

| Role | Email | Notes |
|------|-------|--------|
| Admin | admin@lms.test | Full access |
| Lecturer | lecturer@lms.test | Courses & assignments |
| Student | student1@lms.test | ID: DS2024001 |
| Student | student2@lms.test | ID: DS2024002 |
| Student | student3@lms.test | ID: DS2024003 |
| Student | student4@lms.test | ID: DS2024004 |
| Student | student5@lms.test | ID: DS2024005 |
| Student | student6@lms.test | ID: DS2024006 |
| Student | student7@lms.test | ID: DS2024007 |
| Student | student8@lms.test | ID: DS2024008 |
| Student | student9@lms.test | ID: DS2024009 |
| Student | student10@lms.test | ID: DS2024010 |

Seed database: `php artisan db:seed --class=LmsSeeder`

Hide on login page: set `LMS_SHOW_DEMO_CREDENTIALS=false` in `.env`
