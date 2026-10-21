const shared_with_me_tbl = [
    {
        text: "TITLE",
        value: "title",
        align: "start",
        sortable: false,
    },
    {
        text: "SHARED BY",
        value: "shared_by",
        align: "center",
        sortable: false,
    },
    {
        text: "DATE SHARED",
        value: "date_shared",
        align: "center",
        sortable: false,
    },
    {
        text: "ACTION",
        value: "action",
        align: "center",
        sortable: false,
    },
]

const share_to_tbl = [
    {
        text: "TITLE",
        value: 'title',
        align: 'start',
        sortable: false,
    },
    {
        text: "DATE SHARED",
        value: 'date_shared',
        align: 'center',
        sortable: false,
    },
    {
        text: "LAST MODIFIED",
        value: 'last_modified',
        align: 'center',
        sortable: false,
    },
    {
        text: "SHARED TO",
        value: 'shared_to',
        align: 'center',
        sortable: false,
    },
]

const import_logs_tbl = [
    {
        text: "TYPE",
        value: 'type',
        align: 'start',
        sortable: false,
        width: '200px'
    },
    {
        text: "COURSE",
        value: 'course.title',
        align: 'start',
        sortable: false,
        width: '200px'
    },
    {
        text: "STATUS",
        value: 'status',
        align: 'center',
        sortable: false,
    },
    {
        text: "MESSAGE",
        value: 'message',
        align: 'start',
        sortable: false,
    },
    {
        text: "ACTION",
        value: 'action',
        align: 'center',
        sortable: false,
    },
] 

export { shared_with_me_tbl, share_to_tbl, import_logs_tbl }