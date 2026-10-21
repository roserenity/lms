# Database Schema

All LMS data lives in a single database. Most tables use **soft deletes** (`deleted_at`) and expose a **UUID** column alongside the auto-increment `id` for public-facing references.

---

### Users & Auth

#### users
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | indexed |
| enrollee_id | string | |
| first_name | string | |
| middle_name | string | nullable |
| last_name | string | |
| suffix | string | nullable |
| gender | string | nullable — `M` / `F` |
| email | string | unique |
| email_verified_at | timestamp | nullable |
| password | string | |
| role | string | `USER`, `INSTRUCTOR`, `ADMIN` |
| status | string | default: `active` |
| is_first_login | boolean | default: 1 |
| has_accepted_terms | boolean | default: 0 |
| needs_info_update | boolean | default: 0 |
| data | json | nullable |
| deleted_at | timestamp | soft delete |

#### oauth_access_tokens · oauth_refresh_tokens · oauth_clients · oauth_auth_codes · oauth_personal_access_clients
Standard Laravel Passport OAuth tables — omitted for brevity.

---

### Course Structure

#### courses
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | indexed |
| course_code | string | nullable |
| title | string | |
| description | longText | nullable |
| status | string | `UNPUBLISH`, `PUBLISH`, `DRAFT` |
| price | unsignedInt | |
| is_free | boolean | default: 0 |
| available_until | json | nullable |
| training_hours | unsignedInt | |
| facilitator | string | nullable |
| training_credits | unsignedBigInt | default: 1 |
| type_of_learning | enum | `E-LEARNING`, `BLENDED`, `MICROLEARNING` |
| credit_training_hours | unsignedInt | |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### modules
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| title | string | nullable |
| description | longText | nullable |
| version | string | nullable |
| contact_person | string | nullable |
| valid_until | string | default: `anytime` |
| language | json | nullable |
| data | json | nullable |
| sort_no | int | default: 0 |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### module_contents
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| module_id | unsignedBigInt | FK → modules.id (cascade) |
| content_type | string | |
| data | json | nullable |
| sort_no | int | default: 0 |
| deleted_at | timestamp | soft delete |

#### module_prerequisites
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| module_id | unsignedBigInt | FK → modules.id (cascade) |
| prerequisite_module_id | unsignedBigInt | |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### topics
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | indexed |
| title | string | |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| module_id | unsignedBigInt | FK → modules.id (cascade) |
| sort_no | int | default: 0 |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### sub_topics
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | indexed |
| title | string | |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| topic_id | unsignedBigInt | FK → topics.id (cascade) |
| sort_no | int | default: 0 |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### learning_materials
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| url | longText | |
| file_type | string | |
| learnable_id | unsignedBigInt | Polymorphic (topic / sub_topic) |
| learnable_type | string | Polymorphic |
| is_downloadable | boolean | default: 0 |

#### class_categories
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| class_code | string | unique |
| class_name | string | |
| deleted_at | timestamp | soft delete |

#### course_has_classes *(pivot)*
| course_id | class_category_id | Composite PK |

#### custom_module_fields
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| module_id | unsignedBigInt | FK → modules.id (cascade) |
| field_label | string | |
| field_type | string | |
| data | json | nullable |
| deleted_at | timestamp | soft delete |

---

### Enrollments & Progress

#### enrollments
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| deleted_at | timestamp | soft delete |

#### student_complete_courses
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| deleted_at | timestamp | soft delete |

#### student_module_progress
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| model_id | unsignedBigInt | Polymorphic (topic / sub_topic / etc.) |
| model_type | string | Polymorphic |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| time_spent | unsignedBigInt | in seconds; default: 0 |
| status | string | `IN-PROGRESS`, `DONE` |

#### student_course_progress
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| total_done_topics | unsignedBigInt | |
| total_done_sub_topics | unsignedBigInt | |
| total_done_assessments | unsignedBigInt | |
| total_done_course_evaluations | unsignedBigInt | |
| total_done_module_evaluations | unsignedBigInt | |
| total_done_group_activities | unsignedBigInt | |
| total_attended_zoom_meetings | unsignedBigInt | |
| total_time_spent | unsignedBigInt | |
| progress | unsignedBigInt | in percent |
| deleted_at | timestamp | soft delete |

