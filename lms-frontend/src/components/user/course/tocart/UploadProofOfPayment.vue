<template>
    <div>
        <div class="overflow-x-hidden" :class="form ?  '' : 'pa-5'">
            <div fluid class="primary-font fw600 f16" >
                <label class="caption" v-if="form">UPLOAD PROOF OF PAYMENT</label>
                <section class="primary-font primary--text fw600 f17" v-else>
                    Upload Proof of Payment
                </section>
                    
                <FormTextFieldRequired
                    :label="'PAYMENT REFERENCE NUMBER *'"
                    :value.sync="reference_number"
                    :error="error_reference_number"
                />
                
                <v-container
                    class="bulk-border mt-2 d-flex flex-column justify-center"
                    @dragover="dragover"
                    @dragleave="dragleave"
                    @drop="dropProofOfPayment"
                >
                    <div class="d-flex text-center flex-column">
                        <v-icon color="rgba(63, 93, 208, 0.12)" size="68"
                            >mdi-cloud-upload-outline</v-icon
                        >
                        <div class="secondary--text f12 poppins">
                            <a
                            href="#"
                            @click="$refs.proof_of_payment.click()"
                            class="primary--text text-decoration-none"
                            >
                            Browse
                            </a>
                            or drag file here ( .pdf, .docx, .doc, .jpg, .jpeg, .png )
                        </div>
                    </div>
                </v-container>
                <v-container
                    v-if="proofOfPayment"
                    class="d-flex justify-space-between align-center my-3">
                    <div style="width: 250px;">
                        <div class="roboto fw600 f12" style="margin-bottom: -8px">
                            {{ proofOfPayment.name }}
                        </div>
                        <span class="roboto fw500 f10 secondary--text">
                            {{ (proofOfPayment.size / 1000).toFixed(2) }} KB
                        </span>
                    </div>
                    <v-spacer />
                    <v-progress-linear
                        v-if="loading"
                        indeterminate
                        color="success"
                        height="7"
                        style="width: 120px"
                        rounded
                    />
                    <v-btn icon small @click="remove" v-if="!submitLoading">
                        <v-icon size="18" color="danger-1">mdi-close</v-icon>
                    </v-btn>
                </v-container>
            </div>
            <alert 
                v-if="msg.show"
                :show = "msg.show"
                :text = "msg.text"
                :type = "msg.type"
            />
            
            <!-- <section class="d-flex flex-row justify-space-evenly pa-3">
                <v-btn text @click="() => {
                    remove()
                    $emit('back')
                }">
                    GO BACK
                </v-btn>
                <v-spacer />
                <v-btn color="primary" :disabled="!loading ? !proofOfPayment : true" @click="submitProofOfPayment()" :loading="submitLoading">
                    SUBMIT
                </v-btn>
            </section> -->
            <input
                type="file"
                ref="proof_of_payment"
                class="d-none"
                accept=".pdf,.docx,.doc,.png,.jpg,.jpeg"
                @change="acceptProofOfPayment"
            />
        </div>
    </div>
</template>

<script>
import { mapActions, mapState, mapMutations } from "vuex"

export default {
    props: {
        course: {
            default: null,
            type: Object || null
        },
        error_proof_of_payment: {
            default: '',
            type: String
        },
        error_reference_number: {
            default: '',
            type: String
        },
        form: {
            default: false,
            type: Boolean
        }
    },
    data: () => ({
        file: null,
        proofOfPayment: null,
        loading: false,
        submitLoading: false,
        reference_number: null,
        msg: {
            show: false,
            type: '',
            text: ''
        }
    }),
    watch: {
        reference_number(val){
            this.$emit('setReferenceNumber', val)
        },
        error_proof_of_payment(val) {
            if(val === '') {
                this.msg = {
                    show: false,
                    type: '',
                    text: ''
                }
            } else {
                this.msg = {
                    show: true,
                    type: "error",
                    text: val
                }
            }
        }
    },
    computed: {
        ...mapState({
            errors: (state) => state.errors,
        }),
    },
    methods: {
        ...mapActions('usr', ['courseCheckoutProofOfPayment']),
        ...mapMutations(['alertMutation']),

        remove(){
            this.proofOfPayment = ''
            this.$refs.proof_of_payment.value = ''
            this.$emit('setProofOfPayment', null)
        },

        dragover(e) {
            e.preventDefault();
            e.currentTarget.classList.add("bulk-border-drag");
        },

        dragleave(e) {
            e.preventDefault();
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        dropProofOfPayment(e) {
            e.preventDefault();
            this.$refs.proof_of_payment.files = e.dataTransfer.files;
            this.acceptProofOfPayment();
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        // submitProofOfPayment(e){
        //     this.submitLoading = true

        //     this.msg = {
        //         show: false,
        //         type: '',
        //         text: ''
        //     }

        //     let _form = new FormData()

        //     _form.append('course_id', this.course.id)
        //     _form.append('type_of_payment', this.type_of_payment)
        //     _form.append('proof_of_payment', this.proofOfPayment)

        //     this.courseCheckoutProofOfPayment(_form).then(() => {
        //         this.remove()
        //         this.$emit('close')
        //         this.$emit('success')
        //     }).catch(res => {
        //         if(this.errors.proof_of_payment) {
        //             this.msg = {
        //                 show: true,
        //                 type: 'error',
        //                 text: this.errors.proof_of_payment[0]
        //             }
        //         } else {
        //             this.msg = {
        //                 show: true,
        //                 type: 'error',
        //                 text: res.response.data.message
        //             }
        //         }
        //     }).finally(() => {
        //         this.submitLoading = false
        //     })
        // },

        acceptProofOfPayment() {
            this.msg = {
                show: false,
                type: '',
                text: ''
            }

            this.proofOfPayment = this.$refs.proof_of_payment.files[0]
            this.$emit('setProofOfPayment', this.$refs.proof_of_payment.files[0])
            this.loading = true;
            setTimeout(() => {
                this.loading = false;
            }, 3000);
            
            this.$refs.proof_of_payment.value = ''            
        },
    }
    
}
</script>