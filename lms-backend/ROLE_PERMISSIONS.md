# Role & Permission Reference

Aster Academy Backend — `feature/standalone-setup` branch

---

## Roles

| Role | Constant | Description |
|---|---|---|
| `ADMIN` | `User::ROLE_ADMIN` | Full platform access — manages users, content, and settings |
| `INSTRUCTOR` | `User::ROLE_INSTRUCTOR` | Creates and delivers courses; views student outputs |
| `USER` | `User::ROLE_USER` | Enrolled learner; takes courses and assessments |

---

## Role Hierarchy

Access is **additive upward**: a higher role includes all permissions of lower roles.

```
PUBLIC (no token)
  └── USER        (valid Bearer token, role = USER)
        └── INSTRUCTOR  (valid Bearer token, role = INSTRUCTOR)
              └── ADMIN       (valid Bearer token, role = ADMIN)
```

---

## Authentication

All protected routes require a **Laravel Passport Bearer token**:

```
Authorization: Bearer <token>
```

Obtain a token via `POST /v1/login`. Tokens are revoked on `POST /v1/logout`.

**Rate limits**

| Scope | Limit |
|---|---|
| All API routes | 300 requests / 5 minutes (per user ID or IP) |
| `POST /v1/login` | 3 attempts / 1 minute (per IP) |

---

## How Role Enforcement Works

Role checking is applied in **controller constructors** via named middleware:

| Middleware alias | Class | Allowed roles |
|---|---|---|
| `auth.admin` | `AdminMiddleware` | `ADMIN` |
| `auth.instructor` | `InstructorMiddleware` | `ADMIN`, `INSTRUCTOR` |
| `auth.user` | `UserMiddleware` | `ADMIN`, `INSTRUCTOR`, `USER` |

Example from `UserManagementController`:

```php
public function __construct()
{
    $this->middleware('auth.admin');
}
```

An unauthorized request returns HTTP **403** with JSON body:

```json
{ "message": "Forbidden." }
```

---

## URL Prefix Structure

Routes are grouped by prefix, which matches the intended audience:

| Prefix | Route file | Intended role |
|---|---|---|
| `/v1` | `standalone_public.php` | PUBLIC / any authenticated user |
| `/v1/admin` | `standalone_admin.php` | ADMIN (enforced in controller) |
| `/v1/instructor` | `standalone_instructor.php` | ADMIN + INSTRUCTOR (enforced in controller) |
| `/v1/user` | `standalone_user.php` | ADMIN + INSTRUCTOR + USER (enforced in controller) |

> The route files themselves only require `auth:api` (a valid token). The finer-grained
> role check is applied by each controller's constructor middleware.

---

## Feature Permission Matrix

Legend: **A** = ADMIN only · **A+I** = ADMIN + INSTRUCTOR · **A+I+U** = all authenticated · **PUB** = public (no token)

### Authentication & Profile

| Feature | Access | Endpoints |
|---|---|---|
| Register | PUB | `POST /v1/register` |
| Login | PUB | `POST /v1/login` |
| Logout | A+I+U | `POST /v1/logout` |
| Email verification | PUB | `POST /v1/email/verification-notification`, `GET /v1/email/verify` |
| Forgot / reset password | PUB | `POST /v1/forgot-password`, `GET /v1/reset-password/{token}`, `POST /v1/reset-password` |
| Update password | A+I+U | `PUT /v1/update/password` |
| Update accepted terms | A+I+U | `PUT /v1/update/accepted-terms` |
| Update profile details | A+I+U | `PUT /v1/update/details` |
| Update profile photo | A+I+U | `POST /v1/update/profile/photo` |
| Update needs-info flag | A+I+U | `PUT /v1/update/info-update-flag` |
| Get current user | A+I+U | `GET /v1/user` |
| Login history | A+I+U | `GET /v1/login/history` |
| Activity log (own) | A+I+U | `GET /v1/activity-log` |

### Notifications

| Feature | Access | Endpoints |
|---|---|---|
| List notifications | A+I+U | `GET /v1/user/notifications` |
| Mark single notification read | A+I+U | `POST /v1/user/mark/notification` |
| Mark all notifications read | A+I+U | `GET /v1/user/mark/all/notifications` |

### Public Tenant / Course Catalog

| Feature | Access | Endpoints |
|---|---|---|
| Tenant info | PUB | `GET /v1/tenants/info` |
| Custom registration pages | PUB | `GET /v1/tenants/custom-registration/pages` |
| Class categories | PUB | `GET /v1/tenants/class-categories`, `GET /v1/tenants/random/class-categories` |
| Browse / grouped courses | PUB | `GET /v1/tenants/browse-courses`, `GET /v1/tenants/courses`, `POST /v1/tenants/courses/all`, `GET /v1/tenants/courses/free`, `GET /v1/tenants/courses/{course_uuid}` |
| Course availability dates | PUB | `GET /v1/tenants/course-availability-dates` |
| Training credits (catalog) | PUB | `GET /v1/tenants/training-credits` |
| Landing page backgrounds | PUB | `GET /v1/tenants/backgrounds` |
| Advisories | PUB | `GET /v1/tenants/advisories` |
| View certificate (public) | PUB | `GET /v1/tenants/view/certificate/{certificate_uuid}` |
| Payment callbacks | PUB | `GET /v1/tenants/payment-status/check`, `POST /v1/tenants/request/official-receipt`, `POST /v1/tenants/checkout/callback` |
| Contact mail | PUB | `POST /v1/tenants/send/contact/mail` |
| SCORM cloud callbacks | PUB | `POST /v1/tenants/course/upload/callback`, `POST /v1/tenants/course/registration/postback` |