#### share_course_has_users *(pivot)*
| course_id | user_id | permission (`viewer` / `editor`) | Composite PK |

#### course_favorite_has_users *(pivot)*
| course_id | user_id | Composite PK |

#### cart_items *(pivot)*
| user_id | course_id | Composite PK |

#### course_card_colors
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| color | string | |

---

### Assessments

#### assessments
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| module_id | unsignedBigInt | nullable |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| title | string | nullable |
| type_of_assessment | string | `graded_assessment`, `practice_assessment`, `graded_survey`, `ungraded_survey` |
| description | longText | nullable |
| require_checking | boolean | default: 0 |
| shuffle_questions | boolean | nullable |
| shuffle_answers | boolean | nullable |
| time_limit | unsignedInt | nullable (minutes) |
| max_attempt | unsignedInt | nullable |
| accepted_score | string | `highest` or `latest`; default: `latest` |
| passing_score | unsignedBigInt | nullable |
| view_results_by | string | nullable — `summary`, `each_attempt`, `all_correct_answers` |
| show_each_questions | boolean | nullable |
| lock_question_after_answering | boolean | nullable |
| due_date | json | nullable |
| total_points | decimal(5,2) | default: 0 |
| deleted_at | timestamp | soft delete |

#### assessment_questions
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| assessment_id | unsignedBigInt | FK → assessments.id (cascade) |
| question | longText | nullable |
| type_of_question | string | default: `multiple_choice` |
| score | decimal(5,2) | default: 1 |
| require_file_attachment | boolean | default: 0 |
| require_support_answer | boolean | nullable |
| rubric_id | unsignedInt | nullable |
| sort_no | unsignedInt | default: 0 |
| deleted_at | timestamp | soft delete |

#### assessment_answers
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| assessment_question_id | unsignedBigInt | FK → assessment_questions.id (cascade) |
| correct_answer | string | nullable |
| post_type | string | nullable |
| choices | json | nullable |
| deleted_at | timestamp | soft delete |

#### assessment_scores
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| user_id | unsignedBigInt | |
| assessment_id | unsignedBigInt | |
| score | decimal(5,2) | nullable |
| post_type | string | nullable — `submitted`, `ended`, `left` |
| attempt | int | nullable |
| question_answer_ids | string | nullable |
| checked | boolean | default: 0 |
| active | boolean | nullable |
| status | string | nullable — `PASSED`, `FAILED` |
| deleted_at | timestamp | soft delete |

#### question_answers
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| user_id | unsignedBigInt | |
| assessment_id | unsignedBigInt | |
| assessment_question_id | unsignedBigInt | |
| answer | longText | nullable |
| support_answer | string | nullable |
| file | longText | nullable |
| score | decimal(5,2) | |

#### question_answer_feedback
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| question_answer_id | unsignedBigInt | FK → question_answers.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| comment | longText | nullable |
| deleted_at | timestamp | soft delete |

#### share_assessments
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| assessment_id | unsignedBigInt | FK → assessments.id (cascade) |
| shared_by_user_id | unsignedBigInt | FK → users.id (cascade) |
| shared_to_user_id | unsignedBigInt | FK → users.id (cascade) |
| status | string | `pending` or `accepted` |

#### assessment_has_users · assessment_has_topics · assessment_has_sub_topics *(pivots)*
Standard many-to-many junction tables linking assessments to users, topics, and sub-topics.

---

### Question Pools

#### question_pools
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| title | string | |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### question_pool_questions
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| question_pool_id | unsignedBigInt | FK → question_pools.id (cascade) |
| question | longText | |
| type_of_question | string | |
| score | decimal(5,2) | default: 1 |
| deleted_at | timestamp | soft delete |

#### question_pool_question_answers
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| question_pool_question_id | unsignedBigInt | FK → question_pool_questions.id (cascade) |
| correct_answer | string | nullable |
| choices | json | nullable |
| deleted_at | timestamp | soft delete |

---

