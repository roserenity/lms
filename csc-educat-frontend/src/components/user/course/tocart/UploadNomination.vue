<template>
    <div>
        <div class="overflow-x-hidden" :class="form ?  '' : 'pa-5'">
            <div fluid class=" primary-font fw600 f16" >
                <label class="caption" v-if="form">UPLOAD NOMINATION FORM</label>
                <section class="primary-font primary--text fw600 f17" v-else>
                    Upload Nomination Form
                </section>
                <v-container
                    class="bulk-border mt-2 d-flex flex-column justify-center"
                    @dragover="dragover"
                    @dragleave="dragleave"
                    @drop="dropNominationForm"
                >
                    <div class="d-flex text-center flex-column">
                        <v-icon color="rgba(63, 93, 208, 0.12)" size="68"
                            >mdi-cloud-upload-outline</v-icon
                        >
                        <div class="secondary--text f12 poppins">
                            <a
                            href="#"
                            @click="$refs.nomination_form.click()"
                            class="primary--text text-decoration-none"
                            >
                            Browse
                            </a>
                            or drag file here ( .pdf, .doc, .docx )
                        </div>
                    </div>
                </v-container>
                <v-container
                    v-if="nominationForm"
                    class="d-flex justify-space-between align-center my-3">
                    <div style="width: 250px;">
                        <div class="roboto fw600 f12" style="margin-bottom: -8px">
                            {{ nominationForm.name }}
                        </div>
                        <span class="roboto fw500 f10 secondary--text">
                            {{ (nominationForm.size / 1000).toFixed(2) }} KB
                        </span>
                    </div>
                    <v-spacer />
                    <v-progress-linear
                        v-if="loading1"
                        indeterminate
                        color="success"
                        height="7"
                        style="width: 120px"
                        rounded
                    />
                    <v-btn icon small @click="remove(0)" v-if="!submitLoading">
                        <v-icon size="18" color="danger-1">mdi-close</v-icon>
                    </v-btn>
                </v-container>
            </div>
            <alert 
                v-if="msg1.show"
                :show = "msg1.show"
                :text = "msg1.text"
                :type = "msg1.type"
                class="mx-3"
            />
            
<!--                 
            <v-row dense class="mt-3">
                <v-col>
                    <v-btn text block class="poppins" @click="() => {
                        remove(0)
                        remove(1)
                        $emit('back')
                    }">
                        Back
                    </v-btn>
                </v-col>
                <v-col>
                    <v-btn color="primary" block :disabled="(!loading1 && !loading2) ? (!nominationForm && !proofOfPayment) : true" class="poppins"
                    @click="submitNominationForm()" :loading="submitLoading">
                        Submit
                    </v-btn>
                </v-col>
            </v-row> -->

            <input
                type="file"
                ref="nomination_form"
                class="d-none"
                accept=".docx,.doc,.pdf"
                @change="acceptNomination"
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
        error_nomination_form: {
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
        nominationForm: null,
        proofOfPayment: null,
        loading1: false,
        loading2: false,
        submitLoading: false,
        msg1:  {
            show: false,
            type: '',
            text: ''
        },
    }),
    watch: {
        error_nomination_form(val) {
            if(val === '') {
                this.msg1 = {
                    show: false,
                    type: '',
                    text: ''
                }
            } else {
                this.msg1 = {
                    show: true,
                    type: "error",
                    text: val
                }
            }
        },
        error_proof_of_payment(val) {
            if(val === '') {
                this.msg2 = {
                    show: false,
                    type: '',
                    text: ''
                }
            } else {
                this.msg2 = {
                    show: true,
                    type: "error",
                    text: val
                }
            }
        },
    },
    computed: {
        ...mapState({
            errors: (state) => state.errors,
        }),
    },
    methods: {
        ...mapActions('usr', ['courseCheckoutNomination']),
        ...mapMutations(['alertMutation']),

        remove(index){
            if(index === 0) {
                this.nominationForm = ''
                this.$refs.nomination_form.value = ''
            } else {
                this.proofOfPayment = ''
                this.$refs.proof_of_payment.value = ''
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

        dropNominationForm(e) {
            e.preventDefault();
            this.$refs.nomination_form.files = e.dataTransfer.files;
            this.acceptNomination();
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        dropProofOfPayment(e) {
            e.preventDefault();
            this.$refs.proof_of_payment.files = e.dataTransfer.files;
            this.acceptProofOfPayment();
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        // submitNominationForm(){
        //     this.submitLoading = true

        //     this.msgs = [
        //         {
        //             show: false,
        //             type: '',
        //             text: ''
        //         },
        //         {
        //             show: false,
        //             type: '',
        //             text: ''
        //         }
        //     ]

        //     let _form = new FormData()

        //     _form.append('course_id', this.course.id)
        //     _form.append('nomination_form', this.nominationForm)
        //     _form.append('proof_of_payment', this.proofOfPayment)

        //     this.courseCheckoutNomination(_form).then(() => {
        //         this.remove(0)
        //         this.remove(1)
        //         this.$emit('close')
        //         this.$emit('success')
        //     }).catch(res => {
        //         if(this.errors.nomination_form) {
        //             this.msgs[0] = {
        //                 show: true,
        //                 type: 'error',
        //                 text: this.errors.nomination_form[0]
        //             }
        //         }
        //         if(this.errors.proof_of_payment) {
        //             this.msgs[1] = {
        //                 show: true,
        //                 type: 'error',
        //                 text: this.errors.proof_of_payment[0]
        //             }
        //         }
        //         if(!this.errors.nomination_form && !this.errors.proof_of_payment) {
        //             this.msgs[1] = {
        //                 show: true,
        //                 type: 'error',
        //                 text: res.response.data.message
        //             }
        //         }
        //     }).finally(() => {
        //         this.submitLoading = false
        //     })
        // },

        acceptNomination() {
            this.msg1 = {
                show: false,
                type: '',
                text: ''
            }

            this.nominationForm = this.$refs.nomination_form.files[0]
            this.$emit('setNominationForm', this.$refs.nomination_form.files[0])
            this.loading1 = true;
            setTimeout(() => {
                this.loading1 = false;
            }, 3000);
            
            this.$refs.nomination_form.value = ''            
        },

        acceptProofOfPayment() {
            this.msg2 = {
                show: false,
                type: '',
                text: ''
            }

            this.proofOfPayment = this.$refs.proof_of_payment.files[0]
            this.$emit('setProofOfPayment', this.$refs.proof_of_payment.files[0])
            this.loading2 = true;
            setTimeout(() => {
                this.loading2 = false;
            }, 3000);
            
            this.$refs.proof_of_payment.value = ''            
        },
    }
    
}
</script>