<template>
    <v-card width="800" class="overflow-x-hidden">
        <v-container fluid  >
            <section class="d-flex flex-wrap justify-space-between align-center mb-5">
                <section class="primary-font primary--text fw600 f17">
                    How to pay
                </section>
                <section class="text-right">
                    <v-btn icon @click="$emit('close')">
                        <v-icon small>
                            mdi-close
                        </v-icon>
                    </v-btn>
                </section>
            </section>
            
            <section class="f14 secondary-2--text">
                <v-row dense v-for="(item, i) in steps" :key="i">
                    <v-col cols="2">
                        <v-chip
                            class="fw500 primary--text"
                            color="red lighten-5"
                            pill
                        >
                            Step {{ i + 1 }}
                        </v-chip>
                    </v-col>
                    <v-col class="black--text">
                        <div v-if="i === 0" class="mb-1">
                            <ul>
                                <li>
                                    If the agency is covering the cost:
                                    <div class="ml-5">
                                        Upload the Proof of Payment. If Proof of Payment is unavailable, upload a Commitment/Promissory Letter indicating the date of payment. The system will mark the transaction as "Pending" for Admin Team approval.
                                    </div>
                                </li>
                                <li>
                                    If the trainee is paying through their own account:
                                    <div class="ml-5">
                                        After selecting your payment method, you will be prompted to upload the corresponding Proof of Payment.
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div v-else>
                            <div v-for="(text, j) in item.text" :key="j" class="mb-1">
                                {{ text }}
                            </div>
                        </div>
                        <div v-if="item.listHeader" class="mb-1">
                            {{ item.listHeader }}
                        </div>
                        <ol>
                            <li v-for="(list, k) in item.list" :key="k">
                                {{ list }}
                            </li>
                        </ol>
                        <div v-if="i === 4">
                            Click <a href="#" target="_blank"><b>Download CL</b></a> for the template.
                        </div>
                    </v-col>
                </v-row>
            </section>
        </v-container>
                
        <v-row dense no-gutters class="pa-5">
            <v-col>
                <v-btn text color="secondary" block class="poppins" @click="$emit('back')">
                    Back
                </v-btn>
            </v-col>
            <v-col>
                <v-btn color="primary" block class="poppins"
                    @click="$emit('continue')">
                    Continue
                </v-btn>
            </v-col>
        </v-row>
    </v-card>
</template>

<script>
import { mapActions, mapState, mapMutations } from "vuex"

export default {
    props: ['chosen'],
    data: () => ({
        steps: [
            {
                text: [],
                listHeader: null,
                list: []
            },
            {
                text: [
                    'For Over-the-Counter payment, you may pay at any branch of our partner bank.',
                ],
                listHeader: 'Ensure that the payment is made payable or credited to the following bank account:',
                list: [
                    'Account Name: Aster Academy Training Fund',
                    'Type of Account: Current Account',
                    'Account No.: 0000-0000-00',
                    'Bank Branch: Main Branch'
                ]
            },
            {
                text: [],
                listHeader: 'Payment methods include:',
                list: [
                    'Check Payment/Deposit payable to Aster Academy Training Fund',
                    // 'Direct Payment at the main office via cash or check',
                    'Bank Deposit',
                    // 'LDDAP-ADA Payment (List of Due and Demandable Accounts Payable-Advice-to-Debit Account)',
                    'Bank-to-bank Fund Transfer',
                    'Transfer from other banks (via online/mobile banking)',
                    // 'Electronic disbursement'
                ]
            },
            {
                text: [
                    'When uploading your Proof of Payment, kindly use the following FILENAME format:',
                    'COURSE TITLE & DATE OF CONDUCT_LAST NAME, FIRST NAME',
                    'Example: LEAD101-Nov24_2023_DOE, JANE.'
                ],
                listHeader: null,
                list: []
            },
            {
                text: [
                    'If payment is not yet available, participant may opt to submit or upload a Commitment Letter (CL).',
                ]
            }
        ]
    }),
    computed: {
        ...mapState({
            errors: (state) => state.errors,
        }),

        getStepOne(){
            return this.chosen === 'YES' 
                ? 'If the agency is covering the cost, please answer "YES" and upload the Proof of Payment. In the absence of Proof of Payment, please upload a Commitment/Promissory Letter indicating the date of payment. The system will recognize it as a Pending Transaction, and as for the approval of Admin Team.'
                : 'If the answer is "NO," proceed to payment and upload your Proof of Payment.'
        }
    },
    watch: {
    },
    methods: {
        ...mapActions('usr', ['courseCheckout']),

    }
    
}
</script>