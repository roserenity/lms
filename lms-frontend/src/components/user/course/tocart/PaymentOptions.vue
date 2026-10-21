<template>
    <v-card class="overflow-x-hidden pa-5">
        <section class="d-flex flex-wrap justify-space-between align-center mb-5">
            <section class="primary-font primary--text fw600 f17">
                Type of Payment
            </section>
            <section class="text-right">
                <v-btn icon @click="$emit('close')">
                    <v-icon small>
                        mdi-close
                    </v-icon>
                </v-btn>
            </section>
        </section>

        <FormSelectObject
            :value.sync="type_of_payment"
            :items="type_of_payments"
        />

        <div class="error--text f11" v-if="type_of_payment === ''">Please pick the type of payment from the options.</div>

        <v-row dense class="mt-5">
            <v-col>
                <v-btn block dense text @click="$emit('back')" class="poppins">
                    Back
                </v-btn>
            </v-col>
            <v-col>
                <v-btn primary block dense color="primary" :disabled="type_of_payment === ''" class="poppins"
                    @click="() => { $emit('setPaymentType', type_of_payment), $emit('continue')}">
                    Continue
                </v-btn>
            </v-col>
        </v-row>
    </v-card>
</template>

<script>

export default {
    data: () => ({
        type_of_payment: '',
        type_of_payments: [
            { text: '-', value: '' },
            // { text: 'ONLINE PAYMENT PORTAL', value: 'online_portal' },
            { text: 'CASH DEPOSIT', value: 'cash_deposit' },
            // { text: 'CASH-CASHIER', value: 'cash-cashier' },
            { text: 'CHECK DEPOSIT', value: 'check_deposit' },
            // { text: 'CHECK-CASHIER', value: 'check-cashier' },
            { text: 'FUND TRANSFER', value: 'fund_transfer' },
            { text: 'COMMITMENT LETTER (CL)', value: 'commitment_letter' },
            // { text: 'LDDAP-ADA', value: 'lddap_ada' },
        ]
    }),
}
</script>