---

### Admin-Only Features (`/v1/admin/*`)

All endpoints below require role **ADMIN**.

#### User Management

| Feature | Method | Path |
|---|---|---|
| List users | GET | `/v1/admin/user-management` |
| Get user | GET | `/v1/admin/user-management/{id}` |
| Create user | POST | `/v1/admin/user-management` |
| Update user | PUT | `/v1/admin/user-management/{id}` |
| Delete user | DELETE | `/v1/admin/user-management/{id}` |
| Import users (CSV) | POST | `/v1/admin/user-management/import/users` |
| Import logs | POST/DELETE | `/v1/admin/user-management/import/logs` |
| Update user status | PUT | `/v1/admin/user-management/update/status` |
| Update user role | PUT | `/v1/admin/user-management/update/role` |
| Prompt update (all/single) | PUT | `/v1/admin/user-management/prompt-update/all`, `/v1/admin/user-management/prompt-update/{id}` |
| Export users | POST | `/v1/admin/user-management/export` |

#### Enrollment Management

| Feature | Method | Path |
|---|---|---|
| Enrolled courses per user | GET | `/v1/admin/enrollments/user/{user_id}` |
| Enroll user in courses | POST | `/v1/admin/enrollments/user/{user_id}` |
| Enroll user in single course | POST | `/v1/admin/enrollments/user/{user_id}/enroll` |
| Delete enrollment | DELETE | `/v1/admin/enrollments/{enrollment_id}` |
| Import enrollees (CSV) | POST | `/v1/admin/enrollment/import/users` |
| List enrollable courses | GET | `/v1/admin/enrollment/courses` |
| Export course invoices | POST | `/v1/admin/enrollment/courses/export` |
| Course enrollees | GET | `/v1/admin/enrollment/course/{course_uuid}/enrollees` |
| Update invoice status | PUT | `/v1/admin/enrollment/invoice/status` |
| Update payment type | PUT | `/v1/admin/enrollment/invoice/type_of_payment` |
| Update participant type | PUT | `/v1/admin/enrollment/invoice/type_of_participant` |
| Export course invoice | POST | `/v1/admin/enrollment/course/{course_uuid}/invoice/export` |
| Upload proof of payment | POST | `/v1/admin/enrollment/upload/proof-of-payment` |
| Upload commitment letter | POST | `/v1/admin/enrollment/upload/commitment-letter` |
| Upload prime requirements | POST | `/v1/admin/enrollment/upload/prime-requirements` |
| Payment status check | POST | `/v1/admin/payment-status/check` |

#### Class Categories

| Feature | Method | Path |
|---|---|---|
| CRUD class categories | GET/POST/PUT/DELETE | `/v1/admin/class-categories` and `/v1/admin/class-categories/{id}` |

#### Dashboard

| Feature | Method | Path |
|---|---|---|
| Summary cards | GET | `/v1/admin/list/cards` |
| Pie graphs | GET | `/v1/admin/pie-graphs` |
| Courses list | GET | `/v1/admin/courses` |
| Course users | GET | `/v1/admin/course/users` |
| Course completers | GET | `/v1/admin/course/completers` |
| Total users per course | GET | `/v1/admin/total-users-per-course` |
| Custom registration fields | GET | `/v1/admin/custom-registration/field/lists`, `/v1/admin/custom-registration/field/lists-options` |
| Export dashboard data | POST | `/v1/admin/dashboard/courses/export`, `/v1/admin/dashboard/course/users/export`, `/v1/admin/dashboard/course/completers/export` |

#### Custom Registration

| Feature | Method | Path |
|---|---|---|
| List pages | GET | `/v1/admin/custom-registration/pages` |
| Create / update / delete page | POST/PUT/DELETE | `/v1/admin/custom-registration/pages`, `/v1/admin/custom-registration/pages/{id}` |
| Sort pages | POST | `/v1/admin/sort/custom-registration/pages` |
| Create / update / delete field | POST/PUT/DELETE | `/v1/admin/custom-registration/fields`, `/v1/admin/custom-registration/fields/{id}` |
| Sort fields | POST | `/v1/admin/sort/custom-registration/fields` |

#### Custom Module Settings

| Feature | Method | Path |
|---|---|---|
| List custom fields | GET | `/v1/admin/module-settings/fields` |
| Add / update / delete field | POST/DELETE | `/v1/admin/module-settings/fields`, `/v1/admin/module-settings/fields/{id}` |

