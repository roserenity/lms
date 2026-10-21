<template>
    <section>
        <v-dialog v-model="dialog" persistent width="auto">
            <v-card width="600" class="overflow-x-hidden ">
                <div class="pa-5" v-if="paymentNotice">
                    <section class="d-flex flex-row justify-space-between mb-5">
                        <section class="primary-font primary--text fw600 f17">
                            Important Notice
                        </section>
                        <section class="text-right">
                            <v-btn icon @click="() => { reset(), $emit('close') }" dense>
                                <v-icon small>
                                    mdi-close
                                </v-icon>
                            </v-btn>
                        </section>
                    </section>
                    <div class="f15">
                        <div>
                           Please be informed that all participants enrolling in Aster Academy training programs are required to pay the corresponding Training Fees through the online payment portal except on the following modes of payments:
                        </div>
                        <div class="ml-2">
                            - LDDAP-ADA
                        </div>
                        <div class="ml-2">
                            - Check Deposit
                        </div>
                        <div class="ml-2">
                            - Advice of Checks Issued and Cancelled
                        </div>
                        <div class="ml-2">
                            - Debit/Credit Advice
                        </div>

                        <div class="mt-2">
                            All participants are therefore encouraged to familiarize themselves with payment processing through the online payment portal.
                        </div>
                        
                        <div class="mt-2">
                            For further details on the payment process, please refer to our <a href="#" target="_blank"><b>PAYMENT REMINDERS</b></a>.
                        </div>
                        <div class="mt-2">
                            Thank you
                        </div>
                    </div>
                    <div class="d-flex align-center justify-center mb-2">
                        <ButtonPrimary class="mt-3 mx-auto" :label="'Proceed'" @click="() => { registrationReminder = true, paymentNotice = false}"/>
                    </div>
                </div>
                <div class="pa-5" v-if="termsAndCondition">
                    <section class="d-flex flex-row justify-space-between mb-5">
                        <section class="primary-font primary--text fw600 f17">
                            Terms and Conditions
                        </section>
                        <section class="text-right">
                            <v-btn icon @click="() => { reset(), $emit('close') }" dense>
                                <v-icon small>
                                    mdi-close
                                </v-icon>
                            </v-btn>
                        </section>
                    </section>
                    <div class="f15 mt-2 ">
                        <div>
                            I hereby acknowledge that compliance with the <a href="#" target="_blank"><b>PAYMENT REMINDERS</b></a> is a mandatory requirement.
                        </div>
                        <div class="mt-2">
                            I understand that all submissions will undergo validation by the Collection Unit of Aster Academy prior to endorsement to the Cash Unit. Aster Academy reserves the right to contact me for clarifications, if necessary.
                        </div>
                        <div class="mt-2">
                            I recognize that non-compliance, including failure to provide valid Proof of Payment (POP), will result in the non-processing or non-recognition of my payment.
                        </div>
                        <div class="mt-2">
                            Furthermore, I acknowledge that all submitted information will be handled in strict compliance with applicable data privacy laws and used solely for verification and processing purposes.
                        </div>
                        <div class="my-3 d-flex flex-row">
                            <v-checkbox v-model="proceed" dense></v-checkbox>
                            <b><i>By proceeding, I confirm that I have read, understood, and accepted these terms and conditions and undertake to comply with the PAYMENT REMINDERS accordingly.</i></b>
                        </div>
                    </div>
                </div>
                <div class="pa-5" v-if="registrationReminder">
                    <section class="d-flex flex-row justify-space-between mb-5">
                        <section class="primary-font primary--text fw600 f17">
                            Registration Reminder
                        </section>
                        <section class="text-right">
                            <v-btn icon @click="() => { reset(), $emit('close') }" dense>
                                <v-icon small>
                                    mdi-close
                                </v-icon>
                            </v-btn>
                        </section>
                    </section>
                    <div class="f15">
                        Participants must agree to the following terms and conditions to complete the registration process.
                        <div class="mt-2 d-flex flex-row">
                            <div class="mr-1">1.</div>
                            Certificates of Completion, including credit hours in Leadership and Management, will be awarded only to participants who attend all sessions and fulfill all training requirements.
                        </div>
                        <div class="mt-1 d-flex flex-row">
                            <div class="mr-1">2.</div>
                            <div>Confirmed and paid participants who fail to attend the course without prior notice will forfeit their registration fee. Participants unable to attend must notify the Training Team by submitting a formal notice signed by the Head of Office or an Authorized Representative. This notice must be submitted at least <b>five (5) working days</b> before the course date.</div>
                        </div>
                        <div class="mt-1 d-flex flex-row">
                            <div class="mr-1">3.</div>
                            Paid registration fees may be applied to future programs instead of requesting a refund. If the new program fee exceeds the original fee, the balance must be settled through the specified payment methods.
                        </div>
                        <div class="mt-1 d-flex flex-row">
                            <div class="mr-1">4.</div>
                            By registering, participants agree to adhere to all course guidelines, policies, and timelines established by the Training Team.
                        </div>
                        <div class="mt-1 d-flex flex-row">
                            <div class="mr-1">5.</div>
                            The Training Team reserves the right to modify program schedules, course content, or delivery methods due to unforeseen circumstances. Any changes will be communicated to participants in advance.
                        </div>
                        <div class="mt-1 d-flex flex-row">
                            <div class="mr-1">6.</div>
                            Any behavior or actions that violate course rules may result in removal from the program. In such cases, refunds may not be provided.
                        </div>
                        <div class="mt-5">
                            By completing the registration process, participants confirm that they have read, understood, and agreed to these <b class="cursor-pointer" @click="registrationReminder = false, termsAndCondition = true"><i class="primary--text text-decoration-underline">Terms and Conditions</i></b>.
                        </div>
                    </div>
                </div>
                
                <v-row dense class="pa-5" v-if="registrationReminder || termsAndCondition" >
                    <v-col>
                        <v-btn text color="secondary" block class="poppins" @click="() => { reset(), $emit('close') }">
                            Cancel
                        </v-btn>
                    </v-col>
                    <v-col>
                        <v-btn color="primary" block :disabled="!(proceed === true)" class="poppins"
                            @click="() => { termsAndCondition = false, (course.is_free || course.price <= 0) ? participantOptions = true :howToPayConfirmation = true}">
                            Continue
                        </v-btn>
                    </v-col>
                </v-row>

                <HowToPay 
                    v-if="howToPayConfirmation" 
                    @back="() => { termsAndCondition = true, howToPayConfirmation = false }"
                    @close="() => { reset(), $emit('close') }"
                    @continue="() => { howToPayConfirmation = false, isTheAgencyPayingForYou = true}"
                />

                <IsTheAgencyPayingForYou
                    v-if="isTheAgencyPayingForYou"
                    :course="course"
                    @close="() => { $emit('close'), reset() }"
                    @yes="()=>{ nomination = true, isTheAgencyPayingForYou = false, paymentOptions = true }"
                    @no="()=>{ nomination = false, isTheAgencyPayingForYou = false, paymentOptions = true  }"
                />

                <PaymentOptions 
                    v-if="paymentOptions"
                    @close="() => { $emit('close'), reset() }"
                    @back="() => {  isTheAgencyPayingForYou = true, paymentOptions = false, paymentType = '' }"
                    @setPaymentType="(e) => paymentType = e"
                    @continue="() => { paymentOptions = false, participantOptions = true }"
                />

                <ParticipantType
                    v-if="participantOptions"
                    @enroll="()=>{  enroll() }"
                    @close="() => { $emit('close'), reset() }"
                    @back="() => { (course.is_free || course.price <= 0) ? termsAndCondition = true : paymentOptions = true, participantOptions = false, participantType = '' }"
                    @setParticipantType="(e) => participantType = e"
                    @continue="() => { participantOptions = false, uploadRequirements = true, error = null }"
                    @proceedToOnlinePortal="() => { error = null, submitToOnlinePortal() }"
                    :course="course"
                    :loading="submitLoading"
                    :paymentType="paymentType"
                    :error="error"
                />

                <div v-if="uploadRequirements">
                    
                    <!-- <UploadNomination
                            v-if="nomination"
                            :course="course"
                            @back="() => { uploadRequirements = false, nomination = false, isTheAgencyPayingForYou = true}"
                            @close="() => { $emit('close'), reset() }"
                            @setNominationForm="(e) => nominationForm = e"
                            @setProofOfPayment="(e) => proofOfPayment = e"
                            :error_nomination_form="errors.nomination_form && errors.nomination_form[0]"
                            :error_proof_of_payment="errors.proof_of_payment && errors.proof_of_payment[0]"
                        /> -->
                    <section class="d-flex flex-wrap justify-end" v-if="['regular_payee_hrm'].includes(participantType) && ['online_portal'].includes(paymentType) && !nomination">
                        <section class="text-right">
                            <v-btn icon @click="() => { $emit('close'), reset() }">
                                <v-icon small>
                                    mdi-close
                                </v-icon>
                            </v-btn>
                        </section>
                    </section>               