### Rubrics

#### rubrics
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| title | string | |
| type_of_rubric | string | `custom`, `essay`, `output_based` |
| description | string | |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |

#### rubric_criterias
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| rubric_id | unsignedBigInt | FK → rubrics.id (cascade) |
| title | string | |
| score | bigInt | default: 0 |

#### rubric_ratings
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| rubric_id | unsignedBigInt | FK → rubrics.id (cascade) |
| rate | string | nullable |
| description | string | nullable |

#### criteria_indicators
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| rubric_criteria_id | unsignedBigInt | FK → rubric_criterias.id (cascade) |
| rubric_rating_id | unsignedBigInt | FK → rubric_ratings.id (cascade) |
| body | longText | nullable |

#### share_rubrics
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| rubric_id | unsignedBigInt | FK → rubrics.id (cascade) |
| shared_by_user_id | unsignedBigInt | FK → users.id (cascade) |
| shared_to_user_id | unsignedBigInt | FK → users.id (cascade) |
| status | string | `pending` or `accepted` |

---

### Evaluations

#### course_evaluations
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | indexed |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| title | string | |
| description | longText | nullable |
| is_shuffle_questions | boolean | default: 0 |
| is_send_feedback_to_user | boolean | default: 0 |
| submission_limit | unsignedInt | nullable |
| is_allow_to_review | boolean | default: 0 |
| valid_until | string | default: `anytime` |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### course_evaluation_questions
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| course_evaluation_id | unsignedBigInt | FK → course_evaluations.id (cascade) |
| question | longText | nullable |
| type_of_question | string | default: `multiple_choice` |
| is_require_to_answer | boolean | default: 0 |
| sort_no | unsignedInt | |
| data | json | nullable |
| deleted_at | timestamp | soft delete |

#### student_course_evaluations
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| user_id | unsignedBigInt | |
| course_evaluation_id | unsignedBigInt | |
| is_checked | boolean | default: 0 |
| deleted_at | timestamp | soft delete |

#### student_course_evaluation_answers
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| user_id | unsignedBigInt | |
| student_course_evaluation_id | unsignedBigInt | |
| course_evaluation_question_id | unsignedBigInt | |
| data | json | nullable |
| deleted_at | timestamp | soft delete |

Module evaluations (`module_evaluations`, `module_evaluation_questions`, `student_module_evaluations`, `student_module_evaluation_answers`) follow the same structure as course evaluations, scoped to a module instead of a course.

---

### Certificates

#### certificate_template_categories
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| name | string | |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### certificate_templates
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| name | string | |
| certificate_category_id | unsignedBigInt | nullable — FK → certificate_template_categories.id (set null) |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| is_default | boolean | default: 0 |
| content | json | nullable |
| deleted_at | timestamp | soft delete |

#### certificate_logos · certificate_attestors · certificate_signatories
Design elements attached to a certificate template (logos, signing authorities). All FK → certificate_templates.id (cascade).

#### course_certificate_templates *(pivot)*
| course_id | certificate_template_id | FK cascade on both sides |

#### certificates
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | indexed |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| certificate_category_id | unsignedBigInt | FK → certificate_template_categories.id (cascade) |
| deleted_at | timestamp | soft delete |

#### student_certificate_assignments
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| certificate_id | unsignedBigInt | FK → certificates.id (cascade) |
| deleted_at | timestamp | soft delete |

---

### Group Activities

#### group_activities
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| title | string | |
| description | longText | nullable |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### group_activity_questions
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| group_activity_id | unsignedBigInt | FK → group_activities.id (cascade) |
| question | longText | |
| type_of_question | string | |
| data | json | nullable |
| deleted_at | timestamp | soft delete |

#### group_activity_groups
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| group_activity_id | unsignedBigInt | FK → group_activities.id (cascade) |
| name | string | |
| deleted_at | timestamp | soft delete |

#### course_groups
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| name | string | |
| deleted_at | timestamp | soft delete |