#### Custom Tables

| Feature | Method | Path |
|---|---|---|
| List tables | GET | `/v1/admin/custom-tables` |
| Table columns | GET | `/v1/admin/custom-table/columns/{table}` |
| Create / drop table | POST | `/v1/admin/custom-table/create-table`, `/v1/admin/custom-table/drop-table` |
| Add / drop / update columns | POST | `/v1/admin/custom-table/add-columns`, `/v1/admin/custom-table/drop-column`, `/v1/admin/custom-table/update-column` |
| Change column position | POST | `/v1/admin/custom-table/change-column/position` |
| Sort table | POST | `/v1/admin/custom-table/sort-table` |

#### Exports

| Feature | Method | Path |
|---|---|---|
| List exports | GET | `/v1/admin/exports` |
| Delete export | DELETE | `/v1/admin/exports/{id}` |
| Download export file | POST | `/v1/admin/download/exports` |

#### Certificate Template Categories

| Feature | Method | Path |
|---|---|---|
| CRUD | GET/POST/PUT/DELETE | `/v1/admin/certificate-template-category` and `/{id}` |

#### Training Credits

| Feature | Method | Path |
|---|---|---|
| CRUD | GET/POST/PUT/DELETE | `/v1/admin/training-credits` and `/{id}` |

#### Tickets (Admin)

| Feature | Method | Path |
|---|---|---|
| List tickets | POST | `/v1/admin/tickets` |
| Ticket count | POST | `/v1/admin/ticket-count` |
| Get ticket messages | POST | `/v1/admin/ticket-messages` |
| Create ticket | POST | `/v1/admin/ticket` |
| Update ticket status | POST | `/v1/admin/ticket/status` |
| Export tickets | POST | `/v1/admin/ticket/export` |

#### Activity Log (Admin)

| Feature | Method | Path |
|---|---|---|
| List logs | GET | `/v1/admin/activity-log` |
| Log stats | GET | `/v1/admin/activity-log/stats` |
| Export logs | POST | `/v1/admin/activity-log/export` |

#### Advisories

| Feature | Method | Path |
|---|---|---|
| CRUD advisories | GET/POST/PUT/DELETE | `/v1/admin/advisories` and `/{id}` |

#### Landing Page

| Feature | Method | Path |
|---|---|---|
| List backgrounds | GET | `/v1/admin/landing-page/backgrounds` |
| Add / delete background | POST/DELETE | `/v1/admin/landing-page/background`, `/v1/admin/landing-page/background/{id}` |

#### Documents

| Feature | Method | Path |
|---|---|---|
| List / create / update | GET/POST/PUT | `/v1/admin/documents` and `/{id}` |

#### Forum — Admin

| Feature | Method | Path |
|---|---|---|
| Banned words CRUD | GET/POST/PUT/DELETE | `/v1/admin/forum/banned-words` |
| Save banned words | POST | `/v1/admin/forum/banned-words/save` |
| Forum posts CRUD | GET/POST/PUT/DELETE | `/v1/admin/forum/post` |
| Forum post comments | POST/PUT/DELETE | `/v1/admin/forum/post/comment` |

#### Announcements (Admin)

| Feature | Method | Path |
|---|---|---|
| CRUD announcements | GET/POST/PUT/DELETE | `/v1/admin/announcements` |
| Get single announcement | GET | `/v1/admin/announcement/{id}` |
| Announcement comments CRUD | GET/POST/PUT/DELETE | `/v1/admin/announcement/comments` |

#### Messaging (Admin)

| Feature | Method | Path |
|---|---|---|
| Contact list | GET | `/v1/admin/contact/list` |
| Private chat list | GET | `/v1/admin/private/chat/list` |
| Private chat messages | GET | `/v1/admin/private/chat/{uuid}` |
| Send private message | POST | `/v1/admin/private/chat/send` |
| Download attachment | POST | `/v1/admin/private/chat/attachment/download` |
| Create group chat | POST | `/v1/admin/group/chat` |
| Add users to group | POST | `/v1/admin/add/users/group/chat/{uuid}` |
| Update group photo | POST/DELETE | `/v1/admin/group/chat/photo/{uuid}`, `/v1/admin/group/chat/photo/{uuid}/remove` |
| Group chat list / detail | GET | `/v1/admin/group/chat/list`, `/v1/admin/group/chat/{uuid}` |
| Group contacts | GET | `/v1/admin/group/chat/{uuid}/contact/list` |
| Send group message | POST | `/v1/admin/group/chat/send` |
| Download group attachment | POST | `/v1/admin/group/chat/attachment/download` |

---

### Instructor Features (`/v1/instructor/*`)

All endpoints below require role **ADMIN or INSTRUCTOR**.

#### Course Management

