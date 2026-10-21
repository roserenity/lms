<template>
    <section>
        <v-dialog v-model="proofOfPaymentDialog" persistent width="370">
            <v-card width="370" class="overflow-x-hidden">
                <div fluid class="primary-font fw600 f16 pa-5" >
                    <section class="primary-font primary--text fw600 f17">
                        Upload Proof of Payment
                    </section>
                    
                    <FormTextFieldRequired
                        :label="'PAYMENT REFERENCE NUMBER *'"
                        :value.sync="reference_number"
                    />
                    <v-container
                        class="bulk-border mt-2 d-flex flex-column justify-center"
                        @dragover="dragover"
                        @dragleave="dragleave"
                        @drop="drop"
                    >
                        <div class="d-flex flex-column py-3">
                            <v-icon color="rgba(63, 93, 208, 0.12)" size="68"
                                >mdi-cloud-upload-outline</v-icon
                            >
                            <div class="secondary--text f12 poppins text-center">
                                <a
                                href="#"
                                @click="$refs.file.click()"
                                class="primary--text text-decoration-none"
                                >
                                Browse
                                </a>
                                or drag file here 
                                <div>
                                    ( .pdf, .docx, .doc, .png, .jpg, .jpeg )
                                </div>
                            </div>
                        </div>
                    </v-container>
                    <alert 
                        v-if="msg.show"
                        :show = "msg.show"
                        :text = "msg.text"
                        :type = "msg.type"
                    />
                    <v-container
                        v-if="proof_of_payment"
                        class="d-flex justify-space-between align-center">
                        <div >
                            <div class="roboto fw600 f12" style="margin-bottom: -8px">
                                {{ proof_of_payment.name }}
                            </div>
                            <span class="roboto fw500 f10 secondary--text">
                                {{ (proof_of_payment.size / 1000).toFixed(2) }} KB
                            </span>
                        </div>
                        <v-spacer />
                        <v-progress-linear
                            v-if="loading2"
                            indeterminate
                            color="success"
                            height="7"
                            style="width: 120px"
                            rounded
                        />
                        <v-btn icon small @click="remove()">
                            <v-icon size="18" color="danger-1">mdi-close</v-icon>
                        </v-btn>
                    </v-container>
                </div>
                
                <v-row dense class="pa-3">
                    <v-col>
                        <v-btn text block class="poppins" :disabled="loading2" @click="() => {
                            remove()
                            proofOfPaymentDialog = false
                        }">
                            Cancel
                        </v-btn>
                    </v-col>
                    <v-col>
                        <v-btn color="primary" block :disabled="!loading2 ? (!proof_of_payment || !reference_number) : true" class="poppins"
                            @click="submitProofOfPayment()" :loading="submitLoading">
                            Submit
                        </v-btn>
                    </v-col>
                </v-row>

                <input
                    type="file"
                    ref="file"
                    class="d-none"
                    accept=".pdf,.docx,.doc,.png,.jpg,.jpeg"
                    @change="accept"
                />
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
                @click="(prevRoute && prevRoute.name === 'Admin Course Enrollment') ? $router.back() : $router.push({ name: 'Admin Course Enrollment' , query: { search: '', page: 1, paginate: 10, role: '', status: '', created_at: '' }})"  class="mr-1 pa-0 f14">
                <v-icon small>
                    mdi-chevron-left
                </v-icon>
                BACK
            </v-btn>
            <span class="f14">/</span> <span class="fw600 primary--text f14 ml-2">{{ title  }}</span>
        </section>

        <section class="d-flex flex-wrap justify-space-between">
            <SearchBar 
                :placeholder="'Search student'" 
                :value.sync="search"
                @clear="clearSearch"
                @search="onSearch"
                class="mt-3 mb-5"/>
            <section class="d-flex flex-row ml-auto">
                <FilterMenu
                        :initialFilter="filter"
                        @resetFilters="clearFilters()"
                        @applyFilters="() => { page = 1, updateQuery() }"
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
                            :items="filterStatus"
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
                            :items="filterEnrollmentStatus"
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
                            class_="col-12"
                            @apply="(e) => {
                                filter.date_from = e
                                minDate = e
                            }"/>

                        <label class="caption">TO:</label>
                        <FormDatePicker 
                            :val="filter.date_to"
                            :min="minDate"
                            class_="col-12"
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
            :headers="courseStudentsEnrollment"
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
                <!-- , {{ $dateFormat.hhMM(item.date_enrolled) }} -->
            </template>
            <template v-slot:item.date_approved="{ item }" >
                {{ $dateFormat.ymd(item.date_approved) }}
            </template>
            <template v-slot:item.official_receipt="{ item }" >
                <v-btn 
                    v-if="item.mode_of_payment === 'online' && item.invoice_status !== 4" outlined 
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
            <template v-slot:item.check="{ item }">
                <v-btn v-if="item.mode_of_payment === 'online' && item.invoice_status !== 4" outlined color="primary" dense class="pa-2 f10" 
                    @click="checkPaymentStatus(item)"
                    :loading="checkLoading && invoiceIDToCheck === item.invoice_id"
                    small>Verify</v-btn>
            </template>
            <template v-slot:item.payment_status_action="{ item }">
                <v-select
                    outlined
                    :items="actionItemsCanCancel"
                    item-text="text"
                    item-value="value"
                    class="no-outline-custom-field poppins f14 ma-auto"
                    dense
                    :disabled="item.invoice_status === 1"
                    v-model="item.invoice_status"
                    v-on:change="updateStatus(item)"
                    style="width:150px;"
                    hide-details
                >
                </v-select>
            </template>
            <template v-slot:item.enrollment_status_action="{ item }">
                <div class="d-flex flex-row align-center" v-if="![3,7,4].includes(item.invoice_status)">
                    <v-select
                        outlined
                        :items="actionItemsEnrollment"
                        item-text="text"
                        item-value="value"
                        width="100"
                        class="no-outline-custom-field poppins f14 ma-auto"
                        dense
                        v-model="item.enrollment_status"
                        v-on:change="updateEnrollmentStatus(item)"
                        :style="'width: min-content;'"
                        hide-details
                    >
                        <template slot="selection"  slot-scope="data">
                            <span dense class=" px-2 border" v-if="item.enrollment_status === 1" style="background-color: #7BC14533">ENROLLED</span>
                            <span dense class=" px-2 border text-no-wrap" v-if="item.enrollment_status === 2" style="background-color: #BDBDBD33">NOT ENROLLED</span>
                        </template>
                    </v-select>
                    <v-progress-circular
                        v-if="selectedInvoice === item.invoice_id && enrollmentLoading"
                        size="20"
                        indeterminate
                        color="primary"
                        ></v-progress-circular>
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
            <template v-slot:item.receipt_pick_up_option="{ item }" >
                <span class="text-capitalize" v-if="item.receipt_pick_up_option">
                    {{ item.receipt_pick_up_option.replace('_', ' ') }}
                </span>
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
                <div v-if="item.enrolled_by_id">
                    <v-select
                        outlined
                        :items="type_of_participant"
                        item-text="text"
                        item-value="value"
                        class="no-outline-custom-field poppins f14 ma-auto"
                        dense
                        width="150"
                        v-model="item.type_of_participant"
                        v-on:change="updateTypeOfParticipant(item)"
                        :style="item.type_of_participant === '' ? 'width: min-content;' : ''"
                        hide-details
                    >
                    </v-select>
                </div>
                <div v-else-if="item.type_of_participant !== 'null'">
                    <span class="text-uppercase" v-if="item.type_of_participant && item.type_of_participant !== 'regular_payee_hrm'">
                        {{ item.type_of_participant.replaceAll('_', ' ').replace('hrm', 'HRM') }}
                    </span>
                    <span class="text-uppercase" v-else-if="item.type_of_participant && item.type_of_participant === 'regular_payee_hrm'">
                        Regular Payee (w/ Partner Discount)
                    </span>
                </div>
            </template>
            <template v-slot:item.type_of_payment="{ item }" >
                <v-select
                    v-if="item.enrolled_by_id"
                    outlined
                    :items="offline_type_of_payment"
                    item-text="text"
                    item-value="value"
                    class="no-outline-custom-field poppins f14 ma-auto"
                    dense
                    width="150"
                    v-model="item.type_of_payment"
                    v-on:change="updateTypeOfPayment(item)"
                    :style="item.type_of_payment === '' ? 'width: min-content;' : ''"
                    hide-details
                ></v-select>
                <v-select
                    v-else-if="item.invoice_status == 2 && item.mode_of_payment === 'offline'"
                    outlined
                    :items="offline_type_of_payment"
                    item-text="text"
                    item-value="value"
                    class="no-outline-custom-field poppins f14 ma-auto"
                    dense
                    width="150"
                    v-model="item.type_of_payment"
                    v-on:change="updateTypeOfPayment(item)"
                    :style="item.type_of_payment === '' ? 'width: min-content;' : ''"
                    hide-details
                >
                </v-select>
                <span v-else class="text-uppercase">
                    {{ item.type_of_payment ? item.type_of_payment.replaceAll('_', ' ') : '' }}
                </span>
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
                <v-btn class="ma-3 pa-2 f12" v-if="[2, 5].includes(item.invoice_status) && !item.proof_of_payment && item.type_of_payment !== 'online_portal'" outlined color="secondary-1" dense @click="() => { transaction = item, proofOfPaymentDialog = true }">
                    <v-icon class="mr-1" dense small>
                        mdi-upload-outline
                    </v-icon>
                    Upload Proof of Payment
                </v-btn>
                <!-- <v-btn class="ma-3 pa-2 f12" v-if="item.invoice_status === 2 && !item.proof_of_payment && item.type_of_payment === 'commitment_letter'" outlined color="secondary-1" dense @click="() => { transaction = item, proofOfPaymentDialog = true }">
                    <v-icon class="mr-1" dense small>
                        mdi-upload-outline
                    </v-icon>
                    Upload Proof of Payment
                </v-btn> -->
                <!-- <v-btn v-if="item.proof_of_payment" dense color="secondary-1" text class="f12" small @click="selected= item.proof_of_payment, dialog = true">
                    <v-icon small dense class="mr-1">
                        mdi-eye-outline
                    </v-icon>
                    View
                </v-btn>
                <span v-else>-</span> -->
            </template>
        </v-data-table>

        <FormPagination 
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginationPaginate"
            @page="onPageChange" 
            @paginate="onPaginateChange"/>

        <DialogBox 
            :dialog="confirmationDialog"
            @close="() => { confirmationDialog = false, toApprove = false }"
            :title="'Approve user ?'"
            :subtext="'This action cannot be undone.'"
            :btn1="'Cancel'"
            :btn2="'Approve'"
            @btn_1="() => {
                confirmationDialog = false,
                getData()
            }"
            @btn_2="approveStatus"
            :loading_btn2="statusLoading"
        />
    </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