#### Pivot tables
- `group_activity_has_topics` — group_activity ↔ topic
- `group_activity_has_sub_topics` — group_activity ↔ sub_topic
- `group_activity_has_group` — group_activity ↔ group_activity_group
- `group_activity_has_group_members` — group_activity_group ↔ users
- `course_group_has_users` — course_group ↔ users
- `course_group_has_group_chat` — course_group ↔ group_chat
- `course_group_group_activities` — course_group ↔ group_activity
- `course_has_group_chat` — course ↔ group_chat
- `course_has_course_group` — course ↔ course_group

---

### Communication

#### private_chats
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| creator_id | unsignedBigInt | FK → users.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### private_messages
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| private_chat_id | unsignedBigInt | FK → private_chats.id (cascade) |
| message_body | longText | nullable |
| from_user_id | unsignedBigInt | FK → users.id (cascade) |
| to_user_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### group_chats
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| name | string | |
| user_id | unsignedBigInt | FK → users.id (cascade) — creator |
| deleted_at | timestamp | soft delete |

#### group_messages
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| group_chat_id | unsignedBigInt | FK → group_chats.id (cascade) |
| message_body | longText | nullable |
| sender_id | unsignedBigInt | FK → users.id (cascade) |
| synced_users | boolean | |
| deleted_at | timestamp | soft delete |

#### group_chat_has_users *(pivot)*
| group_chat_id | user_id | Composite PK |

#### announcements
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| content | longText | |
| allow_comments | boolean | |
| deleted_at | timestamp | soft delete |

#### announcement_comments
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| parent_id | unsignedBigInt | nullable — for threaded replies |
| comment | longText | |
| commentable_id | unsignedBigInt | Polymorphic |
| commentable_type | string | Polymorphic |
| deleted_at | timestamp | soft delete |

#### forum_posts
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| title | string | |
| body | longText | |
| deleted_at | timestamp | soft delete |

#### forum_post_comments
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| forum_post_id | unsignedBigInt | FK → forum_posts.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| parent_id | unsignedBigInt | nullable |
| body | longText | |
| deleted_at | timestamp | soft delete |

#### banned_words
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| word | string | unique |
| deleted_at | timestamp | soft delete |

#### tickets
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| concern_category | string | |
| content | longText | |
| status | int | default: 1 |
| deleted_at | timestamp | soft delete |

#### ticket_messages
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| ticket_id | unsignedBigInt | FK → tickets.id (cascade) |
| content | longText | |
| from_user_id | unsignedBigInt | FK → users.id (cascade) |
| to_user_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### advisories
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| title | string | |
| body | longText | |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

---

### Commerce & Payments

#### invoices
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | indexed |
| reference_id | string | nullable |
| official_receipt_no | string | nullable |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| enroll_by_id | unsignedBigInt | nullable |
| type_of_payment | string | |
| mode_of_payment | string | |
| type_of_participant | string | nullable |
| amount | unsignedInt | |
| is_free | boolean | |
| for_verification | boolean | default: false |
| status | unsignedInt | default: 2 |
| receipt_pick_up_option | string | nullable |
| receipt_pick_up_address | string | nullable |
| data | json | nullable |
| deleted_at | timestamp | soft delete |

#### proof_of_payments
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| invoice_id | unsignedBigInt | FK → invoices.id (cascade) |
| reference_number | string | nullable |
| url | longText | |

#### nomination_forms · commitment_letters · endorsement_letters · prime_resolutions
Supporting documents attached to an invoice. All FK → invoices.id (cascade).

#### training_credits
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| credits | unsignedBigInt | |
| deleted_at | timestamp | soft delete |

---

### Video Conferencing (Zoom)

#### zoom_meetings
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| data | json | full Zoom API response |
| deleted_at | timestamp | soft delete |

#### zoom_meeting_attendees
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| zoom_meeting_id | unsignedBigInt | |
| user_id | unsignedBigInt | |
| present | boolean | nullable |
| deleted_at | timestamp | soft delete |

#### zoom_meeting_attendee_activities
Tracks individual join/leave events per attendee per meeting.

---

### SCORM