| Feature | Method | Path |
|---|---|---|
| List courses (by status) | POST | `/v1/instructor/courses/{status}` |
| List all courses | GET | `/v1/instructor/courses/all` |
| Course CRUD | GET/POST/PUT/DELETE | `/v1/instructor/courses`, `/v1/instructor/courses/{id}` |
| Get course modules | GET | `/v1/instructor/course/{course_id}/modules` |
| Get course assessments | GET | `/v1/instructor/course/{course_id}/assessments` |
| Get course evaluations | GET | `/v1/instructor/course/{course_id}/evaluations` |
| Duplicate course | POST | `/v1/instructor/course/{course_id}/duplicate` |
| Upload / delete course image | PUT/DELETE | `/v1/instructor/course/{course_id}/upload-image`, `/v1/instructor/course/{course_id}/delete-image` |
| Set certificate template | PUT | `/v1/instructor/course/{course_id}/certificate-template` |
| Tag classes | PUT | `/v1/instructor/course/{course_id}/class-tags` |
| Update course status | PUT | `/v1/instructor/course/{course_id}/status` |
| Update course timestamp | PUT | `/v1/instructor/course/{course_id}/updated-at` |
| Share course | PUT | `/v1/instructor/share/course` |
| Share course users / permissions | GET/PUT | `/v1/instructor/share/course/users`, `/v1/instructor/share/course/permission`, `/v1/instructor/share/course/users/remove` |
| Restore / permanently delete course | POST | `/v1/instructor/restore/course`, `/v1/instructor/course/{course_id}/permanent-delete` |
| Deleted courses | GET | `/v1/instructor/deleted/courses` |
| Training credits (course) | GET | `/v1/instructor/course/training-credits` |
| Course group chats | POST/GET/DELETE | `/v1/instructor/course/{course_id}/group-chat`, `/v1/instructor/course/{course_id}/group-chats`, `/v1/instructor/course/{course_id}/group-chat/{group_chat_id}` |
| Certificate enrollees | GET/POST | `/v1/instructor/course/{course_id}/certificates/enrollees`, `/v1/instructor/course/{course_id}/certificates/enrollees/assign` |
| Export course | POST | `/v1/instructor/course/{course_id}/export` |

#### Modules, Topics, Sub-Topics

| Feature | Method | Path |
|---|---|---|
| Module CRUD | GET/POST/PUT/DELETE | `/v1/instructor/modules`, `/v1/instructor/modules/{id}` |
| Sort modules | PUT | `/v1/instructor/module/sort` |
| Module validity | PUT | `/v1/instructor/module/{module_id}/validity` |
| Module prerequisites | POST | `/v1/instructor/module/sync/prerequisites` |
| Module evaluations | GET | `/v1/instructor/module/{module_id}/evaluations` |
| Custom module fields | GET | `/v1/instructor/module/fields` |
| Sort module content | PUT | `/v1/instructor/module-content/sort` |
| Topic CRUD | GET/POST/PUT/DELETE | `/v1/instructor/topics`, `/v1/instructor/topics/{id}` |
| Sort topics | PUT | `/v1/instructor/topic/sort` |
| Sub-topic CRUD | GET/POST/PUT/DELETE | `/v1/instructor/sub-topics`, `/v1/instructor/sub-topics/{id}` |
| Sort sub-topics | PUT | `/v1/instructor/sub-topic/sort` |

#### Assessments

| Feature | Method | Path |
|---|---|---|
| Assessment CRUD | GET/POST/PUT/DELETE | `/v1/instructor/assessments`, `/v1/instructor/assessments/{id}` |
| Get assessment | GET | `/v1/instructor/assessment/{assessment_id}` |
| Assessment questions | GET/PUT | `/v1/instructor/assessment/{assessment_id}/questions`, `/v1/instructor/assessment/{assessment_id}/sort` |
| Question CRUD | GET/POST/PUT/DELETE | `/v1/instructor/assessment/questions`, `/v1/instructor/assessment/questions/{id}` |
| User access tags | GET/PUT | `/v1/instructor/assessment/{assessment_id}/user-access-tags` |
| Question answers | GET | `/v1/instructor/assessment/{assessment_id}/{question_id}/answers` |
| Class enrollee check | GET | `/v1/instructor/course/{course_id}/assessment/{assessment_id}/class-enrollee` |
| Share / duplicate / move | POST/PUT | `/v1/instructor/assessment/{assessment_id}/share`, `/v1/instructor/assessment/{assessment_id}/duplicate`, `/v1/instructor/assessment/{assessment_id}/move-to-course` |
| Shared assessments | GET/POST | `/v1/instructor/assessment/shared-with-me`, `/v1/instructor/assessment/shared-to-others` |
| Export assessment responses | POST | `/v1/instructor/assessment/{assessment_id}/list/export`, `/v1/instructor/assessment/{assessment_id}/responses/export` |
| Instructor list for sharing | GET | `/v1/instructor/assessment/instructor/list` |

#### Group Activities

