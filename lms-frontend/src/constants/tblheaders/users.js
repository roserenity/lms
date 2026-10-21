const user_management_tbl = [
    { text: 'USER TYPE', value: 'role', width: "15%", sortable: false },
    { text: 'NAME', value: 'full_name', sortable: false},
    { text: 'EMAIL', value: 'email', sortable: false },
    { text: 'STATUS', value: 'status', width: "15%", sortable: false },
    { text: 'EMAIL VERIFICATION STATUS', value: 'email_verified_at', width: "15%", sortable: false },
    { text: 'DATE REGISTERED', value: 'created_at', width: "10%", align: 'center', sortable: false },
    { text: 'ACTION', value: 'action', width: "10%", sortable: false, align: 'center' },
]

const activity_log_tbl = [
    { text: 'ACTIVITY', value: 'description', width: "", sortable: false },
    { text: 'COURSE', value: 'course.title', width: "", sortable: false },
    { text: 'DESCRIPTION', value: 'desc', width: "", sortable: false },
    { text: 'DATE', value: 'created_at', align: 'center', sortable: false, width: "10%" },
]

const certificate_issuance_tbl = [
    { text: 'NAME', value: 'user.full_name', sortable: false},
    { text: 'EMAIL', value: 'user.email', sortable: false },
    { text: 'CERTIFICATE', value: 'certificate', width: "", sortable: false },
]

export { user_management_tbl, activity_log_tbl, certificate_issuance_tbl }