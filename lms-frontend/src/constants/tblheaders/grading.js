const grading_tbl = [
    { text: "COURSE", value: "title", width: "70%", sortable: false},
    { text: "COURSE CODE", value: "course_code", width: "13%", align: 'center', sortable: false},
    { text: "TYPE OF LEARNING", value: "type_of_learning", width: "13%", align: 'center', sortable: false},
    { text: "STATUS", value: "status", width: "10%", sortable: false, align: "center"},
    { text: "DATE CONDUCTED START", value: "start", width: "120px", align: 'center', sortable: false},
    { text: "DATE CONDUCTED END", value: "end", width: "120px", align: 'center', sortable: false},
    { text: "ASSESSMENTS", value: "assessments_count", align: "center", sortable: false},
    { text: "EVALUATIONS", value: "evaluations_count", align: "center", sortable: false},
    { text: "GROUP ACTIVITIES", value: "group_activities_count", align: "center", sortable: false},
    { text: "SCORM", value: "scorm_count", align: "center", sortable: false},
]

const grading_assessment_tbl = [
    { text: "TITLE", value: "title", width: "60%", sortable: false}, 
    { text: "FOR CHECKING", value: "checking", width: "20%", align: "center", sortable: false},
    { text: "DUE DATE", value: "due_date", width: "20%", align: "center", sortable: false}
]

const grading_assessment_student_tbl = [
    { text: "", value: "select", sortable: false, align: "center"},
    { text: "STUDENT", value: "student", width: "30%", sortable: false },
    { text: "GRADING STATUS", value: "checked", align: 'center', sortable: false },
    { text: "DATE ACCOMPLISHED", value: "date", align: 'center', sortable: false },
    { text: "TIME ACCOMPLISHED", value: "time", align: 'center', sortable: false },
    { text: "SCORE", value: "score", align: 'center', sortable: false },
    { text: "ACTION", value: "action", align: 'center', sortable: false }
]

const grading_survey_tbl = [
    { text: "", value: "select", sortable: false, align: "center"},
    { text: "USER", value: "name", sortable: false,},
    { text: "STATUS", value: "status", align: 'center' , sortable: false},
    { text: "NO. OF ATTEMPTS", value: "attempts", align: 'center', sortable: false},
    { text: "DATE ACCOMPLISHED", value: "date_submitted", align: 'center' , sortable: false},
    { text: "ACTION", value: "action", align: 'center', sortable: false },
]

const grading_scorm_activities_tbl = [
    { text: "USER", value: "user.full_name", sortable: false,},
    { text: "EMAIL", value: "user.email", align: 'center', sortable: false,},
    { text: "SCORE", value: "score", align: 'center' , sortable: false},
    { text: "STATUS", value: "passed", align: 'center' , sortable: false},
    { text: "DATE ACCOMPLISHED", value: "created_at", align: 'center' , sortable: false},
    { text: "ACTION", value: "action", align: 'center', sortable: false },
]

const grading_evaluation_tbl = [
    { text: "TYPE", value: "type_of_assessment", sortable: false},
    { text: "TITLE", value: "title", sortable: false},
    { text: "NO. OF SUBMITTERS", value: "no_of_submitters", align: "center", sortable: false},
    { text: "ACTION", value: "action", align: 'center', sortable: false },
]

const grading_group_activity_tbl = [
    { text: "GROUP NAME", value: "name", sortable: false},
    { text: "GROUP MEMBER COUNT", value: "group_activity_has_group_members_count", align: "center", sortable: false},
    { text: "STATUS", value: "checked", align: "center", sortable: false},
    { text: "ACTION", value: "action", align: 'center', sortable: false },
]

export { grading_tbl, grading_assessment_tbl, grading_assessment_student_tbl, grading_survey_tbl, grading_evaluation_tbl, grading_scorm_activities_tbl, grading_group_activity_tbl }