| Feature | Method | Path |
|---|---|---|
| Group activity CRUD | GET/POST/PUT/DELETE | `/v1/instructor/group-activity`, `/v1/instructor/group-activity/{id}` |
| Manage groups | GET/POST/DELETE | `/v1/instructor/group-activity/{id}/groups`, `/v1/instructor/group-activity/{id}/groups/{group_id}` |
| Group members | GET/POST/DELETE | `/v1/instructor/group-activity/{id}/groups/{group_id}/members`, `.../members/add`, `.../members/remove` |
| Non-members list | GET | `/v1/instructor/group-activity/{id}/groups/non-members` |
| Generate group file | POST | `/v1/instructor/group-activity/{id}/groups/{group_id}/generate` |

#### Rubrics

| Feature | Method | Path |
|---|---|---|
| Rubric CRUD | GET/POST/PUT/DELETE | `/v1/instructor/rubrics`, `/v1/instructor/rubrics/{id}` |
| Criteria management | GET/POST/DELETE | `/v1/instructor/rubric/add-criteria/{id}`, `.../update-criteria/{id}`, `.../delete-criteria/{id}` |
| Rating management | GET/POST/DELETE | `/v1/instructor/rubric/add-rating/{id}`, `.../update-rating/{id}`, `.../delete-rating/{id}` |
| Update indicator | POST | `/v1/instructor/rubric/update-indicator/{id}` |
| Share / shared rubrics | POST/GET | `/v1/instructor/rubric/{id}/share`, `/v1/instructor/rubric/shared-with-me`, `/v1/instructor/rubric/shared-to-others`, `/v1/instructor/rubric/share-to-instructors` |

#### Grading

| Feature | Method | Path |
|---|---|---|
| Grading list / detail | GET | `/v1/instructor/grading`, `/v1/instructor/grading/{id}` |
| Assessment grading | GET | `/v1/instructor/grading/assessment/{assessment_id}`, `.../students` |
| Group activity grading | GET/PUT | `/v1/instructor/grading/group-activity/{id}/groups`, `.../group/{group_id}`, `.../group/{group_id}/checked` |
| Auto-check assessment | POST | `/v1/instructor/grading/check-assessment-answers` |
| Update score | PUT | `/v1/instructor/grading/update/score` |
| Update checked status | PUT | `/v1/instructor/grading/course-evaluation/update/checked`, `/v1/instructor/grading/module-evaluation/update/checked`, `/v1/instructor/grading/assessment/update/checked` |

#### Student Output

| Feature | Method | Path |
|---|---|---|
| Student records | GET | `/v1/instructor/student-output` |
| Student info | GET | `/v1/instructor/student-output/user/{user_uuid}` |
| Courses / enrollees | GET | `/v1/instructor/student-output/courses`, `/v1/instructor/student-output/course/{course_uuid}/enrollees` |
| Export enrollees | POST | `/v1/instructor/student-output/course/{course_uuid}/enrollees/export` |
| Enrollee progress | GET | `/v1/instructor/student-output/course/{course_uuid}/enrollees/{user_id}/progress` and sub-paths |
| Course progress | GET | `/v1/instructor/student-output/course-progress/{user_uuid}`, `.../details` |
| Overall grading | GET | `/v1/instructor/student-output/overall-grading/{user_uuid}` |
| Mark topic / subtopic complete | POST | `/v1/instructor/student-output/courses/enrollees/{user_id}/topic/{topic_id}/mark-as-completed`, `.../sub-topic/...` |
| Assessments / modules | GET | `/v1/instructor/student-output/assessments`, `/v1/instructor/student-output/modules/{class_category_id}/{course_uuid}` |
| Class categories | GET | `/v1/instructor/student-output/class-categories` |

#### Zoom Meetings (Instructor)

| Feature | Method | Path |
|---|---|---|
| Zoom meeting CRUD | GET/POST/PUT/DELETE | `/v1/instructor/zoom-meetings`, `/v1/instructor/zoom-meetings/{id}` |
| Request access token | POST | `/v1/instructor/zoom-meetings/request/access_token` |
| Participants | GET | `/v1/instructor/{course_id}/zoom-meetings/participants` |
| Update attendance | POST | `/v1/instructor/{course_id}/zoom-meetings/participants/attendance/update` |
| Export participants | POST | `/v1/instructor/{course_id}/zoom-meetings/participants/export` |
| Get / update participant | GET/POST | `/v1/instructor/zoom-meetings/{meeting_id}/participant/{user_id}` |

#### Enrollment (Instructor)

| Feature | Method | Path |
|---|---|---|
| Get user for enrollment | GET | `/v1/instructor/manual/enrollment/user/{user_id}` |
| Manual enroll | POST | `/v1/instructor/manual/enrollment` |
| Course enrollees | GET | `/v1/instructor/enrollment/course/{course_uuid}/enrollees` |
| Upload payment docs | POST | `/v1/instructor/manual/enrollment/proof-of-payment`, `.../commitment-letter`, `.../prime-requirements` |
| Export invoices | POST | `/v1/instructor/enrollment/courses/export`, `/v1/instructor/enrollment/course/{course_uuid}/invoice/export` |

#### Course & Module Evaluations (Instructor)

