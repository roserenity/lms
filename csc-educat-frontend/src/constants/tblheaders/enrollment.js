const courseEnrollment = [
    { text: "COURSES", value: "title", width: "40%", sortable: false},
    { text: "CODE", value: "course_code", width: "100px", sortable: false},
    { text: "TYPE OF LEARNING", value: "type_of_learning", width: "13%", align: 'center', sortable: false},
    { text: "STATUS", value: "status", align: 'center', sortable: false},
    { text: "DATE CREATED", value: "created_at", width: "120px", align: 'center', sortable: false},
    { text: "DATE CONDUCTED START", value: "start", width: "120px", align: 'center', sortable: false},
    { text: "DATE CONDUCTED END", value: "end", width: "120px", align: 'center', sortable: false},
    { text: "FREE", value: "free_invoices", align: 'center', sortable: false},
    { text: "PENDING", value: "pending_invoices", align: 'center', sortable: false},
    { text: "SUCCESS", value: "success_invoices", align: 'center', sortable: false},
    { text: "COMMITMENT LETTERS", value: "commitment_letters", align: 'center', sortable: false},
    { text: "DISQUALIFIED", value: "fail_invoices", align: 'center', sortable: false},
    { text: "DISAPPROVED", value: "disapproved_invoices", align: 'center', sortable: false},
    { text: "CANCELLED", value: "cancel_invoices", align: 'center', sortable: false},
    { text: "ACTION", value: "action", align: 'center', sortable: false}
]

const instructorCourseEnrollment = [
    { text: "COURSES", value: "title", width: "40%", sortable: false},
    { text: "COURSE CODE", value: "course_code", align: "center", width: "40%", sortable: false},
    { text: "TYPE OF LEARNING", value: "type_of_learning", width: "13%", align: 'center', sortable: false},
    { text: "STATUS", value: "status", align: 'center', sortable: false},
    { text: "DATE CONDUCTED START", value: "start", width: "120px", align: 'center', sortable: false},
    { text: "DATE CONDUCTED END", value: "end", width: "120px", align: 'center', sortable: false},
    { text: "FREE", value: "free_invoices", align: 'center', sortable: false},
    { text: "PENDING", value: "pending_invoices", align: 'center', sortable: false},
    { text: "SUCCESS", value: "success_invoices", align: 'center', sortable: false},
    { text: "COMMITMENT LETTERS", value: "commitment_letters", align: 'center', sortable: false},
    { text: "DISQUALIFIED", value: "fail_invoices", align: 'center', sortable: false},
    { text: "DISAPPROVED", value: "disapproved_invoices", align: 'center', sortable: false},
    { text: "CANCELLED", value: "cancel_invoices", align: 'center', sortable: false},
    { text: "ACTION", value: "action", align: 'center', sortable: false}
]

const courseStudentsEnrollment = [
    { text: "", value: "for_verification", sortable: false},
    { text: "MODE OF PAYMENT", value: "mode_of_payment", sortable: false},
    { text: "REFERENCE ID", value: "reference_id", align: 'center', sortable: false},
    { text: "AMOUNT", value: "amount", align: 'center', sortable: false},
    { text: "USER", value: "name", width: "50%", align: 'center', sortable: false},
    { text: "EMAIL", value: "email", align: 'center', sortable: false},
    { text: "CONTACT NUMBER", value: "contact_number", align: 'center', sortable: false},
    { text: "ENROLLED BY", value: "enrolled_by", align: 'center', sortable: false},
    { text: "TRANSACTION DATE", value: "date_enrolled", align: 'center', sortable: false},
    { text: "TYPE OF PAYMENT", value: "type_of_payment", align: 'center', justify: 'center', sortable: false, width: '200px'},
    { text: "TYPE OF PARTICIPANT", value: "type_of_participant", align: 'center', sortable: false, width: '200px'},
    { text: "NOMINATION FORM", value: "nomination_form", align: 'center', sortable: false},
    { text: "PAYMENT REFERENCE NUMBER", value: "reference_number", align: 'center', sortable: false},
    { text: "PROOF OF PAYMENT", value: "proof_of_payment", align: 'center', sortable: false},
    { text: "COMMITMENT LETTER", value: "commitment_letter", align: 'center', sortable: false},
    { text: "PRIME RESOLUTION", value: "prime_resolution", align: 'center', sortable: false},
    { text: "ENDORSEMENT LETTER", value: "endorsement_letter", align: 'center', sortable: false},
    { text: "OFFICIAL RECEIPT", value: "official_receipt", align: 'center', sortable: false},
    // { text: "VERIFY ONLINE PAYMENT STATUS", value: "check", width: '20%', align: 'center', sortable: false},
    { text: "DELIVERY METHOD", value: "receipt_pick_up_option", align: 'center', sortable: false},
    { text: "DELIVERY ADDRESS", value: "receipt_pick_up_address", align: 'center', sortable: false, width: '350px'},
    { text: "PAYMENT STATUS", value: "payment_status_action", align: 'center', sortable: false},
    { text: "ENROLLMENT STATUS", value: "enrollment_status_action", align: 'center', sortable: false}
]

