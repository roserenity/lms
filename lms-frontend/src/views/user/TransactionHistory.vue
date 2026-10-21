<template>
    <v-sheet class="custom-border border pa-4">
        <v-dialog v-model="dialog" persistent width="470">
            <v-card class="overflow-x-hidden">
                <div fluid class="primary-font fw600 f16 pa-5" >
                    <section class="primary-font primary--text fw600 f17">
                        Upload Proof of Payment
                    </section>
                    
                    <FormTextFieldRequired
                        :label="'PAYMENT REFERENCE NUMBER *'"
                        :value.sync="reference_number"
                        :error="errors && errors.reference_number"
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
                            v-if="loading4"
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
                        <v-btn text block class="poppins" @click="() => {
                            remove()
                            dialog = false
                        }">
                            Cancel
                        </v-btn>
                    </v-col>
                    <v-col>
                        <v-btn color="primary" block :disabled="!loading ? (!proof_of_payment && !reference_number) : true" class="poppins"
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

        <v-dialog v-model="deliveryOptionDialog" persistent width="500">
            <v-card class="overflow-x-hidden">
                <div fluid class="primary-font f16 pa-5" >
                    <section class="primary-font primary--text fw600 f17">
                        Request Official Receipt
                    </section>

                    <i class="f12">
                        <div class="mt-3">
                            Please note that Physical Copies of the Official Receipt will be delivered via our courier partner. Regardless of whether you choose Pick-Up or Delivery, you will be responsible for the corresponding courier/shipping fee.
                        </div>
                    </i>
                    
                    <div class="d-flex flex-row justify-center align-center mt-3 fw600">
                        <!-- <v-chip class="d-flex flex-column align-center pa-5 ma-1" outlined :color="deliveryOption === 'email' ? 'primary' : ''" @click="deliveryOption = 'email'">
                            <v-icon x-large class="mb-2 mr-1">
                                mdi-send-outline
                            </v-icon>
                            Soft Copy
                        </v-chip> -->
                        <v-chip class="d-flex flex-column align-center pa-5 ma-1" outlined :color="deliveryOption === 'pick_up' ? 'primary' : ''" @click="deliveryOption = 'pick_up'">
                            <v-icon x-large class="mb-2 mr-1">
                                mdi-package-variant-closed
                            </v-icon>
                            Pick Up
                        </v-chip>
                        <v-chip class="d-flex flex-column align-center pa-5 ma-1" outlined :color="deliveryOption === 'courier' ? 'primary' : ''" @click="deliveryOption = 'courier'">
                            <v-icon x-large class="mb-2 mr-1">
                                mdi-truck-fast-outline
                            </v-icon>
                            Courier
                        </v-chip>
                    </div>
                
                    <FormTextFieldRequired
                        class="mt-3"
                        v-if="deliveryOption"
                        :label="deliveryOption === 'pick_up' ? 'Please enter the exact address of your preferred courier branch for pick-up:' : (deliveryOption === 'courier' ? 'Please enter the exact delivery address where you would like the Official Receipt to be sent:' : 'Please enter a valid email address, as the Official Receipt will be sent to this email.')"
                        :value.sync="deliveryAddress"
                        :error="errors && errors.delivery_address"
                    />
                </div>
                
                <v-row dense class="pa-3">
                    <v-col>
                        <v-btn text block class="poppins" @click="() => {
                            deliveryOptionDialog = false
                            deliveryOption = null
                        }">
                            Cancel
                        </v-btn>
                    </v-col>
                    <v-col>
                        <v-btn color="primary" block class="poppins"
                            :disabled="!(deliveryOption && deliveryAddress)"
                            @click="updateDeliveryOption()" :loading="deliveryOptionLoading">
                            Submit
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card>
        </v-dialog>

        <v-dialog v-model="commitmentLetterDialog" persistent width="470">
            <v-card class="overflow-x-hidden">
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


        <v-dialog v-model="primeRequirementsDialog" persistent width="470">
            <v-card class="overflow-x-hidden">
                
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
        
        <section class="d-flex flex-wrap justify-space-between">
            <div class="poppins fw600 f18 secondary-1--text pa-3">Transaction History</div>
            <FilterMenu 
                :initialFilter="filter"
                @resetFilters="clearFilters()"
                @applyFilters="() => {page = 1, getTransactions()}">
                <section>
                    <FormSelectObject
                        v-if="tab === 0"
                        :label="'MODE OF PAYMENT'"
                        :items="mode_of_payment"
                        :value.sync="filter.mode_of_payment"
                        class="mb-3"
                    />

                    <FormSelectObject
                        v-if="tab === 0"
                        :label="'TYPE OF PAYMENT'"
                        :items="filter.mode_of_payment === '' ? [] : (filter.mode_of_payment === 'online' ? online_type_of_payment : offline_type_of_payment)"
                        :value.sync="filter.type_of_payment"
                        :disabled="filter.mode_of_payment == 'online'"
                        class="mb-3"
                    />

                    <FormSelectObject
                        v-if="tab === 0"
                        :label="'PAYMENT STATUS'"
                        :items="payment_status"
                        :value.sync="filter.invoice_status"
                        class="mb-3"
                    />

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
            
        </section>
        <v-tabs v-model="tab" show-arrows class="px-3 mt-2">
            <v-tab
                v-for="(item, i) in items"
                :key="i"
                class="text-capitalize poppins f14 fw600"
                active-class="primary--text"
            >
                <span>
                {{ item }}
                </span>
            </v-tab>
        </v-tabs>
        <v-data-table
            :headers="tab === 0 ? transaction_history_tbl_paid : transaction_history_tbl_free"
            class="poppins"
            :items="invoices"
            :loading="loading"
            hide-default-footer
            :items-per-page.sync="itemsPerPage">
            <template v-slot:item.mode_of_payment="{ item }" >
                <span class="text-uppercase">
                    {{ item.mode_of_payment }}
                </span>
            </template>
            <template v-slot:item.date="{ item }">
                {{  $dateFormat.dayDateTime(item.date) }}
            </template>
            <template v-slot:item.date_enrolled="{ item }">
                <div style="width: 200px">
                    {{  item.date_enrolled === '' ? '' : $dateFormat.dayDateTime(item.date_enrolled) }}
                </div>
            </template>
            <template v-slot:item.receipt="{ item }">
                <div class="d-flex flex-row">
                    <a
                        v-if="item.image && item.mode_of_payment === 'offline'"
                        :href="item.image.url"
                        class="primary--text text-decoration-none f12"
                        target="_blank"
                        >
                        <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                        View
                    </a>
                    <v-btn v-if="item.mode_of_payment === 'online' && item.status != 4" outlined color="secondary-1" dense class="pa-2 f12 ma-1" @click="requestOR(item)" small> Request OR </v-btn>
                    <v-btn v-if="item.mode_of_payment === 'online' && item.status != 4" outlined color="secondary-1" dense class="pa-2 f12 ma-1" @click="requestDelivery(item)" small> Request Delivery </v-btn>
            
                </div>
            </template>
            <template v-slot:item.status="{ item }">
                <span class="secondary-2--text px-2 border">
                    {{ payment_status.find((payment) => payment.value === item.status ).text }}
                </span>
            </template>
            <template v-slot:item.nomination_form="{ item }">
                <a
                    v-if="item.nomination_form && item.nomination_form.url !== ''"
                    :href="item.nomination_form.url"
                    class="primary--text text-decoration-none f12"
                    target="_blank"
                    >
                    <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                    View
                </a>
            </template>
            <template v-slot:item.receipt_pick_up_option="{ item }" >
                <span class="text-capitalize" v-if="item.receipt_pick_up_option">
                    {{ item.receipt_pick_up_option.replace('_', ' ') }}
                </span>
            </template>
            <template v-slot:item.type_of_payment="{ item }" >
                <span class="text-capitalize" v-if="item.type_of_payment !== 'null'">
                    {{ item.type_of_payment.replace('_', ' ') }}
                </span>
            </template>
            <template v-slot:item.type_of_participant="{ item }" >
                <span v-if="item.type_of_participant !== 'null'">
                    <span class="text-capitalize" v-if="item.type_of_participant && item.type_of_participant !== 'regular_payee_hrm'">
                        {{ item.type_of_participant.replaceAll('_', ' ').replace('hrm', 'HRM') }}
                    </span>
                    <span class="text-capitalize" v-else-if="item.type_of_participant && item.type_of_participant === 'regular_payee_hrm'">
                        REGULAR PAYEE (Partner Awardee)
                    </span>
                </span>
            </template>
            <template v-slot:item.reference_number="{ item }">
                <div v-if="item.proof_of_payment && item.proof_of_payment.reference_number && ![7].includes(item.status)">
                    {{ item.proof_of_payment.reference_number }}
                </div>
            </template>
            <template v-slot:item.proof_of_payment="{ item }">
                <a
                    v-if="item.proof_of_payment && item.proof_of_payment.url !== '' && ![7].includes(item.status)"
                    :href="item.proof_of_payment.url"
                    class="primary--text text-decoration-none f12"
                    target="_blank"
                    >
                    <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                    View
                </a>
                
                <v-btn class="ma-3 pa-2 f12" v-if="[2, 5].includes(item.status) && !item.proof_of_payment && !item.nomination_form && !item.type_of_payment !== 'online_portal'" outlined color="secondary-1" dense @click="() => { transaction = item, dialog = true }">
                    <v-icon class="mr-1" dense small>
                        mdi-upload-outline
                    </v-icon>
                    Upload Proof of Payment
                </v-btn>

                <v-btn class="ma-3 pa-2 f12" v-else-if="[7].includes(item.status) && item.type_of_payment !== 'online_portal'" outlined color="secondary-1" dense @click="() => { transaction = item, dialog = true }">
                    <v-icon class="mr-1" dense small>
                        mdi-upload-outline
                    </v-icon>
                    Upload Proof of Payment
                </v-btn>
                <!-- <v-btn class="ma-3 pa-2 f12" dense outlined :loading="loading2" v-else-if="item.status === 2 && item.nomination_form && !item.proof_of_payment" color="secondary-1" @click="() => { transaction = item, dialog = true }">
                    <v-icon class="mr-1" dense small>
                        mdi-upload-outline
                    </v-icon>
                    Upload Proof of Payment
                </v-btn> -->

            </template>
            <template v-slot:item.commitment_letter="{ item }">
                <a
                    v-if="item.commitment_letter && item.commitment_letter.url !== ''"
                    :href="item.commitment_letter.url"
                    class="primary--text text-decoration-none f12"
                    target="_blank"
                    >
                    <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                    View
                </a>
                <v-btn class="ma-3 pa-2 f12" dense outlined :loading="loading2" v-if="!item.commitment_letter && item.type_of_payment === 'commitment_letter' && !item.enroll_by_id" color="secondary-1" @click="() => { transaction = item, commitmentLetterDialog = true }">
                    <v-icon class="mr-1" dense small>
                        mdi-upload-outline
                    </v-icon>
                    Upload Commitment Letter
                </v-btn>
            </template>
            <template v-slot:item.prime_resolution="{ item }">
                <a
                    v-if="item.prime_resolution && item.prime_resolution.url !== ''"
                    :href="item.prime_resolution.url"
                    class="primary--text text-decoration-none f12"
                    target="_blank"
                    >
                    <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                    View
                </a>
                <v-btn class="ma-3 pa-2 f12" dense outlined :loading="loading2" v-if="!item.prime_resolution && item.type_of_participant === 'regular_payee_hrm'" color="secondary-1" @click="() => { transaction = item, primeRequirementsDialog = true }">
                    <v-icon class="mr-1" dense small>
                        mdi-upload-outline
                    </v-icon>
                    Upload Prime Requirements
                </v-btn>
            </template>
            <template v-slot:item.endorsement_letter="{ item }">
                <a
                    v-if="item.endorsement_letter && item.endorsement_letter.url !== ''"
                    :href="item.endorsement_letter.url"
                    class="primary--text text-decoration-none f12"
                    target="_blank"
                    >
                    <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                    View
                </a>
            </template>
            <template v-slot:item.action="{ item }">
                <v-btn block dense v-if="item.status === 1" color="success" text class="ma-3 f12" @click="$router.push({name: 'User Course Details', params: {id: item.uuid}})">
                    <v-icon class="mr-1" dense>
                        mdi-eye-outline
                    </v-icon>
                    View Course
                </v-btn>
                <div v-if="item.status === 2 && !item.nomination_form && item.mode_of_payment === 'online'" class="ma-3 f12 secondary-2--text" dense>
                    <v-icon class="mr-1" dense>
                        mdi-clock-outline
                    </v-icon>
                    <span>For Approval of Admin</span>
                </div>
                <div v-else-if="item.status === 2 && !item.nomination_form && item.mode_of_payment === 'offline' && item.commitment_letter" class="ma-3 f12 secondary-2--text" dense>
                    <div class="d-flex flex-row">
                        <v-icon class="mr-1" dense>
                            mdi-clock-outline
                        </v-icon>
                        <span>For Approval of Admin</span>
                    </div>
                </div>
                <div v-else-if="item.status === 2 && !item.nomination_form && !item.commitment_letter && item.mode_of_payment === 'offline'" class="ma-3 f12 secondary-2--text" dense>
                    <div class="d-flex flex-row">
                        <v-icon class="mr-1" dense>
                            mdi-clock-outline
                        </v-icon>
                        <span>For Approval of Admin</span>
                    </div>
                </div>
                <div v-else-if="item.status === 2 && item.nomination_form && item.proof_of_payment && item.mode_of_payment === 'offline'" class="ma-3 f12 secondary-2--text" dense>
                    <div class="d-flex flex-row">
                        <v-icon class="mr-1" dense>
                            mdi-clock-outline
                        </v-icon>
                        <span>For Approval of Admin</span>
                    </div>
                </div>
                <div v-else-if="item.status === 2 && item.nomination_form && item.proof_of_payment && item.mode_of_payment === 'online'" class="ma-3 f12 secondary-2--text" dense>
                    <div class="d-flex flex-row">
                        <v-icon class="mr-1" dense>
                            mdi-clock-outline
                        </v-icon>
                        <span>For Approval of Admin</span>
                    </div>
                </div>
                <div v-else-if="item.status === 5 && item.proof_of_payment && item.for_verification === 1" class="ma-3 f12 secondary-2--text" dense>
                    <div class="d-flex flex-row">
                        <v-icon class="mr-1" dense>
                            mdi-clock-outline
                        </v-icon>
                        <span>For Approval of Admin</span>
                    </div>
                </div>
            </template>
            <template v-slot:item.cancel="{ item }">
                <v-btn icon dense text v-if="item.status === 2 || item.status === 5" class="f12 error--text" @click="() => { cancelDialog = true, selected = item }">
                    <v-icon color="error" small dense>
                        mdi-close
                    </v-icon>
                </v-btn>
            </template>
        </v-data-table>

        <FormPagination 
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginate"
            @page="(e) => { page = e, getTransactions()}" 
            @paginate="(e) => { paginate = e, page = 1, getTransactions()}"/>

        <CancelTransactionDialog 
            :dialog="cancelDialog"
            :item="selected"
            @close="() => { cancelDialog = false, getTransactions() }"
        />
        
    </v-sheet>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex'