| Feature | Method | Path |
|---|---|---|
| Course evaluation CRUD | GET/POST/PUT/DELETE | `/v1/instructor/course/evaluations`, `/v1/instructor/course/evaluations/{id}` |
| Evaluation respondents / answers | GET | `/v1/instructor/course/evaluation/{id}/respondents`, `.../answers` |
| Update evaluation status | PUT | `/v1/instructor/course/evaluation/attempt/{id}/check` |
| Update validity / sort | PUT | `/v1/instructor/course/evaluation/{id}/validity`, `.../sort` |
| Question CRUD | GET/POST/PUT/DELETE | `/v1/instructor/course/evaluation/questions`, `.../questions/{id}` |
| Question images | GET/POST/DELETE | `/v1/instructor/course/evaluation/question/{id}/image` |
| Module evaluation CRUD | GET/POST/PUT/DELETE | `/v1/instructor/module/evaluations`, `/v1/instructor/module/evaluations/{id}` |
| Module evaluation respondents / answers | GET | `/v1/instructor/module/evaluation/{id}/respondents`, `.../answers` |
| Module question CRUD / images | GET/POST/PUT/DELETE | `/v1/instructor/module/evaluation/questions`, `.../question/{id}/image` |

#### Certificates (Instructor)

| Feature | Method | Path |
|---|---|---|
| Generate certificate | POST | `/v1/instructor/generate/certificate` |
| Certificate template CRUD | GET/POST/PUT/DELETE | `/v1/instructor/certificate-template`, `/v1/instructor/certificate-template/{id}` |
| Duplicate template | POST | `/v1/instructor/certificate-template/{id}/duplicate` |
| Template categories | GET | `/v1/instructor/certificate-template/category/list` |

#### Reports

| Feature | Method | Path |
|---|---|---|
| Summary cards | GET | `/v1/instructor/report/list/cards` |
| Enrollee counts | GET | `/v1/instructor/report/list/enrollees` |
| Courses / completers / users | GET | `/v1/instructor/report/list/courses`, `.../completers`, `.../users` |
| Exports | GET/POST | `/v1/instructor/report/list/courses/export`, `.../completers/export`, `.../users/export` |
| Assessment reports | GET/POST | `/v1/instructor/report/list/assessments`, `/v1/instructor/report/list/{course_id}/assessments`, `.../export` |
| Users per course | GET | `/v1/instructor/report/total-users-per-course` |
| Custom registration fields | GET | `/v1/instructor/report/custom-registration/field/lists` |

#### SCORM (Instructor)

| Feature | Method | Path |
|---|---|---|
| Upload progress | GET | `/v1/instructor/scorm/course/upload/progress` |
| Preview link | POST | `/v1/instructor/scorm/course/build/preview-link` |
| Course summary | GET | `/v1/instructor/scorm/course/summary` |
| Learner details / progress | GET | `/v1/instructor/scorm/course/learners`, `.../details`, `.../learner/progress`, `.../learner/interactions` |
| Learner submissions | GET | `/v1/instructor/scorm/course/learners/submissions` |
| Export learners | POST | `/v1/instructor/scorm/course/learners/export` |
| Registration CRUD | GET/POST/PUT/DELETE | `/v1/instructor/scorm/registration`, `/v1/instructor/scorm/registration/{id}` |
| Registration summary | GET | `/v1/instructor/scorm/registration/summary` |
| SCORM activity list / detail | GET/POST | `/v1/instructor/scorm-activity`, `/{course_id}/scorm-activity/{id}`, `.../questions`, `.../answers`, `.../respondents`, `.../learner/{user_id}` |
| Export SCORM data | POST | `/{course_id}/scorm-activity/{id}/respondents/list/export`, `.../answers/export` |

#### Forum (Instructor)

| Feature | Method | Path |
|---|---|---|
| Post CRUD | GET/POST/PUT/DELETE | `/v1/instructor/forum/post`, `/v1/instructor/forum/post/{id}` |
| Comment CRUD | GET/POST/PUT/DELETE | `/v1/instructor/forum/post/comment`, `/v1/instructor/forum/post/comment/{id}` |

#### Question Pools

| Feature | Method | Path |
|---|---|---|
| Pool CRUD | GET/POST/PUT/DELETE | `/v1/instructor/question-pools`, `/v1/instructor/question-pools/{id}` |
| Assign pool to evaluation/assessment | POST | `/v1/instructor/question-pool/assign/course-evaluation`, `.../module-evaluation`, `.../assessment` |
| Pool question CRUD | GET/POST/PUT/DELETE | `/v1/instructor/question-pool/question`, `/v1/instructor/question-pool/question/{id}` |
| Pool question images | GET/POST/DELETE | `/v1/instructor/question-pool/question/{id}/image` |

#### Course Groups

| Feature | Method | Path |
|---|---|---|
| Course group CRUD | GET/POST/PUT/DELETE | `/v1/instructor/course-groups`, `/v1/instructor/course-groups/{id}` |
| Members | GET/POST/DELETE | `/v1/instructor/course-groups/{id}/members`, `.../members/add`, `.../members/remove` |
| User list | GET | `/v1/instructor/course-groups/{id}/user-list` |
| Group chat | POST | `/v1/instructor/course-groups/{id}/group-chat` |