<!--                     <v-container class="pa-5" v-if="!['regular_payee_hrm'].includes(participantType) && ['online_portal'].includes(paymentType)">
                        Please proceed to your transaction history once valid Proof of Payment (POP) is available.
                    </v-container> -->

                    <UploadCommitmentLetter 
                        v-if="uploadRequirements && paymentType === 'commitment_letter'"
                        @setCommitmentLetter="(e) => commitmentLetter = e"
                        :error="errors.commitment_letter && errors.commitment_letter[0]"
                    />

                    <UploadPrimeRequirements
                        v-if="uploadRequirements && ['regular_payee_hrm'].includes(participantType)"
                        @setEndorsementLetter="(e) => endorsementLetter = e"
                        @setPrimeResolution="(e) => primeResolution = e"
                        :error_endorsement_letter="errors.endorsement_letter && errors.endorsement_letter[0]"
                        :error_prime_resolution="errors.prime_resolution && errors.prime_resolution[0]"
                    />

                    <UploadProofOfPayment
                        v-if="(uploadRequirements && !['online_portal', 'commitment_letter'].includes(paymentType) && !(course.is_free || course.price <= 0))"
                        @setProofOfPayment="(e) => proofOfPayment = e"
                        @setReferenceNumber="(e) => referenceNumber = e"
                        :error_proof_of_payment="errors.proof_of_payment && errors.proof_of_payment[0]"
                        :error_reference_number="errors.reference_number && errors.reference_number[0]"
                    />

                    <alert 
                        v-if="error"
                        :show = "true"
                        :text = "error"
                        :type = "'error'"
                        class="mx-3"
                    />

                    <v-row dense class="pa-3" v-if="!nomination && paymentType !== 'online_portal'">
                        <v-col>
                            <v-btn text block class="poppins" @click="() => { uploadRequirements = false, participantOptions = true, participantType = '', error = null,  resetFiles() }">
                                Back
                            </v-btn>
                        </v-col>
                        <v-col>
                            <v-btn color="primary" block class="poppins"
                            @click="(course.is_free || course.price <= 0) ? enroll() : submitRequirements()" :loading="submitLoading">
                                {{ (course.is_free || course.price <= 0) ? "Submit and Enroll" : "Submit" }}
                            </v-btn>
                        </v-col>
                    </v-row>
                    
                    <!-- FOR ONLINE PORTAL without NOMINATION -->
                    <!-- <v-row dense class="pa-3" v-else-if="!nomination && paymentType == 'online_portal'">
                        <v-col>
                            <v-btn text block class="poppins" @click="() => { uploadRequirements = false, participantOptions = true, participantType = '', error = null,  resetFiles() }">
                                Back
                            </v-btn>
                        </v-col>
                        <v-col>
                            <v-btn color="primary" block class="poppins"
                            @click="submitToOnlinePortal" :loading="submitLoading">
                                {{ participantType === 'regular_payee_hrm' ? 'Submit and Proceed to Payment Portal' : 'Proceed to Payment Portal'}}
                            </v-btn>
                        </v-col>
                    </v-row> -->

                    <!-- FOR non ONLINE PORTAL with NOMINATION -->

                    <v-row dense class="pa-3" v-if="nomination && paymentType !== 'online_portal'">
                        <v-col>
                            <v-btn text block class="poppins" @click="() => { uploadRequirements = false, participantOptions = true, participantType = '', error = null,  resetFiles() }">
                                Back
                            </v-btn>
                        </v-col>
                        <v-col>
                            <v-btn color="primary" block class="poppins" @click="submitNomination" :loading="submitLoading">
                                Submit
                            </v-btn>
                        </v-col>
                    </v-row>
                    

                    <!-- FOR ONLINE PORTAL with NOMINATION -->
                    <!-- <v-row dense class="pa-3" v-if="nomination && paymentType === 'online_portal'">
                        <v-col>
                            <v-btn text block class="poppins" @click="() => { uploadRequirements = false, participantOptions = true, participantType = '', error = null, resetFiles() }">
                                Back
                            </v-btn>
                        </v-col>
                        <v-col>
                            <v-btn color="primary" block class="poppins" @click="submitToOnlinePortal" :loading="submitLoading">
                                Submit and Proceed to Payment Portal
                            </v-btn>
                        </v-col>
                    </v-row> -->
                </div>
                

            </v-card>
        </v-dialog>
        
        <SuccessDialog
            :dialog="successDialog" 
            :title="success.successTitle" 
            :message="success.successText"
            :icon="true"
        />

        <DeliveryOptionDialog
            :dialog="deliveryOptions"
            :loading="deliveryOptionsLoading"
            :errors="errors"
            @proceed="(e) => { deliveryOption = e.deliveryOption, deliveryAddress = e.deliveryAddress, saveDeliveryDetails()  }"
        />

    </section>
