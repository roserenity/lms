const ticket_tbl = [
    { text: "TICKET #", value: "id", align: "center" },
    { text: "CATEGORY", value: "concern_category", align: "center" },
    { text: "STATUS", value: "status", align: "center" },
    { text: "DATE SUBMITTED", value: "created_at", align: "center" },
]

const admin_ticket_tbl = [
    { text: "TICKET #", value: "id", align: "center" },
    { text: "SUBMITTED BY", value: "submitted_by", align: "center" },
    { text: "STATUS", value: "status", align: "center" },
    { text: "DATE SUBMITTED", value: "created_at", align: "center" },
]

export { ticket_tbl, admin_ticket_tbl }