#### Exports (Instructor)

| Feature | Method | Path |
|---|---|---|
| Export CRUD | GET/POST/PUT/DELETE | `/v1/instructor/exports`, `/v1/instructor/exports/{id}` |
| Download export | POST | `/v1/instructor/download/exports` |
| Evaluation exports | POST | `/v1/instructor/course/evaluation/list/export`, `.../summary/export`, `/v1/instructor/module/evaluation/list/export`, `.../summary/export` |

#### Messaging (Instructor) — same as Admin

| Feature | Method | Path |
|---|---|---|
| Contact list | GET | `/v1/instructor/contact/list` |
| Private chat (list, view, send) | GET/POST | `/v1/instructor/private/chat/list`, `./{uuid}`, `.../send`, `.../attachment/download` |
| Group chat (full) | GET/POST/DELETE | `/v1/instructor/group/chat/*` |

#### Activity Log (Instructor)

| Feature | Method | Path |
|---|---|---|
| List logs | GET | `/v1/instructor/activity-log` |
| Log stats | GET | `/v1/instructor/activity-log/stats` |
| Export logs | POST | `/v1/instructor/activity-log/export` |

#### Announcements (Instructor)

| Feature | Method | Path |
|---|---|---|
| CRUD announcements | GET/POST/PUT/DELETE | `/v1/instructor/announcements` |
| Get single announcement | GET | `/v1/instructor/announcement/{id}` |
| Announcement comments CRUD | GET/POST/PUT/DELETE | `/v1/instructor/announcement/comments` |

---

### User (Learner) Features (`/v1/user/*`)

All endpoints below require role **ADMIN, INSTRUCTOR, or USER**.

#### Courses (Learner)

| Feature | Method | Path |
|---|---|---|
| My courses | POST | `/v1/user/courses` |
| View course | GET | `/v1/user/courses/{course_uuid}` |
| Update course card color | POST | `/v1/user/course-card/color` |
| Favorite / unfavorite | POST | `/v1/user/course/favorite` |
| Download learning material | POST | `/v1/user/download/course/learning-material` |
| Completed courses | POST | `/v1/user/complete-courses` |
| Course announcements | GET | `/v1/user/course/{id}/announcements` |
| Course zoom meetings | GET | `/v1/user/course/{id}/zoom-meetings` |
| My course evaluations | GET | `/v1/user/course/{uuid}/course-evaluations` |
| SCORM progress check | POST | `/v1/user/course/scorm/progress/check` |
| Course export | POST | `/v1/user/course/{course_id}/export` |
| Course categories | GET | `/v1/user/course/class-categories` |
| Set log | POST | `/v1/user/course/setlog` |

#### Modules & Progress (Learner)

| Feature | Method | Path |
|---|---|---|
| Get modules | GET | `/v1/user/course/{course_id}/modules` |
| Get module | GET | `/v1/user/course/module/{module_id}` |
| Navigation controls | POST | `/v1/user/course/progress/controls` |
| Save done content | POST | `/v1/user/course/save/content` |
| Mark course complete | POST | `/v1/user/course/mark-as-complete` |
| Save time spent | POST | `/v1/user/course/time-spent` |
| Student course progress | GET | `/v1/user/course/{course_uuid}/progress` |

#### Assessments (Learner)

| Feature | Method | Path |
|---|---|---|
| View assessment | GET | `/v1/user/assessments/{assessment_uuid}` |
| Get questions | GET | `/v1/user/assessments/{assessment_uuid}/questions` |
| Submit answers | POST | `/v1/user/assessments/answers` |

#### Evaluations (Learner)

| Feature | Method | Path |
|---|---|---|
| Get course evaluation | GET | `/v1/user/course/evaluation/{course_evaluation_id}` |
| List / view course evaluations | GET | `/v1/user/course/evaluations/{id}` |
| Submit course evaluation | POST | `/v1/user/course/evaluations` |
| Get module evaluation | GET | `/v1/user/module/evaluation/{module_evaluation_id}` |
| List / view module evaluations | GET | `/v1/user/module/evaluations/{id}` |
| Submit module evaluation | POST | `/v1/user/module/evaluations` |

#### Cart & Checkout

| Feature | Method | Path |
|---|---|---|
| Cart items | GET | `/v1/user/add-to-cart/items` |
| Add / remove item | POST | `/v1/user/add-to-cart`, `/v1/user/remove-from-cart` |
| Empty cart | POST | `/v1/user/empty-cart` |
| Checkout (various payment methods) | POST | `/v1/user/checkout`, `/v1/user/checkout/online-portal`, `/v1/user/checkout/nomination`, `/v1/user/checkout/own-account`, `/v1/user/checkout/offline` |

#### Invoices & Transactions

