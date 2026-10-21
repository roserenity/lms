<template>
    <section>
        <v-dialog v-model="proofOfPaymentDialog" persistent width="370">
            <v-card width="370" class="overflow-x-hidden">
                <UploadProofOfPayment 
                    @setProofOfPayment="(e) => proof_of_payment = e"
                    :error="errors.proof_of_payment && errors.proof_of_payment[0]"
                />
                
                <v-row dense class="pa-3">
                    <v-col>
                        <v-btn text block class="poppins" :disabled="proofOfPaymentLoading" @click="() => {
                            remove()
                            proofOfPaymentDialog = false
                        }">
                            Cancel
                        </v-btn>
                    </v-col>
                    <v-col>
                        <v-btn color="primary" block class="poppins"
                            @click="submitProofOfPayment()" :loading="proofOfPaymentLoading">
                            Submit
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card>
        </v-dialog>
        <v-dialog v-model="commitmentLetterDialog" persistent width="370">
            <v-card width="370" class="overflow-x-hidden">
                <UploadCommitmentLetter 
                    @setCommitmentLetter="(e) => commitment_letter = e"
                    :error="errors.commitment_letter && errors.commitment_letter[0]"
                />
                
                <v-row dense class="pa-3">
                    <v-col>
                        <v-btn text block class="poppins" :disabled="commitmentLetterLoading" @click="() => {
                            remove()
                            commitmentLetterDialog = false
                        }">
                            Cancel
                        </v-btn>
                    </v-col>
                    <v-col>
                        <v-btn color="primary" block :disabled="!commitmentLetterLoading ? !commitment_letter : true" class="poppins"
                            @click="submitCommitmentLetter()" :loading="commitmentLetterLoading">
                            Submit
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card>
        </v-dialog>

        <v-dialog v-model="primeRequirementsDialog" persistent width="370">
            <v-card width="370" class="overflow-x-hidden">
                
                <UploadPrimeRequirements
                    @setEndorsementLetter="(e) => endorsement_letter = e"
                    @setPrimeResolution="(e) => prime_resolution = e"
                    :error_endorsement_letter="errors.endorsement_letter && errors.endorsement_letter[0]"
                    :error_prime_resolution="errors.prime_resolution && errors.prime_resolution[0]"
                />
                
                <v-row dense class="pa-3">
                    <v-col>
                        <v-btn text block class="poppins" :disabled="primeRequirementsLoading" @click="() => {
                            remove()
                            primeRequirementsDialog = false
                        }">
                            Cancel
                        </v-btn>
                    </v-col>
                    <v-col>
                        <v-btn color="primary" block :disabled="!primeRequirementsLoading ? (!prime_resolution && !endorsement_letter) : true" class="poppins"
                            @click="submitPrimeRequirements()" :loading="primeRequirementsLoading">
                            Submit
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card>
        </v-dialog>

        <section class="d-flex flex-wrap poppins align-center">
            <v-btn text dense 
                @click="(prevRoute && prevRoute.name === 'Instructor Enrollment') ? $router.back() : $router.push({ name: 'Instructor Enrollment' , query: { search: '', page: 1, paginate: 10 }})"
                class="mr-1 pa-0 f14">
                <v-icon small>
                    mdi-chevron-left
                </v-icon>
                BACK
            </v-btn>
            <span class="f14">/</span> <span class="fw600 primary--text f14 ml-2">{{ title  }}</span>
        </section>

        <section>
            <section class="d-flex flex-wrap justify-space-between">
                <SearchBar 
                    :placeholder="'Search student'"
                    :value.sync="search" 
                    @search="() => { page = 1, getData()}" 
                    @clear="() => { search = '', page = 1, getData()}"
                    class="mt-3 mb-5"/>
                <section class="d-flex flex-row ml-auto">
                    <FilterMenu
                        :initialFilter="filter"
                        @resetFilters="clearFilters()"
                        @applyFilters="() => {
                                page = 1,
                                updateQuery()
                            }"
                    >
                    <section>
                        <FormSelectObject
                            :label="'MODE OF PAYMENT'"
                            :items="mode_of_payment"
                            :value.sync="filter.mode_of_payment"
                            class="mb-3"
                        />

                        <FormSelectObject
                            :label="'TYPE OF PAYMENT'"
                            :items="filter.mode_of_payment === '' ? type_of_payment : (filter.mode_of_payment === 'online' ? online_type_of_payment : offline_type_of_payment)"
                            :value.sync="filter.type_of_payment"
                            :disabled="filter.mode_of_payment == 'online'"
                            class="mb-3"
                        />

                        <FormSelectObject
                            :label="'PAYMENT STATUS'"
                            :items="payment_status"
                            :value.sync="filter.invoice_status"
                            class="mb-3"
                        />
                        
                        <FormSelectObject
                            :label="'DELIVERY METHOD'"
                            :items="delivery_option"
                            :value.sync="filter.receipt_pick_up_option"
                            class="mb-3"
                        />
                        
                        <FormSelectObject
                            :label="'ENROLLMENT STATUS'"
                            :items="enrollment_status"
                            :value.sync="filter.enrollment_status"
                            class="mb-3"
                        />

                        <FormTextFieldOptional
                            :label="'ENROLLED BY'"
                            :value.sync="filter.enrolled_by" />

                        <div class="mt-3">
                            <FormLabel class="mb-1" :label="'TRANSACTION DATE (YYYY-MM-DD)'" />
                        </div>
                        <label class="caption">FROM:</label>
                        <FormDatePicker 
                            :val="filter.date_from"
                            :max="maxDate"
                            class_="col-12 mb-3"
                            @apply="(e) => {
                                filter.date_from = e
                                minDate = e
                            }"/>

                        <label class="caption">TO:</label>
                        <FormDatePicker 
                            :val="filter.date_to"
                            :min="minDate"
                            class_="col-12 mb-3"
                            @apply="(e) => {
                                filter.date_to = e
                                maxDate = e
                            }"/>
                    </section>
                </FilterMenu>
                    <ButtonExport 
                        @click="exportInvoices"
                        :loading="exportLoading"
                        :disabled="loading || enrollees.length === 0"
                        class="ma-1"/>
                </section>
            </section>

            <section class="mb-3 text-right">
                <FormLabel :label="`${totalCount} result/s`" v-if="totalCount !== 0"/>
                <FormLabel :label="`-`" v-else/>
            </section>
            
            <v-data-table
                :loading="loading"
                :headers="instructorCourseStudentsEnrollment"
                :items="enrollees"
                hide-default-footer
                :items-per-page.sync="itemsPerPage"
                class="text--center custom-border poppins f14 mt-2">
                <template v-slot:item.name="{ item }" >
                    <div class="text-no-wrap">
                        {{ item.name }}
                    </div>
                </template>
                <template v-slot:item.mode_of_payment="{ item }" >
                    <span class="text-uppercase">
                        {{ item.mode_of_payment }}
                    </span>
                </template>
                <template v-slot:item.date_enrolled="{ item }" >
                    {{ $dateFormat.ymd(item.date_enrolled) }}
                </template>
                <template v-slot:item.date_approved="{ item }" >
                    {{ $dateFormat.ymd(item.date_approved) }}
                </template>
                <template v-slot:item.official_receipt="{ item }" >
                    <v-btn 
                        v-if="item.mode_of_payment === 'online'  && item.invoice_status !== 4" outlined 
                        color="primary" dense class="pa-2 f10" @click="requestOR(item)"
                        :loading="invoiceIDForOR === item.invoice_id && requestORLoading"
                        small> Request OR </v-btn>
                    <a
                        v-if="item.mode_of_payment === 'offline' && item.official_receipt"
                        :href="item?.official_receipt?.url"
                        class="primary--text text-decoration-none f10"
                        target="_blank"
                        >
                        <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                        View OR
                    </a>
                </template>
                <template v-slot:item.payment_status_action="{ item }">
                    <span class="secondary-2--text px-2 border">
                        {{ payment_status.find((payment) => payment.value === item.invoice_status ).text }}
                    </span>
                </template>
                <template v-slot:item.enrollment_status_action="{ item }">
                    <span dense class="secondary-2--text px-2 border" v-if="item.enrollment_status === 1" style="background-color: #7BC14533">ENROLLED</span>
                    <span dense class="secondary-2--text px-2 border text-no-wrap" v-if="item.enrollment_status === 2">NOT ENROLLED</span>
                </template>
                <template v-slot:item.for_verification="{ item }">
                    <v-chip v-if="item.for_verification === 1" x-small dense color="primary" class="ml-1">
                        FOR VERIFICATION
                    </v-chip>
                </template>
                <template v-slot:item.reference_number="{ item }">
                    <div v-if="item.proof_of_payment && item.proof_of_payment.reference_number">
                        {{ item.proof_of_payment.reference_number }}
                    </div>
                </template>
                <template v-slot:item.nomination_form="{ item }" >
                    <a
                        v-if="item.nomination_form && item.nomination_form.url !== ''"
                        :href="item.nomination_form.url"
                        class="primary--text text-decoration-none f10"
                        target="_blank"
                        >
                        <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                        View
                    </a>
                </template>
                <template v-slot:item.commitment_letter="{ item }" >
                    <a
                        v-if="item.commitment_letter && item.commitment_letter.url !== ''"
                        :href="item.commitment_letter.url"
                        class="primary--text text-decoration-none f10"
                        target="_blank"
                        >
                        <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                        View
                    </a>
                    <v-btn class="ma-3 pa-2 f12" v-else-if="!item.commitment_letter && item.enrolled_by_id && item.type_of_payment === 'commitment_letter'" outlined color="secondary-1" dense @click="() => { transaction = item, commitmentLetterDialog = true }">
                        <v-icon class="mr-1" dense small>
                            mdi-upload-outline
                        </v-icon>
                        Upload Commitment Letter
                    </v-btn>
                </template>
                <template v-slot:item.prime_resolution="{ item }" >
                    <a
                        v-if="item.prime_resolution && item.prime_resolution.url !== ''"
                        :href="item.prime_resolution.url"
                        class="primary--text text-decoration-none f10"
                        target="_blank"
                        >
                        <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                        View
                    </a>
                    
                    <v-btn class="ma-3 pa-2 f12" v-else-if="!item.prime_resolution && item.enrolled_by_id && item.type_of_participant === 'regular_payee_hrm'" outlined color="secondary-1" dense @click="() => { transaction = item, primeRequirementsDialog = true }">
                        <v-icon class="mr-1" dense small>
                            mdi-upload-outline
                        </v-icon>
                        Upload Prime Requirements
                    </v-btn>
                </template>
                <template v-slot:item.endorsement_letter="{ item }" >
                    <a
                        v-if="item.endorsement_letter && item.endorsement_letter.url !== ''"
                        :href="item.endorsement_letter.url"
                        class="primary--text text-decoration-none f10"
                        target="_blank"
                        >
                        <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                        View
                    </a>
                </template>
                <template v-slot:item.type_of_participant="{ item }" >
                    <div v-if="item.type_of_participant !== 'null'">
                        <span class="text-capitalize" v-if="item.type_of_participant && item.type_of_participant !== 'regular_payee_hrm'">
                            {{ item.type_of_participant.replaceAll('_', ' ').replace('hrm', 'HRM') }}
                        </span>
                        <span class="text-capitalize" v-else-if="item.type_of_participant && item.type_of_participant === 'regular_payee_hrm'">
                            REGULAR PAYEE (Partner Awardee)
                        </span>
                    </div>
                </template>
                <template v-slot:item.type_of_payment="{ item }" >
                    <span class="text-capitalize" v-if="item && item.type_of_payment">
                        {{ item.type_of_payment.replace('_', ' ') }}
                    </span>
                </template>
                <template v-slot:item.receipt_pick_up_option="{ item }" >
                    <span class="text-capitalize" v-if="item.receipt_pick_up_option">
                        {{ item.receipt_pick_up_option.replace('_', ' ') }}
                    </span>
                </template>
                <template v-slot:item.proof_of_payment="{ item }" >
                    <a
                        v-if="item.proof_of_payment && item.proof_of_payment.url !== ''"
                        :href="item.proof_of_payment.url"
                        class="primary--text text-decoration-none f12"
                        target="_blank"
                        >
                        <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                        View
                    </a>
                    <!-- <v-btn class="ma-3 pa-2 f12" v-if="[2, 5].includes(item.invoice_status) && !item.proof_of_payment && item.type_of_payment !== 'online portal'" outlined color="secondary-1" dense @click="() => { transaction = item, proofOfPaymentDialog = true }">
                        <v-icon class="mr-1" dense small>
                            mdi-upload-outline
                        </v-icon>
                        Upload Proof of Payment
                    </v-btn> -->
                    <!-- <v-btn class="ma-3 pa-2 f12" v-if="item.invoice_status === 2 && !item.proof_of_payment && item.type_of_payment === 'commitment_letter'" outlined color="secondary-1" dense @click="() => { transaction = item, proofOfPaymentDialog = true }">
                        <v-icon class="mr-1" dense small>
                            mdi-upload-outline
                        </v-icon>
                        Upload Proof of Payment
                    </v-btn> -->
                </template>
            </v-data-table>
            
            <FormPagination 
                :pageCount="pageCount" 
                :page="page"
                :paginate="paginate"
                @page="(e) => {page = e, getData()}" 
                @paginate="(e) => {paginate = e, page = 1, getData()}"/>
        </section>

    </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
