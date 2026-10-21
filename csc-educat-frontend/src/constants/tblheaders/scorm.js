const course_scorm_learners_tbl = [
    { text: "LEARNER", value: "user.full_name", width: "30%", sortable: false},
    { text: "EMAIL", value: "user.email", align: 'center', sortable: false},
    { text: "COMPLETION", value: "scorm_progress.completion_status", align: 'center', sortable: false},
    /* { text: "SUCCESS", value: "scorm_progress.success_status", align: 'center', sortable: false}, */
    { text: "TIME SPENT", value: "scorm_progress.time_spent", align: 'center', sortable: false},
    { text: "COMPLETION DATE", value: "scorm_progress.completion_date", align: 'center', sortable: false},
    { text: 'ACTION', value: 'action', align: 'center', sortable: false }
]

const course_scorm_learner_progress_tbl = [
    { text: "TYPE OF QUESTION", value: "type", width: "5%", sortable: false},
    { text: "QUESTION(S)", value: "question", width: "30%", sortable: false},
    { text: "RESPONSE", value: "learnerResponse", align: 'center', sortable: false},
    { text: "RESULT", value: "result", align: 'center', sortable: false},
    { text: "CORRECT ANSWER", value: "correctResponses", align: 'center', sortable: false},
    { text: "ATTEMPT NO.", value: "attempt", align: 'center', sortable: false},
]

const course_scorm_learner_interactions_tbl = [
    /* { text: "", value: "type", sortable: false}, */
    { text: "QUESTION", value: "interaction_id", width: "40%", align: 'start', sortable: false},
    { text: "QUESTION TYPE", value: "interaction_type", align: 'center', sortable: false},
    { text: "RESULT", value: "result", align: 'center', sortable: false},
    { text: "LEARNER RESPONSE", value: "learner_response", align: 'center', sortable: false},
    /* { text: "CORRECT RESPONSES", value: "correct_responses", width: "10%", align: 'center', sortable: false}, */
    { text: "ATTEMPT NO.", value: "attempt_no", width: "5%", align: 'center', sortable: false},
]

export { course_scorm_learners_tbl, course_scorm_learner_progress_tbl, course_scorm_learner_interactions_tbl }