const instructorCourseStudentsEnrollment = [
    { text: "", value: "for_verification", sortable: false},
    { text: "MODE OF PAYMENT", value: "mode_of_payment", sortable: false},
    { text: "REFERENCE ID", value: "reference_id", align: 'center', sortable: false},
    { text: "USER", value: "name", width: "50%", align: 'center', sortable: false},
    { text: "EMAIL", value: "email", align: 'center', sortable: false},
    { text: "CONTACT NUMBER", value: "contact_number", align: 'center', sortable: false},
    { text: "ENROLLED BY", value: "enrolled_by", align: 'center', sortable: false},
    { text: "TRANSACTION DATE", value: "date_enrolled", align: 'center', sortable: false},
    { text: "TYPE OF PAYMENT", value: "type_of_payment", align: 'center', justify: 'center', sortable: false},
    { text: "TYPE OF PARTICIPANT", value: "type_of_participant", align: 'center', sortable: false, width: '200px'},
    { text: "NOMINATION FORM", value: "nomination_form", align: 'center', sortable: false},
    { text: "PAYMENT REFERENCE NUMBER", value: "reference_number", align: 'center', sortable: false},
    { text: "PROOF OF PAYMENT", value: "proof_of_payment", align: 'center', sortable: false},
    { text: "COMMITMENT LETTER", value: "commitment_letter", align: 'center', sortable: false},
    { text: "PRIME RESOLUTION", value: "prime_resolution", align: 'center', sortable: false},
    { text: "ENDORSEMENT LETTER", value: "endorsement_letter", align: 'center', sortable: false},
    { text: "OFFICIAL RECEIPT", value: "official_receipt", align: 'center', sortable: false},
    { text: "DELIVERY METHOD", value: "receipt_pick_up_option", align: 'center', sortable: false},
    { text: "DELIVERY ADDRESS", value: "receipt_pick_up_address", align: 'center', sortable: false, width: '350px'},
    { text: "PAYMENT STATUS", value: "payment_status_action", align: 'center', sortable: false},
    { text: "ENROLLMENT STATUS", value: "enrollment_status_action", align: 'center', sortable: false}
]

const instructorCourseStudentsProgress = [
    { text: "USER", value: "name", width: "50%", align: 'start', sortable: false},
    { text: "EMAIL", value: "email", align: 'center', sortable: false},
    { text: "CONTACT", value: "contact_number", align: 'center', sortable: false},
    { text: "PROGRESS TRACKING", value: "progress", align: "start", sortable: false, width: '20%'},
    { text: "STATUS", value: "status", align: "start", sortable: false},
    { text: "DATE COMPLETED", value: "date_completed", align: 'center', sortable: false},
    { text: "COMPLETION CERTIFICATE", value: "certificate", align: 'center', sortable: false},
]

export { courseEnrollment, courseStudentsEnrollment, instructorCourseEnrollment, instructorCourseStudentsEnrollment, instructorCourseStudentsProgress }