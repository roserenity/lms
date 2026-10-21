const instructor_reporting_courses_tbl = [
    { text: "COURSE", value: "title", sortable: false},
    { text: "CODE", value: "course_code", sortable: false},
    { text: "STATUS", value: "status", align: 'center', sortable: false},
    { text: "TYPE OF LEARNING", value: "type_of_learning", align: 'center', sortable: false},
    { text: "TOTAL NO. OF ENROLLEES", value: "total_enrollments", align: 'center', sortable: false },
    { text: "ENROLLEES", value: "enrollments_count", align: 'center', sortable: false },
    { text: "COMPLETERS", value: "completed_course_count", align: 'center', sortable: false },
    { text: "TRAINING HOURS", value: "training_hours", align: 'center', sortable: false },
    { text: "DATE CONDUCTED START", value: "start", align: 'center', sortable: false },
    { text: "DATE CONDUCTED END", value: "end", align: 'center', sortable: false },
    { text: "CREATED AT", value: "created_at", align: 'center', width: "10%", sortable: false },
]

const instructor_reporting_enrolled_users_tbl = [
    { text: "ENROLLMENT DATE", value: "date_enrolled", sortable: false, width: "20%", align: 'start'},
    { text: "USER", value: "name", width: "50%", align: 'start', sortable: false},
    { text: "EMAIL", value: "email", align: 'center', sortable: false},
    { text: "CONTACT", value: "contact_number", align: 'center', sortable: false},
    { text: "PROGRESS TRACKING", value: "progress", align: "start", sortable: false, width: '20%'},
    { text: "STATUS", value: "status", align: "start", sortable: false},
    { text: "DATE COMPLETED", value: "date_completed", align: 'center', sortable: false},
    { text: "COMPLETION CERTIFICATE", value: "certificate", align: 'center', sortable: false},
]

const instructor_reporting_completers_users_tbl = [
    { text: "ENROLLMENT DATE", value: "user.enrollments.0.created_at", sortable: false},
    { text: "FIRST NAME", value: "user.first_name", align: 'center', sortable: false},
    { text: "LAST NAME", value: "user.last_name", align: 'center', sortable: false },
    { text: "SUFFIX", value: "user.suffix", align: 'center', sortable: false },
    { text: "EMAIL", value: "user.email", align: 'center', sortable: false },
]

const instructor_reporting_assessments_tbl = [
    { text: "TITLE", value: "title", sortable: false, width: '60%'},
    { text: "PASSERS", value: "passed_count", align: 'center', sortable: false},
    { text: "FAILURES", value: "failed_count", align: 'center', sortable: false},
    { text: "ANSWERED", value: "answered_count", align: 'center', sortable: false},
    { text: "UNANSWERED", value: "unanswered_count", align: 'center', sortable: false},
]

export { instructor_reporting_courses_tbl, instructor_reporting_enrolled_users_tbl, instructor_reporting_completers_users_tbl, instructor_reporting_assessments_tbl }