| Feature | Method | Path |
|---|---|---|
| Transaction history | POST | `/v1/user/transaction-history/invoices` |
| Upload nomination form | PUT | `/v1/user/upload/nomination-form/invoice/{invoice_id}` |
| Upload proof of payment | POST | `/v1/user/upload/proof-of-payment/invoice` |
| Upload commitment letter | POST | `/v1/user/upload/commitment-letter/invoice` |
| Upload prime requirements | POST | `/v1/user/upload/prime-requirements/invoice` |
| Cancel transaction | POST | `/v1/user/cancel/transaction/invoice/{invoice_id}` |
| Update delivery receipt | POST | `/v1/user/update/delivery-receipt/invoice/{invoice_id}` |

#### Certificates (Learner)

| Feature | Method | Path |
|---|---|---|
| My certificates | POST | `/v1/user/certificates` |
| Download certificate | POST | `/v1/user/download/certificate` |
| Generate certificate | POST | `/v1/user/generate/certificate` |

#### SCORM (Learner)

| Feature | Method | Path |
|---|---|---|
| Submit SCORM completion | POST | `/v1/user/scorm/submission` |
| Set SCORM score | POST | `/v1/user/scorm/score/set` |
| Get / save SCORM progress | GET/POST | `/v1/user/scorm/progress` |

#### Forum (Learner)

| Feature | Method | Path |
|---|---|---|
| List / view / create / edit / delete posts | GET/POST/PUT/DELETE | `/v1/user/forum/post` |
| Own posts | GET | `/v1/user/forum/post/own` |
| Create / edit / delete comments | POST/PUT/DELETE | `/v1/user/forum/post/comment` |

#### Messaging (Learner)

| Feature | Method | Path |
|---|---|---|
| Contact list | GET | `/v1/user/contact/list` |
| Private chat (list, view, send) | GET/POST | `/v1/user/private/chat/list`, `./{uuid}`, `.../send`, `.../attachment/download` |
| Group chat (list, view, send) | GET/POST | `/v1/user/group/chat/*` |

#### Support Tickets (Learner)

| Feature | Method | Path |
|---|---|---|
| Ticket CRUD | GET/POST/PUT/DELETE | `/v1/user/tickets`, `/v1/user/tickets/{id}` |
| Ticket messages | POST | `/v1/user/ticket-messages` |
| Submit ticket message | POST | `/v1/user/ticket` |

#### Calendar

| Feature | Method | Path |
|---|---|---|
| Calendar events | POST | `/v1/user/calendar/events` |

#### Zoom Meetings (Learner)

| Feature | Method | Path |
|---|---|---|
| Get meeting info | GET | `/v1/user/zoom-meetings/{id}` |

#### Announcements (Learner)

| Feature | Method | Path |
|---|---|---|
| View announcement | GET | `/v1/user/announcements/{id}` |
| Announcement comments CRUD | GET/POST/PUT/DELETE | `/v1/user/announcement/comments` |

---

## Cross-Role Summary Table

| Feature Area | PUBLIC | USER | INSTRUCTOR | ADMIN |
|---|:---:|:---:|:---:|:---:|
| Login / Register / Password reset | R | R | R | R |
| Profile management | — | R | R | R |
| Notifications | — | R | R | R |
| Course catalog (browse) | R | R | R | R |
| My courses (enrolled) | — | R | R | R |
| Course progress / content | — | R | R | R |
| Assessments (take) | — | R | R | R |
| Evaluations (submit) | — | R | R | R |
| Cart & checkout | — | R | R | R |
| Invoices & transactions | — | R | R | R |
| Certificates (download/generate) | — | R | R | R |
| SCORM (learner side) | — | R | R | R |
| Forum (post/comment) | — | R | R | R |
| Messaging (private/group chat) | — | R | R | R |
| Support tickets | — | R | R | R |
| Calendar | — | R | R | R |
| Announcements (read/comment) | — | R | R | R |
| Course creation & management | — | — | R | R |
| Module / topic / sub-topic CRUD | — | — | R | R |
| Assessment creation & grading | — | — | R | R |
| Rubrics & group activities | — | — | R | R |
| Student output & reports | — | — | R | R |
| Zoom meeting management | — | — | R | R |
| Enrollment management (instructor) | — | — | R | R |
| SCORM (admin side / reports) | — | — | R | R |
| Certificate templates | — | — | R | R |
| Question pools | — | — | R | R |
| Course groups | — | — | R | R |
| Activity log (instructor view) | — | — | R | R |
| Announcement management | — | — | R | R |
| User management | — | — | — | R |
| Role & status changes | — | — | — | R |
| Dashboard analytics | — | — | — | R |
| Custom registration fields | — | — | — | R |
| Custom module settings | — | — | — | R |
| Custom tables | — | — | — | R |
| Class category management | — | — | — | R |
| Advisories management | — | — | — | R |
| Landing page management | — | — | — | R |
| Document management | — | — | — | R |
| Banned words management | — | — | — | R |
| Training credits management | — | — | — | R |
| Certificate template categories | — | — | — | R |
| Export management (admin) | — | — | — | R |
| Ticket management (admin) | — | — | — | R |
| Activity log (admin full view) | — | — | — | R |

R = can access · — = denied (403)