import { mode_of_payment, offline_type_of_payment, online_type_of_payment, payment_status, enrollment_status, delivery_option } from '@/constants/payment'
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import { instructorCourseStudentsEnrollment } from '@/constants/tblheaders/enrollment';
import UploadCommitmentLetter from '@/components/user/course/tocart/UploadCommitmentLetter.vue';
import UploadPrimeRequirements from '@/components/user/course/tocart/UploadPrimeRequirements.vue';
import UploadProofOfPayment from '@/components/user/course/tocart/UploadProofOfPayment.vue';

export default {
    components: { UploadPrimeRequirements, UploadCommitmentLetter, UploadProofOfPayment },
    mixins: [searchAndPaginateMixin],
    mounted() {
        this.getData()
    },
    data: () => ({
        tab: 0,
        loading: false,
        exportLoading: false,
        statusLoading: false,
        item: null,
        selected: null,
        dialog: false,
        title: '',
        filter: {
            date_from: '',
            date_to: '',
            mode_of_payment: '',
            type_of_payment: '',
            invoice_status: '',
            enrolled_by: '',
            progress_status: '',
            enrollment_status: '',
            receipt_pick_up_option: ''
        },
        modelFrom: false,
        modelTo: false,
        type_of_payment: [
            { text: 'All', value: '' },
            { text: 'CASH DEPOSIT', value: 'cash_deposit' },
            // { text: 'CASH-CASHIER', value: 'cash-cashier' },
            { text: 'CHECK DEPOSIT', value: 'check_deposit' },
            // { text: 'CHECK-CASHIER', value: 'check-cashier' },
            { text: 'DEBIT MEMO', value: 'debit_memo' },
            // { text: 'LDDAP-ADA', value: 'lddap_pada' },
            { text: 'FUND TRANSFER', value: 'fund_transfer' },
            // { text: 'ONLINE PAYMENT PORTAL', value: 'online_portal' },
            { text: 'COMMITMENT LETTER (CL)', value: 'commitment_letter' },
            // { text: 'LDDAP-ADA', value: 'lddap_ada' },
        ],
        payment_status,
        mode_of_payment,
        enrollment_status,
        offline_type_of_payment,
        online_type_of_payment,
        delivery_option,
        invoiceIDForOR: null,
        prevRoute: null,
        minDate: '',
        maxDate: '',
        totalCount: 0,
        pageCount: 1,
        page: 1,
        paginate: '10',
        instructorCourseStudentsEnrollment,
        proofOfPaymentDialog: false,
        proofOfPaymentLoading: false,
        commitmentLetterDialog: false,
        commitmentLetterLoading: false,
        primeRequirementsDialog: false,
        primeRequirementsLoading: false,
        commitment_letter: null,
        prime_resolution: null,
        endorsement_letter: null,
        proof_of_payment: null,
        transaction: null
    }),
    methods: {
        ...mapActions('instructor', ['getCourseEnrolleesAction', 'requestOfficialReceipt', 'paymentExportAction', 'progressExportAction', 'courseProofOfPaymentAction', 'courseCommitmentLetterAction', 'coursePrimeRequirementsAction']),
        ...mapMutations(['alertMutation']),

        errorMessage(){
            this.alertMutation({
                show: true,
                text: "Something went wrong",
                type: "error"
            })
        },

        clearFilters(){
            this.filter = {
                date_from: '',
                date_from: '',
                date_to: '',
                mode_of_payment: '',
                type_of_payment: '',
                invoice_status: '',
                enrolled_by: '',
                receipt_pick_up_option: ''
            }

            this.minDate = ''
            this.maxDate = ''
        },

        remove(){
            this.transaction = null
            this.commitment_letter = null
            this.prime_resolution = null
            this.endorsement_letter = null
            this.proof_of_payment = null
        },

        async requestOR(item) {
            this.requestORLoading = true
            this.invoiceIDForOR = item.invoice_id
            this.requestOfficialReceipt({ 
                course_uuid: this.$route.params.course_uuid, 
                user_id: item.user_id,
                email: item.email,
                reference_id: item.reference_id,
            }).then((res) => {
                this.alertMutation({
                    show: true,
                    text: "Requested OR successfully generated!",
                    type: "success"
                })
                this.$exportToPDF(`${item.name}`, res)
            }).catch(err => {
                if (err.response && err.response.status === 404) {
                    this.alertMutation({
                        show: true,
                        text: 'Official Receipt not available.',
                        type: "error"
                    })
                } else {
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                }
            }).finally(() => {
                this.requestORLoading = false
                this.invoiceIDForOR = null
            })
        },

        exportInvoices(){
            this.exportLoading = true
            this.paymentExportAction({
                course_uuid: this.$route.params.course_uuid,
                search: this.search,
                ...this.filter
            }).then((res) => {
                this.alertMutation({
                    show: true,
                    text: 'Export is being processed in the background.',
                    type: "success"
                })
            }).catch(e => {
                this.errorMessage()
            }).finally(() => {
                this.exportLoading = false
            })
        },

        setCourseTitle(_title) {
            this.title = _title
        },

        getData() {
            if(!this.loading) {
                this.loading = true
                this.getCourseEnrolleesAction({
                    course_uuid: this.$route.params.course_uuid,
                    search: this.search,
                    page: this.page,
                    paginate: Number(this.paginate),
                    ...this.filter
                }).then(res => {
                    this.page = res.invoices.current_page
                    this.pageCount = res.invoices.last_page
                    this.paginate = String(res.invoices.per_page)
                    this.title = res.title
                    this.totalCount = res.invoices.total
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                    this.errorMessage()
                })
            }
        },

        submitCommitmentLetter() {

            this.commitmentLetterLoading = true

            this.msg = {
                show: false,
                type: '',
                text: ''
            }

            let _form = new FormData()

            _form.append('invoice_id', this.transaction.invoice_id)
            _form.append('commitment_letter', this.commitment_letter)

            this.courseCommitmentLetterAction(_form).then(() => {
                this.commitmentLetterDialog = false
                this.alertMutation({
                    show: true,
                    text: 'Commitment Letter uploading success!',
                    type: "success"
                })
                this.getData()
            }).catch(res => {
                this.commitmentLetterLoading = false
                if(this.errors.commitment_letter) {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: this.errors.commitment_letter[0]
                    }
                } else {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: res.message
                    }
                }
            }).finally(() => {
                this.commitmentLetterLoading = false
            })

        },

        submitPrimeRequirements() {

            this.primeRequirementsLoading = true

            this.msg = {
                show: false,
                type: '',
                text: ''
            }

            let _form = new FormData()

            _form.append('invoice_id', this.transaction.invoice_id)
            _form.append('prime_resolution', this.prime_resolution)
            _form.append('endorsement_letter', this.endorsement_letter)

            this.coursePrimeRequirementsAction(_form).then(() => {
                this.primeRequirementsDialog = false
                this.alertMutation({
                    show: true,
                    text: 'Prime Requirements uploading success!',
                    type: "success"
                })
                this.getData()
            }).catch(res => {
                this.primeRequirementsLoading = false
                if(this.errors.prime_resolution) {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: this.errors.prime_resolution[0]
                    }
                } else if(this.errors.endorsement_letter) {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: this.errors.endorsement_letter[0]
                    }
                } else {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: res.message
                    }
                }
            }).finally(() => {
                this.primeRequirementsLoading = false
            })
        },

        submitProofOfPayment() {

            this.proofOfPaymentLoading = true

            this.msg = {
                show: false,
                type: '',
                text: ''
            }

            let _form = new FormData()

            _form.append('invoice_id', this.transaction.invoice_id)
            _form.append('proof_of_payment', this.proof_of_payment)

            this.courseProofOfPaymentAction(_form).then(() => {
                this.proofOfPaymentDialog = false
                this.alertMutation({
                    show: true,
                    text: 'Proof of payment uploading success!',
                    type: "success"
                })
                this.getData()
            }).catch(res => {
                if(this.errors.proof_of_payment) {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: this.errors.proof_of_payment[0]
                    }
                } else {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: res.message
                    }
                }
            }).finally(() => {
                this.proofOfPaymentLoading = false
            })
        },
    },
    computed: {
        ...mapState('instructor', {
            enrollees: (state) => state.enrollees
        }),

        ...mapState({
            errors: (state) => state.errors,
        }),

        itemsPerPage(){
            return Number(this.paginate)
        }
    },
    watch: {
        filter: {
            handler(val) {
                if(val.mode_of_payment === 'online') {
                    this.filter.type_of_payment = 'online_portal'
                }
            },
            deep: true
        },
        },
        beforeRouteEnter(to, from, next) {
            next(vm => {
                vm.prevRoute = from          
            })
        },
    }
</script>
