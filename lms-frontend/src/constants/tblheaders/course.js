const course_zoom_meeting_participants_tbl = [
    { text: "", value: "action", align: 'center', sortable: false, width: "5%"},
    { text: "NAME", value: "name", sortable: false, width: "45%"},
    { text: "ATTENDANCE STATUS", value: "present", align: 'center', sortable: false},
    { text: "ACTION", value: "actions", align: 'center', sortable: false},
]

const course_group_chats_tbl = [
    { text: "GROUP CHAT NAME", value: "name", align: 'start', sortable: false, width: "50%"},
    { text: "MEMBER COUNT", value: "group_chat_users_count", align: 'center', sortable: false},
    { text: "ACTION", value: "action", align: 'center', sortable: false},
]

const course_group_activity_group_tbl = [
    { text: "GROUP ACTIVITY NAME", value: "name", align: 'start', sortable: false, width: "50%"},
    { text: "MEMBER COUNT", value: "group_activity_has_group_members_count", align: 'center', sortable: false},
    { text: "ACTION", value: "actions", align: 'center', sortable: false},
]

export { course_zoom_meeting_participants_tbl, course_group_chats_tbl, course_group_activity_group_tbl }