</template>

<script>
import HowToPay from './HowToPay.vue';
import IsTheAgencyPayingForYou from './IsTheAgencyPayingForYou.vue';
import PaymentOptions from './PaymentOptions.vue';
import UploadNomination from './UploadNomination.vue';
import ParticipantType from './ParticipantType.vue';
import UploadCommitmentLetter from './UploadCommitmentLetter.vue';
import UploadPrimeRequirements from './UploadPrimeRequirements.vue';
import UploadProofOfPayment from './UploadProofOfPayment.vue';
import DeliveryOptionDialog from './DeliveryOptionDialog.vue';
import SuccessDialog from '@/constants/reusable/SuccessDialog.vue';
import { mapActions, mapState, mapMutations } from 'vuex';

export default {
    components: {
        HowToPay,
        IsTheAgencyPayingForYou,
        PaymentOptions,
        ParticipantType,
        UploadNomination,
        UploadCommitmentLetter,
        SuccessDialog,
        UploadPrimeRequirements,
        UploadProofOfPayment,
        DeliveryOptionDialog
    },
    props: {
        dialog: {
            default: false,
            type: Boolean
        },
        course: {
            default: null,
            type: Object || null
        }
    },
    data: () => ({
        paymentNotice: false, // payment-portal notice disabled
        termsAndCondition: false,
        registrationReminder: true,
        howToPayConfirmation: false,
        isTheAgencyPayingForYou: false,
        paymentOptions: false,
        participantOptions: false,
        nomination: false,
        uploadRequirements: false, 
        deliveryOptions: false,
        endTransaction: false,
        successDialog: false,
        successCommitmentLetterDialog: true, 
        proceed: false,
        paymentType: '',
        participantType: '',
        success: {
            successTitle: '',
            successText: ''
        },
        commitmentLetter: null,
        primeResolution: null, 
        endorsementLetter: null,
        proofOfPayment: '',
        nominationForm: null,
        submitLoading: false,
        deliveryOptionsLoading: false,
        referenceNumber: null,
        deliveryOption: null,
        deliveryAddress: null,
        error: null,
        proceedToPaymentLoading: false,
        invoice: null,
        successText: 'Processing may take up to 3 working days for validation, depending on the number/volume of transactions'
    }),
    methods: {
        ...mapMutations(['alertMutation']),
        ...mapActions('usr', ['courseCheckoutOwnAccount', 'courseCheckoutOnlinePortal', 'courseCheckout', 'courseCheckoutNomination', 'updateReceiptDeliveryOptionAction']),

        reset(){
            this.paymentNotice = false
            this.registrationReminder = true
            this.termsAndCondition = false
            this.howToPayConfirmation = false
            this.isTheAgencyPayingForYou = false
            this.nomination = false
            this.participantOptions = false
            this.paymentOptions = false
            this.uploadRequirements = false
            this.proceed = false
            this.paymentType = ''
            this.participantType = ''
            this.commitmentLetter = null
            this.primeResolution = null
            this.endorsementLetter = null
            this.proofOfPayment = null
            this.nominationForm = null
            this.referenceNumber = ''
            this.error = null
            this.submitLoading = false
            this.proceedToPaymentLoading = false
        },

        resetFiles(){
            this.commitmentLetter = null
            this.primeResolution = null
            this.endorsementLetter = null
            this.proofOfPayment = null
            this.nominationForm = null
            this.error = null
        },

        submitRequirements(){
            let _form = new FormData()

            _form.append('course_id', this.course.id)
            if(this.commitmentLetter) {
                _form.append('commitment_letter', this.commitmentLetter)
            }
            if(this.primeResolution) {
                _form.append('prime_resolution', this.primeResolution)
            }
            if(this.endorsementLetter) {
                _form.append('endorsement_letter', this.endorsementLetter)
            }
            if(this.proofOfPayment) {
                _form.append('proof_of_payment', this.proofOfPayment)
            }
            if(this.referenceNumber) {
                _form.append('reference_number', this.referenceNumber)
            }
            _form.append('type_of_payment', this.paymentType)
            _form.append('type_of_participant', this.participantType)

            this.submitLoading = true
            this.errors = {}
            this.error = null
            this.courseCheckoutOwnAccount(_form).then(res => {
                this.invoice = res.invoice
                this.$emit('close')
                this.reset()
                
                if(this.invoice) {
                    if(!['online_portal'].includes(this.invoice.type_of_payment)) {
                        this.deliveryOptions = true
                    } else {
                        this.success = {
                            successTitle: 'Attachments has been uploaded',
                            successText: this.successText
                        }

                        this.successDialog = true
                    }
                }
            }).catch((e) => {
                if(!this.errors.prime_resolution && !this.errors.endorsement_letter && !this.errors.commitment_letter && !this.errors.proof_of_payment) {
                    this.error = e.response.data.message
                }
            }).finally(() => {
                this.submitLoading = false
                console.log(this.errors)
            })
        },

        submitToOnlinePortal() {
            let _form = new FormData()

            _form.append('course_id', this.course.id)

            if(this.primeResolution) {
                _form.append('prime_resolution', this.primeResolution)
            }
            if(this.endorsementLetter) {
                _form.append('endorsement_letter', this.endorsementLetter)
            }
            if(this.nomination) {
              _form.append('nomination_form', this.nominationForm)
            }
            _form.append('type_of_payment', this.paymentType)
            _form.append('type_of_participant', this.participantType)

            this.submitLoading = true
            this.errors = {}
            this.error = null
            
            const config = { 
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data',
                }
            }
            this.$api.post(`/user/checkout`, _form, config).then(res => {
                this.$paymentHandler(res.data)
                if(this.participantType === 'regular_payee_hrm' ) {
                    this.success = {
                        successTitle: 'Attachments has been uploaded, Process your Payment on the Payment Portal',
                        successText: this.successText
                    }
                } else {
                    this.success = {
                        successTitle: 'Process your Payment on the Payment Portal',
                        successText: this.successText
                    }
                }
                this.successDialog = true
                this.$emit('close')
                this.reset()
            }).catch((e) => {
                this.submitLoading = false
                if(!this.errors.prime_resolution || !this.errors.endorsement_letter || !this.errors.nomination_form) {
                    this.error = e.response?.data?.message ? e.response?.data?.message : 'Something went wrong'
                }
            }).finally(() => {
                this.submitLoading = false
            })
            // this.$api.post(`/user/checkout`, _form, config).then(res => {
            //     setTimeout(() => {
            //         this.$paymentHandler(res.data)

            //         if(this.errors.prime_resolution || this.errors.endorsement_letter || this.errors.nomination_form) {
            //             this.success = {
            //                 successTitle: 'Attachments has been uploaded, Process your Payment on the Payment Portal',
            //                 successText: 'Processing may take up to 3 working days.'
            //             }
            //         } else {
            //             this.success = {
            //                 successTitle: 'Process your Payment on the Payment Portal',
            //                 successText: 'Processing may take up to 3 working days.'
            //             }
            //         }

            //         this.successDialog = true
            //         this.$emit('close')
            //         this.reset()
            //     }, 3000)
            // }).catch((e) => {
            //     if(!this.nomination) {
            //         if(!this.errors.prime_resolution || !this.errors.endorsement_letter ) {
            //             this.error = e.response.data.message ? e.response.data.message : 'Something went wrong'
            //         }
            //     } else {
            //         this.error = 'Something went wrong'
            //     }
            // }).finally(() => {
            //     this.submitLoading = false
            //     console.log(this.errors)
            // })

            
            // this.courseCheckoutOnlinePortal(_form).then(res => {
            //     this.$paymentHandler(res.data)

            //     if(this.errors.prime_resolution || this.errors.endorsement_letter || this.errors.nomination_form) {
            //         this.success = {
            //             successTitle: 'Attachments has been uploaded, Process your Payment on the Payment Portal',
            //             successText: 'Processing may take up to 3 working days.'
            //         }
            //     } else {
            //         this.success = {
            //             successTitle: 'Process your Payment on the Payment Portal',
            //             successText: 'Processing may take up to 3 working days.'
            //         }
            //     }

            //     this.successDialog = true
            //     this.$emit('close')
            //     this.reset()
            // }).catch((e) => {
            //     if(!this.nomination) {
            //         if(!this.errors.prime_resolution || !this.errors.endorsement_letter ) {
            //             this.error = e.response.data.message ? e.response.data.message : 'Something went wrong'
            //         }
            //     } else {
            //         if(!this.errors.nomination_form) {
            //             this.error = e.response.data.message ? e.response.data.message : 'Something went wrong'
            //         }
            //     }
            // }).finally(() => {
            //     this.submitLoading = false
            //     console.log(this.errors)
            // })
        },

        submitNomination() {
            let _form = new FormData()

            _form.append('course_id', this.course.id)

            if(this.primeResolution) {
                _form.append('prime_resolution', this.primeResolution)
            }
            if(this.endorsementLetter) {
                _form.append('endorsement_letter', this.endorsementLetter)
            }
            if(this.commitmentLetter) {
                _form.append('commitment_letter', this.commitmentLetter)
            }
            if(this.proofOfPayment) {
                _form.append('proof_of_payment', this.proofOfPayment)
            }
            if(this.referenceNumber) {
                _form.append('reference_number', this.referenceNumber)
            }
            _form.append('nomination_form', this.nominationForm)
            _form.append('type_of_payment', this.paymentType)
            _form.append('type_of_participant', this.participantType)

            this.submitLoading = true
            this.errors = {}
            this.error = null
            this.courseCheckoutNomination(_form).then(res => {
                this.invoice = res.invoice
                this.$emit('close')
                this.reset()

                if(this.invoice) {
                    if(!['online_portal'].includes(this.invoice.type_of_payment)) {
                        this.deliveryOptions = true
                    } else {
                        this.success = {
                            successTitle: 'Attachments has been uploaded',
                            successText: this.successText
                        }

                        this.successDialog = true
                    }
                }

            }).catch((e) => {
                if(!this.errors.nomination_form || !this.errors.proof_of_payment || !this.errors.prime_resolution || !this.errors.endorsement_letter || !this.errors.commitment_letter) {
                    this.error = e.response.data.message ? e.response.data.message : 'Something went wrong'
                }
            }).finally(() => {
                this.submitLoading = false
                console.log(this.errors)
            })

        },

        enroll(){
            let _form = new FormData()

            _form.append('course_id', this.course.id)
            _form.append('type_of_payment', this.paymentType)
            _form.append('type_of_participant', this.participantType)

            if(this.primeResolution) {
                _form.append('prime_resolution', this.primeResolution)
            }
            if(this.endorsementLetter) {
                _form.append('endorsement_letter', this.endorsementLetter)
            }

            this.submitLoading = true

            this.courseCheckout(_form).then(res => {
                this.alertMutation({
                    show: true,
                    text: 'Enrollment Success! Redirecting...',
                    type: "success"
                })
                this.submitLoading = false
                this.$emit('close')
                this.reset()
                setTimeout(() => {
                    this.$router.push({name: 'User Course Details', params: {id: this.course.uuid}})
                }, 3000)
            }).catch(err => {
                this.submitLoading = false
                if(err.response?.status === 409) { 
                    this.alertMutation({
                        show: true,
                        text: err.response.data.message,
                        type: "error"
                    })
                    this.$emit('close')
                    this.reset()
                } else if(!this.errors.prime_resolution && !this.errors.endorsement_letter) {
                    this.error = err.response.data.message
                }
            }).finally(() => {
                this.submitLoading = false
            })
            
        },

        saveDeliveryDetails(){
            console.log(this.invoice)
            
            if(!this.deliveryAddress && !this.deliveryOption) {
                this.deliveryOptions = false
                
                this.success = {
                    successTitle: 'Attachments has been uploaded',
                    successText: this.successText
                }

                this.successDialog = true
            } else {
                this.deliveryOptionsLoading = true
                this.updateReceiptDeliveryOptionAction({ invoice_id: this.invoice.id, delivery_option: this.deliveryOption, delivery_address: this.deliveryAddress })
                    .then(() => {
                        this.deliveryOptions = false
                        
                        this.success = {
                            successTitle: 'Attachments has been uploaded and Receipt Delivery method has been updated successfully!',
                            successText: this.successText
                        }

                        this.successDialog = true
                    }).catch(() => {
                        this.alertMutation({
                            show: true,
                            text: 'Something went wrong',
                            type: "error"
                        })
                    }).finally(() => {
                        this.deliveryOptionsLoading = false
                    })
                }
        }
    },
    computed: {
        ...mapState({
            errors: (state) => state.errors,
        }),
    },
    watch: {
        successDialog(val){
            if(val === true) {
                setTimeout(() => {
                    this.successDialog = false
                    this.success = {
                        successTitle: '',
                        successText: ''
                    }
                }, 10000)
            } else {
                this.success = {
                    successTitle: '',
                    successText: ''
                }
            }
        },
        dialog(val) {
            if(val) {
                if(this.course.is_free || this.course.price <= 0) {
                    this.paymentNotice = false
                    this.registrationReminder = true
                } else {
                    this.paymentNotice = false
                    this.registrationReminder = true
                }
            }
        }
    }
}
</script>