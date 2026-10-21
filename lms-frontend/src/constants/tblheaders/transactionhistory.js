const transaction_history_tbl_paid = [
    { text: "MODE OF PAYMENT", value: "mode_of_payment", sortable: false},
    { text: "COURSE", value: "title", width: "10%", sortable: false},
    { text: "REFERENCE ID", value: "reference_id", width: "10%", sortable: false},
    { text: "TRANSACTION DATE", value: "date", align: 'center'},
    { text: "AMOUNT", value: "amount", align: 'center', sortable: false},
    { text: "PAYMENT STATUS", value: "status", align: 'center', sortable: false},
    { text: "TYPE OF PAYMENT", value: "type_of_payment", align: 'center', sortable: false, width: '200px'},
    { text: "TYPE OF PARTICIPANT", value: "type_of_participant", align: 'center', sortable: false, width: '200px'},
    { text: "NOMINATION FORM", value: "nomination_form", align: 'center', sortable: false},
    { text: "PAYMENT REFERENCE NUMBER", value: "reference_number", align: 'center', sortable: false},
    { text: "PROOF OF PAYMENT", value: "proof_of_payment", align: 'center', sortable: false},
    { text: "COMMITMENT LETTER", value: "commitment_letter", align: 'center', sortable: false},
    { text: "PRIME RESOLUTION", value: "prime_resolution", align: 'center', sortable: false},
    { text: "ENDORSEMENT LETTER", value: "endorsement_letter", align: 'center', sortable: false},
    { text: "RECEIPT", value: "receipt", align: 'center', sortable: false},
    { text: "DELIVERY METHOD", value: "receipt_pick_up_option", align: 'center', sortable: false},
    { text: "DELIVERY ADDRESS", value: "receipt_pick_up_address", align: 'center', sortable: false, width: '350px'},
    { text: "DATE ENROLLED", value: "date_enrolled", align: 'center', sortable: false},
    { text: "", value: "action", sortable: false },
    { text: "", value: "cancel", align: 'center', sortable: false }
]

const transaction_history_tbl_free = [
    { text: "REFERENCE ID", value: "reference_id", width: "10%", sortable: false},
    { text: "COURSE", value: "title", width: "30%"},
    { text: "DATE ENROLLED", value: "date", align: 'center'},
    { text: "TYPE OF PARTICIPANT", value: "type_of_participant", align: 'center', sortable: false},
    { text: "PRIME RESOLUTION", value: "prime_resolution", align: 'center', sortable: false},
    { text: "ENDORSEMENT LETTER", value: "endorsement_letter", align: 'center', sortable: false},
]

export  { transaction_history_tbl_paid, transaction_history_tbl_free }