import { transaction_history_tbl_paid, transaction_history_tbl_free } from '@/constants/tblheaders/transactionhistory';
import { mode_of_payment, offline_type_of_payment, online_type_of_payment, payment_status } from '@/constants/payment'
import CancelTransactionDialog from '@/components/user/course/tocart/CancelTransactionDialog.vue';
import UploadCommitmentLetter from '@/components/user/course/tocart/UploadCommitmentLetter.vue';
import UploadPrimeRequirements from '@/components/user/course/tocart/UploadPrimeRequirements.vue';

export default {
    components: {
        CancelTransactionDialog,
        UploadCommitmentLetter,
        UploadPrimeRequirements
    },
    data: () => ({
        items: ['PAID', 'FREE'],
        transaction_history_tbl_paid,
        transaction_history_tbl_free,
        loading: false,
        loading1: false,
        loading2: false,
        loading3: false,
        loading4: false,
        submitLoading: false,
        dialog: false,
        commitmentLetterDialog: false,
        primeRequirementsDialog: false,
        transaction: null,
        commitment_letter: null,
        prime_resolution: null,
        endorsement_letter: null,
        proof_of_payment: null,
        reference_number: null,
        pageCount: 1,
        page: 1,
        paginate: '10',
        tab: 0,
        msg: {
            show: false,
            type: '',
            text: ''
        },
        selected: null,
        cancelDialog: false,
        filter: {
            date_from: '',
            date_to: '',
            mode_of_payment: '',
            type_of_payment: '',
            invoice_status: '',
        },
        minDate: '',
        maxDate: '',
        totalCount: 0,
        mode_of_payment,
        offline_type_of_payment,
        online_type_of_payment,
        payment_status,
        commitmentLetterLoading: false,
        primeRequirementsLoading: false,
        deliveryOptionLoading: false,
        deliveryOptionDialog: false,
        deliveryOption: null,
        deliveryAddress: null
    }),
    methods: {
        ...mapMutations(['alertMutation']),
        ...mapActions('usr', ['getTransactionHistory', 'addToCart', 'uploadProofOfPayment', 'courseProofOfPaymentAction', 'requestOfficialReceipt', 'courseCommitmentLetterAction', 'coursePrimeRequirementsAction', 'updateReceiptDeliveryOptionAction']),

        clearFilters(){
            this.filter = {
                date_from: '',
                date_from: '',
                date_to: '',
                mode_of_payment: '',
                type_of_payment: '',
                invoice_status: '',
            }

            this.minDate = ''
            this.maxDate = ''
        },

        addToList(id) {
            this.loading1 = true
            this.addToCart({course_id: [id]}).then(() => {
                this.loading1 = false
                this.alertMutation({
                    show: true,
                    text: 'Added to List!',
                    type: "success"
                })
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

        submitProofOfPayment() {

            this.submitLoading = true

            this.msg = {
                show: false,
                type: '',
                text: ''
            }

            let _form = new FormData()

            _form.append('invoice_id', this.transaction.id)
            _form.append('proof_of_payment', this.proof_of_payment)
            if(this.reference_number) {
                _form.append('reference_number', this.reference_number)
            }

            this.courseProofOfPaymentAction(_form).then(() => {
                this.dialog = false
                this.alertMutation({
                    show: true,
                    text: 'Proof of payment uploading success!',
                    type: "success"
                })
                this.getTransactions()
                this.remove()
            }).catch(res => {
                if(this.errors.proof_of_payment) {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: this.errors.proof_of_payment[0]
                    }
                }
            }).finally(() => {
                this.submitLoading = false
            })
        },

        accept() {
            this.msg = {
                show: false,
                type: '',
                text: ''
            }

            this.proof_of_payment = this.$refs.file.files[0]
            this.loading4 = true;
            setTimeout(() => {
                this.loading4 = false;
            }, 3000);
            
            this.$refs.file.value = ''            
        },
        

        submitFile(e) {
            this.loading3 = true

            let _formData = new FormData();

            _formData.append("invoice_id", this.transaction.nomination_form.invoice_id);
            _formData.append("course_id", this.transaction.course_id);
            _formData.append("official_receipt_no", this.transaction.official_receipt_no);
            _formData.append("_method", "PUT");

            if(e) {
                _formData.append("image", e);
            }

            this.uploadProofOfPayment({invoice_id: this.transaction.nomination_form.invoice_id, form: _formData}).then(() => {
                this.loading3 = false
                this.transaction = null
                this.dialog = false
                this.alertMutation({
                    show: true,
                    text: 'Proof of payment successfully uploaded.',
                    type: "success"
                })
                this.getTransactions()
            }).catch(e => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in uploading.',
                    type: "error"
                })
                this.loading3 = false
            })
        },

        getTransactions(){
            this.loading = true
            let _filter = {}
            if(this.tab === 0) {
                _filter = this.filter
            } else {
                _filter = {
                    date_to: this.filter.date_to,
                    date_from: this.filter.date_from
                }
            }
            this.getTransactionHistory({ 
                page: this.page, 
                paginate: Number(this.paginate), 
                is_free: this.tab,
                ..._filter
            }).then((res) => {
                this.page = res.current_page
                this.paginate = String(res.per_page)
                this.pageCount = res.last_page
                this.totalCount = res.total
            }).finally(() => {
                this.loading = false
            })
        },

        async requestDelivery(item) {
            this.deliveryOptionDialog = true
            this.invoiceIDForOR = item.id
            this.deliveryOption = null
        },

        updateDeliveryOption(){
            this.deliveryOptionLoading = true
            this.updateReceiptDeliveryOptionAction({ invoice_id: this.invoiceIDForOR, delivery_option: this.deliveryOption, delivery_address: this.deliveryAddress })
                .then(() => {
                    this.deliveryOptionDialog = false
                    this.alertMutation({
                        show: true,
                        text: 'Receipt Delivery method updated successfully!',
                        type: "success"
                    })
                    this.getTransactions()
                }).catch(() => {
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                }).finally(() => {
                    this.deliveryOptionLoading = false
                })
        },

        async requestOR(item) {
            this.requestORLoading = true
            this.invoiceIDForOR = item.invoice_id
            this.requestOfficialReceipt({ 
                course_uuid: item.course_uuid,
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

            _form.append('invoice_id', this.transaction.id)
            _form.append('commitment_letter', this.commitment_letter)

            this.courseCommitmentLetterAction(_form).then(() => {
                this.commitmentLetterDialog = false
                this.alertMutation({
                    show: true,
                    text: 'Commitment Letter uploading success!',
                    type: "success"
                })
                this.getTransactions()
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

            _form.append('invoice_id', this.transaction.id)
            _form.append('prime_resolution', this.prime_resolution)
            _form.append('endorsement_letter', this.endorsement_letter)

            this.coursePrimeRequirementsAction(_form).then(() => {
                this.primeRequirementsDialog = false
                this.alertMutation({
                    show: true,
                    text: 'Prime Requiremenets uploading success!',
                    type: "success"
                })
                this.getTransactions()
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
        
    },

    mounted() {
        this.getTransactions()
    },
    computed: {

        ...mapState('usr', {
            invoices: (state) => state.transaction_history
        }),

        itemsPerPage(){
            return Number(this.paginate)
        },

        ...mapState({
            errors: (state) => state.errors,
        }),

        deliveryOptionDialog(){
            this.deliveryAddress = null
            this.deliveryOption = null
        }

    },
    watch: {
        tab(val) {
            this.getTransactions();
        },
        
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