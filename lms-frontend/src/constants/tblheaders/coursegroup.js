const course_group_tbl = [
    { text: "GROUP NAME", value: "name", sortable: false, width: "25%" },
    { text: "COURSE", value: "course.title", align: 'center', sortable: false, width: "35%"},
    { text: "MEMBER COUNT", value: "course_group_has_users_count", align: 'center', sortable: false},
    { text: "ACTION", value: "action", align: 'center', sortable: false},
]

const course_group_members_tbl = [
    { text: "NAME", value: "full_name", sortable: false, width: "45%" },
    { text: "EMAIL", value: "email", align: 'center', sortable: false },
    { text: "ACTION", value: "action", align: 'center', sortable: false},
]

const user_subgroup_tbl = [
    { text: "GROUP NAME", value: "name", sortable: false, width: "45%" },
    { text: "MEMBER COUNT", value: "course_subgroup_has_users_count", align: 'center', sortable: false},
    { text: "ACTION", value: "action", align: 'center', sortable: false},
]
export { course_group_tbl, course_group_members_tbl, user_subgroup_tbl }