#### scorms
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| course_id | unsignedBigInt | FK → courses.id (cascade) |
| model_id | unsignedBigInt | Polymorphic (topic / sub_topic) |
| model_type | string | Polymorphic |
| scorm_cloud_course_id | string | |
| title | string | |
| status | string | nullable — `COMPLETED`, `RUNNING`, `ERROR` |
| job_id | string | |
| preview_link | string | nullable |
| data | json | nullable — SCORM Cloud job response |
| errors | longText | nullable |
| deleted_at | timestamp | soft delete |

#### scorm_registrations
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| scorm_id | unsignedBigInt | FK → scorms.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| scorm_cloud_course_id | string | |
| registration_id | string | |
| launch_link | string | nullable |
| scorm_registration_progress_id | unsignedBigInt | nullable |
| deleted_at | timestamp | soft delete |

#### scorm_registration_progress
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| scorm_registration_id | unsignedBigInt | FK → scorm_registrations.id (cascade) |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| completion_status | string | |
| success_status | string | |
| completion_date | dateTime | nullable |
| total_seconds_tracked | int | unsigned |
| first_accessed_date | dateTime | nullable |
| last_accessed_date | dateTime | nullable |
| activity_details | longText | nullable |
| data | longText | nullable |
| deleted_at | timestamp | soft delete |

#### scorm_registration_interactions
xAPI interaction records per registration (type, response, result, timestamp, objectives).

#### scorm_activities · scorm_questions · scorm_activity_answers · scorm_activity_scores · scorm_student_progress
Legacy SCORM activity tracking tables (pre-SCORM Cloud integration). Track quiz questions, answers, scores, and per-user progress within a SCORM package.

---

### Registration & Custom Fields

#### custom_registration_pages
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| title | string | |
| sort_no | int | default: 0 |
| deleted_at | timestamp | soft delete |

#### custom_registration_fields
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| custom_registration_page_id | unsignedBigInt | FK → custom_registration_pages.id (cascade) |
| input_field_label | string | |
| input_field_name | string | |
| input_field_type | string | |
| is_required | boolean | |
| is_multiple | boolean | |
| sort_no | int | default: 0 |
| data | json | nullable |
| deleted_at | timestamp | soft delete |

#### registration_tables
Configurable admin-facing registration table definitions (labels and sort order).

---

### Documents & Attachments

#### documents
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| uuid | uuid | |
| title | string | |
| url | longText | |
| uploader_id | unsignedBigInt | FK → users.id (cascade) |
| deleted_at | timestamp | soft delete |

#### attachments
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| file_path | longText | |
| file_type | string | |
| file_name | string | |
| attachable_id | unsignedBigInt | Polymorphic |
| attachable_type | string | Polymorphic |

#### images
Polymorphic image store (covers logos, avatars, branding assets).

---

### Landing Page

#### landing_page_assets
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| asset_key | string | |
| url | longText | nullable |
| data | json | nullable |
| deleted_at | timestamp | soft delete |

---

### Logging & Exports

#### activity_log
Spatie Laravel Activitylog table — records who did what, to which model, with full `properties` JSON payload. Includes `event` and `batch_uuid` columns.

#### exports
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| type | string | |
| status | string | |
| file_path | longText | nullable |
| deleted_at | timestamp | soft delete |

#### import_logs
| Column | Type | Notes |
|---|---|---|
| id | bigInt | Primary key |
| user_id | unsignedBigInt | FK → users.id (cascade) |
| course_id | unsignedBigInt | nullable |
| type | longText | nullable |
| status | longText | nullable |
| message | longText | nullable |
| deleted_at | timestamp | soft delete |

---

## Polymorphic Relationships

| Column pair | Used in | Targets |
|---|---|---|
| `learnable_id / learnable_type` | learning_materials | topic, sub_topic |
| `model_id / model_type` | student_module_progress, scorms, scorm_activities, scorm_student_progress | topic, sub_topic, module, assessment |
| `imageable_id / imageable_type` | images | user, course, certificate_template, landing_page_asset |
| `attachable_id / attachable_type` | attachments | question_answer, invoice, ticket |
| `commentable_id / commentable_type` | announcement_comments | announcement, forum_post |
| `subject / causer` | activity_log | any model |

---

## Table Count

~100 tables total.