import { courseStudentsEnrollment } from '../../../constants/tblheaders/enrollment';
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import UploadCommitmentLetter from '@/components/user/course/tocart/UploadCommitmentLetter.vue';
import UploadPrimeRequirements from '@/components/user/course/tocart/UploadPrimeRequirements.vue';
import { delivery_option } from '@/constants/payment';

export default {
    components: { UploadCommitmentLetter, UploadPrimeRequirements },
    mixins: [searchAndPaginateMixin],
    data: () => ({
        selectedInvoice: null,
        totalCount: 0,
        pageCount: 1,
        loading: false,
        enrollmentLoading: false,
        exportLoading: false,
        statusLoading: false,
        requestORLoading: false,
        checkLoading: false,
        courseStudentsEnrollment,
        confirmationDialog: false,
        actionItemsCanCancel: [
            { text: 'FREE', value: 6 },
            { text: 'PENDING', value: 2 },
            { text: 'APPROVED', value: 1 },
            { text: 'COMMITMENT LETTER', value: 5 },
            { text: 'DISAPPROVED (INVALID POP)', value: 7 },
            { text: 'CANCELLED', value: 4 },
            { text: 'DISQUALIFIED', value: 3 },
        ],
        actionItemsEnrollment: [
            { text: 'ENROLL', value: 1 },
            { text: 'UNENROLL', value: 2 },
        ],
        filterStatus: [
            { text: 'All', value: '' },
            { text: 'PENDING', value: '2' },
            { text: 'FREE', value: '6' },
            { text: 'APPROVED', value: '1' },
            { text: 'COMMITMENT LETTER', value: '5' },
            { text: 'DISAPPROVED (INVALID POP)', value: '7' },
            { text: 'CANCELLED', value: '4' },
            { text: 'DISQUALIFIED', value: '3' },
        ],
        filterEnrollmentStatus: [
            { text: 'All', value: '' },
            { text: 'ENROLLED', value: '1' },
            { text: 'NOT ENROLLED', value: '2' },
        ],
        item: null,
        prevItem: null,
        selected: null,
        toApprove: false,
        dialog: false,
        title: '',
        mode_of_payment: [
            { text: 'All', value: '' },
            { text: 'OFFLINE', value: 'offline' },
            { text: 'ONLINE', value: 'online' },
        ],
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
        offline_type_of_payment: [
            { text: '-', value: '' },
            { text: 'CASH DEPOSIT', value: 'cash_deposit' },
            // { text: 'CASH-CASHIER', value: 'cash-cashier' },
            { text: 'CHECK DEPOSIT', value: 'check_deposit' },
            // { text: 'CHECK-CASHIER', value: 'check-cashier' },
            { text: 'DEBIT MEMO', value: 'debit_memo' },
            // { text: 'LDDAP-ADA', value: 'lddap_pada' },
            { text: 'FUND TRANSFER', value: 'fund_transfer' },
            { text: 'COMMITMENT LETTER (CL)', value: 'commitment_letter' },
            // { text: 'LDDAP-ADA', value: 'lddap_ada' },
        ],
        online_type_of_payment: [
            { text: '-', value: '' },
            // { text: 'ONLINE PAYMENT PORTAL', value: 'online_portal' },
        ],    
        type_of_participant: [
            { text: '-', value: '' },
            { text: 'INTERNAL EMPLOYEE', value: 'internal_employee' },
            { text: 'REGULAR PAYEE', value: 'regular_payee' },
            { text: 'REGULAR PAYEE (Partner Awardee)', value: 'regular_payee_hrm' },
        ],
        invoiceIDToCheck: null,
        invoiceIDForOR: null,
        prevRoute: null,
        minDate: '',
        maxDate: '',
        confirmationDialogChange: false,
        commitmentLetterDialog: false,
        commitmentLetterLoading: false,
        primeRequirementsDialog: false,
        primeRequirementsLoading: false,
        proofOfPaymentDialog: false,
        proofOfPaymentLoading: false,
        loading2: false,
        proof_of_payment: null,
        transaction: null,
        msg: {
            show: false,
            type: '',
            text: ''
        },
        commitment_letter: null,
        prime_resolution: null,
        endorsement_letter: null,
        delivery_option,
        reference_number: null
    }),
    methods: {
        ...mapActions('admin', ['courseProofOfPaymentAction', 'getCourseEnrolleesAction', 'updateInvoiceStatus', 'requestOfficialReceipt', 'paymentStatusCheckAction', 'updateTypeOfPaymentAction', 'paymentExportAction', 'unenrollUserAction', 'enrollUserAction', 'updateTypeOfParticipantAction', 'courseCommitmentLetterAction', 'coursePrimeRequirementsAction', 'coursePrimeRequirementsAction']),
        ...mapMutations(['alertMutation']),
        ...mapMutations('admin', ['enrolleesMutation']),

        clearFilters(){
            this.filter = {
                date_from: '',
                date_to: '',
                enrolled_by: '',
                mode_of_payment: '',
                type_of_payment: '',
                invoice_status: '',
                enrollment_status: '',
                receipt_pick_up_option: ""
            }

            this.updateQuery()

            this.minDate = ''
            this.maxDate = ''
        },

        approve() {
            this.toApprove = true
            this.updateStatus(this.item)
        },

        errorMessage(){
            this.alertMutation({
                show: true,
                text: "Something went wrong",
                type: "error"
            })
        },

        getData(){
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
                }).catch(() => {
                    this.errorMessage()
                }).finally(() => {
                    this.loading = false
                })
            }
        },

        updateTypeOfPayment(item) {
            this.loading = true
            this.updateTypeOfPaymentAction({invoice_id: item.invoice_id, type_of_payment: item.type_of_payment}).then(() => {
                this.getData()
            }).finally(() => {
                this.loading = false
            })
        },

        updateTypeOfParticipant(item) {
            this.loading = true
            this.updateTypeOfParticipantAction({invoice_id: item.invoice_id, type_of_participant: item.type_of_participant}).then(() => {
                this.getData()
            }).finally(() => {
                this.loading = false
            })
        },

        updateEnrollmentStatus(item) {
            this.selectedInvoice = item.invoice_id
            if(item.enrollment_status === 2) {

                this.enrollmentLoading = true
                this.unenrollUserAction({
                    id: item.enrollment_id,
                    user_id: item.user_id,
                    course_id: item.course_id
                }).then(() => {
                    this.enrollmentLoading = false
                    this.alertMutation({
                        show: true,
                        text: "Learner successfully unenrolled",
                        type: "success"
                    })
                    this.getData()
                }).catch(() => {
                    this.enrollmentLoading = false
                    this.alertMutation({
                        show: true,
                        text: "Something went wrong in unenrolling",
                        type: "error"
                    })
                })
            } else {
                this.enrollmentLoading = true
                this.enrollUserAction({
                    user_id: item.user_id,
                    course_id: item.course_id
                }).then(() => {
                    this.enrollmentLoading = false
                    this.alertMutation({
                        show: true,
                        text: "Learner successfully enrolled",
                        type: "success"
                    })
                    this.getData()
                }).catch(() => {
                    this.enrollmentLoading = false
                    this.alertMutation({
                        show: true,
                        text: "Something went wrong in enrolling",
                        type: "error"
                    })
                })

            }
        },

        approveStatus(){
            if(this.toApprove) {
                this.statusLoading = true
                this.updateInvoiceStatus({invoice_id: this.item.invoice_id, status: 1}).then(() => {
                    this.enrolleesMutation([])
                    this.getData()
                    this.alertMutation({
                        show: true,
                        text: "Successfully updated!",
                        type: "success"
                    })
                }).catch(e => {
                    this.statusLoading = false
                    this.errorMessage()
                }).finally(() => {
                    this.statusLoading = false
                    this.toApprove = false
                    this.confirmationDialog = false
                })
            } else {
                this.statusLoading = true
                this.updateInvoiceStatus({invoice_id: this.item.invoice_id, status: this.item.invoice_status}).then(() => {
                    this.getData()
                    this.alertMutation({
                        show: true,
                        text: "Successfully updated!",
                        type: "success"
                    })
                    this.statusLoading = false
                    this.item = null
                    this.confirmationDialog = false
                }).catch(e => {
                    console.log(e)
                    this.item = null
                    this.confirmationDialog = false
                    this.toApprove = false
                    this.statusLoading = false
                    this.errorMessage()
                })
            }
        },

        updateStatus(item){
            this.item = item
            if(item.invoice_status === 1) {
                this.confirmationDialog = true
            } else if(!this.toApprove) {
                this.statusLoading = true
                this.updateInvoiceStatus({invoice_id: this.item.invoice_id, status: this.item.invoice_status}).then(() => {
                    this.getData()
                    this.alertMutation({
                        show: true,
                        text: "Successfully updated!",
                        type: "success"
                    })
                    this.statusLoading = false
                    this.item = null
                    this.confirmationDialog = false
                }).catch(e => {
                    console.log(e)
                    this.item = null
                    this.confirmationDialog = false
                    this.statusLoading = false
                    this.errorMessage()
                })
            }
            
        },

        requestOR(item) {
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

        checkPaymentStatus(item) {
            this.checkLoading = true
            this.invoiceIDToCheck = item.invoice_id
            this.paymentStatusCheckAction({ email: item.email, ref_id: item.reference_id, amount: item.amount}).then( _status => {
                if(_status === 1) {
                    this.getData()
                }
            }).finally(() => {
                this.checkLoading = false
                this.invoiceIDToCheck = null
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

            this.submitLoading = true

            this.msg = {
                show: false,
                type: '',
                text: ''
            }

            let _form = new FormData()

            _form.append('invoice_id', this.transaction.invoice_id)
            _form.append('proof_of_payment', this.proof_of_payment)
            _form.append('reference_number', this.reference_number)

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
                } else if(this.errors.reference_number) {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: this.errors.reference_number[0]
                    }
                } else {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: res.message
                    }
                }
            }).finally(() => {
                this.submitLoading = false
            })
        },

        remove(){
            this.transaction = null
            this.proof_of_payment = null
            this.commitment_letter = null
            this.prime_resolution = null
            this.endorsement_letter = null
            this.reference_number = null
            if(this.$refs.file) {
                this.$refs.file.value = ''
            }
        },

        dragover(e) {
            e.preventDefault();
            e.currentTarget.classList.add("bulk-border-drag");
        },

        dragleave(e) {
            e.preventDefault();
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        drop(e) {
            e.preventDefault();
            this.$refs.file.files = e.dataTransfer.files;
            this.accept();
            e.currentTarget.classList.remove("bulk-border-drag");
            // console.log(this.$refs.file.files);
        },
        
        accept() {
            this.msg = {
                show: false,
                type: '',
                text: ''
            }

            this.proof_of_payment = this.$refs.file.files[0]
            this.loading2 = true;
            setTimeout(() => {
                this.loading2 = false;
            }, 3000);
            
            this.$refs.file.value = ''            
        },
    },
    computed: {
        ...mapState('admin', {
            enrollees: (state) => state.enrollees
        }),

        ...mapState({
            errors: (state) => state.errors,
        }),

        itemsPerPage(){
            return Number(this.paginate)
        },

        paginationPaginate(){
            return String(this.paginate)
        }
    },
    mounted() {
        // 1 approved
        // 2 pending
        // 3 failed
        this.enrolleesMutation([])
        this.getData()
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
    }
}
</script>
