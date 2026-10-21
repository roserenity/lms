const instructor_courses_tbl = [
    {
        text: "CLASS",
        value: "class_name",
        align: "start",
        width: '20%',
        sortable: false
    },
    {
        text: "TITLE",
        value: "title",
        align: "start",
        sortable: false
    },
    {
        text: "COURSE CODE",
        value: "course_code",
        align: "start",
        sortable: false
    },
    {
        text: "STATUS",
        value: "status",
        align: "center",
        sortable: false
    },
    {
        text: "TYPE OF LEARNING",
        value: "type_of_learning",
        align: "center",
        width: '15%',
        sortable: false
    },
    {
        text: "DATE CONDUCTED START",
        value: "start",
        align: "center",
        sortable: false
    },
    {
        text: "DATE CONDUCTED END",
        value: "end",
        align: "center",
        sortable: false
    },
    {
        text: "NO. OF ENROLLEES",
        value: "enrollments_count",
        align: "center",
        sortable: false
    },
    {
        text: "ACTION",
        value: "action",
        align: "center",
        sortable: false
    },
]

const enrollees_tbl = [
    {
        text: "NAME",
        value: "name",
        align: "start",
        sortable: false
    },
    {
        text: "EMAIL",
        value: "email",
        align: "start",
        sortable: false
    },
    {
        text: "PROGRESS",
        value: "progress",
        align: "center",
        sortable: false
    },
    {
        text: "ACTION",
        value: "action",
        align: "center",
        sortable: false
    },
]

const enrolled_courses_tbl = [
    {
        text: "TITLE",
        value: "title",
        align: "start",
        sortable: false,
    },
    {
        text: "FINAL GRADES",
        value: "final_grades",
        align: "center",
        sortable: false
    },
    {
        text: "PROGRESS TRACKING",
        value: "progress",
        align: "start",
        sortable: false,
        width: '20%',
    },
]

export { instructor_courses_tbl, enrollees_tbl, enrolled_courses_tbl }