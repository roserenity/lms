const mode_of_payment = [
    { text: 'All', value: '' },
    { text: 'OFFLINE', value: 'offline' },
    // { text: 'ONLINE', value: 'online' },
]

const offline_type_of_payment = [
    { text: '-', value: '' },
    { text: 'CASH DEPOSIT', value: 'cash_deposit' },
    // { text: 'CASH-CASHIER', value: 'cash-cashier' },
    { text: 'CHECK DEPOSIT', value: 'check_deposit' },
    // { text: 'CHECK-CASHIER', value: 'check-cashier' },
    { text: 'DEBIT MEMO', value: 'debit_memo' },
    { text: 'FUND TRANSFER', value: 'fund_transfer' },
    { text: 'COMMITMENT LETTER (CL)', value: 'commitment_letter' },
    // { text: 'LDDAP-ADA', value: 'lddap_ada' },
]

const online_type_of_payment = [
    { text: '-', value: '' },
    // { text: 'ONLINE PAYMENT PORTAL', value: 'online_portal' },
]

const payment_status = [
    { text: 'All', value: '' },
    { text: 'PENDING', value: 2 },
    { text: 'FREE', value: 6 },
    { text: 'APPROVED', value: 1 },
    { text: 'COMMITMENT LETTER', value: 5 },
    { text: 'DISAPPROVED (INVALID POP)', value: 7 },
    { text: 'CANCELLED', value: 4 },
    { text: 'DISQUALIFIED', value: 3 },
]

const enrollment_status = [
    { text: 'All', value: '' },
    { text: 'ENROLLED', value: '1' },
    { text: 'NOT ENROLLED', value: '2' },
]

const delivery_option = [
    { text: 'All', value: '' },
    { text: 'PICK UP', value: 'pick_up' },
    { text: 'COURIER', value: 'courier' },
]

export  { mode_of_payment, offline_type_of_payment, online_type_of_payment, payment_status, enrollment_status, delivery_option }