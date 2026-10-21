<template>
    <section>
        <v-dialog v-model="dialog" persistent width="auto">
            <v-card v-if="nominationConfirmation" width="370">
                <section class="text-right">
                    <v-btn icon @click="$emit('close')">
                        <v-icon>
                            mdi-close
                        </v-icon>
                    </v-btn>
                </section>
                <v-container fluid class="text-center primary-font f16 fw600">
                    Is the agency paying this for you?
                </v-container>
                <v-sheet v-if="course" class="custom-border border py-3 d-flex justify-center flex-column align-center">
                    <v-img 
                        :src="course.image ? course.image.url : require('@/assets/images/course-photo.png')"
                        v-on:error="require('@/assets/images/course-photo.png')"
                        class="rounded-lg"
                        width="150"
                        height="100"
                    />
                    <b class="mt-2 f16 text-center">{{ course.title ? course.title : 'Untitled' }}</b>
                    <section v-if="course.available_until" class="fw500 mx-3 f12">
                        <span v-if="course.available_until.start_date"> from {{ $dateFormat.mmDDyy(course.available_until.start_date) }} </span>
                        <span v-if="course.available_until.end_date"> until {{ $dateFormat.mmDDyy(course.available_until.end_date) }} </span>
                    </section>
                    <section v-if="course.is_free" class="f16 fw600 mx-3 f14">
                        <span class="secondary-2--text" >COST : </span>
                        FREE!
                        <span class="mb-3" v-if="!course.is_free">COST : </span>
                    </section>
                    <section v-if="!course.is_free" class="f16 fw600 mx-3">
                        PHP {{ course.price ? course.price : 0 }}
                    </section>
                    <v-container fluid class="d-flex flex-row justify-space-evenly">
                        <!-- <v-btn dense text @click="proceedToPayment" :loading="proceedToPaymentLoading">
                            NO
                        </v-btn> -->
                        <v-btn dense text @click="() => {
                            chosen = 'NO'
                            nominationConfirmation = false
                            paymentOptionsConfirmation = true
                        }">
                            NO
                        </v-btn>
                        <v-spacer />
                        <v-btn outlined dense color="primary" @click="() => {
                            chosen = 'YES'
                            nominationConfirmation  =  false
                        }">
                            YES
                        </v-btn>
                    </v-container>
                </v-sheet>
            </v-card>
            
            <UploadNomination
                v-if="uploadNominationConfirmation"
                :course="course"
                @success="$emit('success', {
                    successTitle: 'Transaction is on progress',
                    successText: 'Check on your transaction page for updates / action needed. Processing may take up to 24hrs during workdays except holidays and weekends.'
                })"
                @back="() => { uploadNominationConfirmation = false }"
                @close="() => { $emit('close'), uploadNominationConfirmation = false, nominationConfirmation = true }"
            />

            <UploadProofOfPayment
                v-if="uploadProofOfPaymentConfirmation"
                :type_of_payment="type_of_payment"
                :course="course"
                @success="$emit('success',  {
                    successTitle: 'Proof of payment has been uploaded',
                    successText: 'Processing may take up to 3 working days.'
                })"
                @back="() => {
                    uploadProofOfPaymentConfirmation = false
                }"
                @close="() => { $emit('close'), uploadProofOfPaymentConfirmation = false, paymentOptionsConfirmation = false, nominationConfirmation = true }"
            />

            <PaymentOptionsDialog 
                v-if="paymentOptionsConfirmation"
                :loading="proceedToPaymentLoading"
                @continue="(e) => {
                    type_of_payment = e,
                    paymentOptionsConfirmation = false
                }"
                @back="() => {
                    paymentOptionsConfirmation = false,
                    nominationConfirmation = true
                }"
                @close="() => { $emit('close'), 
                    paymentOptionsConfirmation = false,
                    nominationConfirmation = true }"
                @proceedToPayment="proceedToPayment()"
            />
        </v-dialog>
    </section>
    
</template>

<script>
import { mapActions, mapState, mapMutations } from "vuex"
import UploadNomination from './UploadNomination.vue'
import PaymentOptionsDialog from "./PaymentOptions.vue"
import UploadProofOfPayment from "./UploadProofOfPayment.vue"

export default {
    components: { 
        UploadNomination,
        PaymentOptionsDialog,
        UploadProofOfPayment
    },
    props: {
        dialog: Boolean,
        course: Object,
    },
    data: () => ({
        proceedToPaymentLoading: false,
        nominationConfirmation: true,
        uploadNominationConfirmation: false,
        paymentOptionsConfirmation: false,
        uploadProofOfPaymentConfirmation: false,
        file: null,
        loading: false,
        submitLoading: false,
        chosen: '',
        msg: {
            show: false,
            type: '',
            text: ''
        },
        type_of_payment: ''
    }),
    computed: {
        ...mapState({
            errors: (state) => state.errors,
        }),
    },
    methods: {
        ...mapActions('usr', ['courseCheckoutOnlinePortal']),

        ...mapMutations(['alertMutation']),

        back(){
            if(this.chosen === 'YES') {
                this.nominationConfirmation = true
            } else  {
                this.paymentOptionsConfirmation = true
            }
        },

        close(){
            if(this.chosen === 'YES') {
                this.nominationConfirmation = true
            } else  {
                this.paymentOptionsConfirmation = false
                this.nominationConfirmation = true
            }
        },

        proceedSendAttachments(){
            if(this.chosen === 'YES') {
                this.uploadNominationConfirmation = true
            } else {
                this.uploadProofOfPaymentConfirmation = true
            }
        },

        proceedToPayment(){
            this.proceedToPaymentLoading = true

            let _form = new FormData()
            _form.append('course_id', this.course.id)

            this.courseCheckoutOnlinePortal(_form).then(res => {
                this.$paymentHandler(res.data)
                this.$emit('close')
            }).catch(err => {
                this.$emit('close'), 
                this.paymentOptionsConfirmation = false,
                this.nominationConfirmation = true
                if(err.response?.status === 409) { 
                    this.alertMutation({
                        show: true,
                        text: err.response.data.message,
                        type: "error"
                    })
                } else {
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong.',
                        type: "error"
                    })
                }
            }).finally(() => {
                this.proceedToPaymentLoading = false
            })
        }
    }
    
}
</script>