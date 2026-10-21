<template>
    <div>
        <section class="overflow-x-hidden" :class="form ?  '' : 'pa-5'">
            <section class="d-flex flex-wrap justify-space-between align-center mb-5">
                <label class="caption" v-if="form">UPLOAD COMMITMENT LETTER</label>
                <section class="primary-font primary--text fw600 f17" v-else>
                    Upload Commitment Letter
                </section>
            </section>
            <div v-if="info">
                For bulk payments, provide a payment breakdown with participants list. Download and use the <a target="_blank" href="https://docs.google.com/document/d/1iIpKkaAFY6O9PBFF_yuc1e4FQ4xm-L2fqlFppHYdXm4/edit?usp=sharing">CL template</a>.
            </div>
            <div fluid class=" primary-font fw600 f16" >
                <v-container
                    class="bulk-border mt-2 d-flex flex-column justify-center"
                    @dragover="dragover"
                    @dragleave="dragleave"
                    @drop="dropCommitmentLetter"
                >
                    <div class="d-flex text-center flex-column">
                        <v-icon color="rgba(63, 93, 208, 0.12)" size="68"
                            >mdi-cloud-upload-outline</v-icon
                        >
                        <div class="secondary--text f12 poppins">
                            <a
                            href="#"
                            @click="$refs.commitment_letter.click()"
                            class="primary--text text-decoration-none"
                            >
                            Browse
                            </a>
                            or drag file here ( .pdf, .doc, .docx )
                        </div>
                    </div>
                </v-container>
                <v-container
                    v-if="commitmentLetter"
                    class="d-flex justify-space-between align-center my-3">
                    <div style="width: 250px;">
                        <div class="roboto fw600 f12" style="margin-bottom: -8px">
                            {{ commitmentLetter.name }}
                        </div>
                        <span class="roboto fw500 f10 secondary--text">
                            {{ (commitmentLetter.size / 1000).toFixed(2) }} KB
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
                v-if="msg.show"
                :show = "msg.show"
                :text = "msg.text"
                :type = "msg.type"
                class="mx-3"
            />            
                
            <!-- <v-row dense class="mt-3">
                <v-col>
                    <v-btn text block class="roboto" @click="() => {
                        remove(0)
                        $emit('back')
                    }">
                        Back
                    </v-btn>
                </v-col>
                <v-col>
                    <v-btn color="primary" block :disabled="!loading1 ? !commitmentLetter : true" class="roboto"
                    @click="submitCommitmentLetter()" :loading="submitLoading">
                        Submit
                    </v-btn>
                </v-col>
            </v-row> -->

            <input
                type="file"
                ref="commitment_letter"
                class="d-none"
                accept=".docx,.doc,.pdf"
                @change="acceptCommitmentLetter"
            />

        </section>
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
        type_of_participant: {
            default: '',
            type: String
        },
        type_of_payment: {
            default: '',
            type: String
        },
        error: {
            default: '',
            type: String
        },
        form: {
            default: false,
            type: Boolean
        },
        info: {
            default: true,
            type: Boolean
        }
    },
    watch: {
        error(val) {
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
    data: () => ({
        file: null,
        commitmentLetter: null,
        loading1: false,
        submitLoading: false,
        msg: {
            show: false,
            type: '',
            text: ''
        }
    }),
    computed: {
        ...mapState({
            errors: (state) => state.errors,
        }),
    },
    methods: {
        ...mapActions('usr', ['courseCheckoutCommitmentLetter']),
        ...mapMutations(['alertMutation']),

        remove(index){
            if(index === 0) {
                this.commitmentLetter = ''
                this.$emit('setCommitmentLetter', null)
                this.$refs.commitment_letter.value = ''
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

        dropCommitmentLetter(e) {
            e.preventDefault();
            this.$refs.commitment_letter.files = e.dataTransfer.files;
            this.acceptCommitmentLetter();
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        // submitCommitmentLetter(){
        //     this.submitLoading = true

        //     this.msgs = [
        //         {
        //             show: false,
        //             type: '',
        //             text: ''
        //         }
        //     ]

        //     let _form = new FormData()

        //     _form.append('course_id', this.course.id)
        //     _form.append('commitment_letter', this.commitmentLetter)
        //     _form.append('type_of_payment', this.type_of_payment)
        //     _form.append('type_of_participant', this.type_of_participant)

        //     this.courseCheckoutCommitmentLetter(_form).then(() => {
        //         this.remove(0)
        //         this.$emit('close')
        //         this.$emit('success')
        //     }).catch(res => {
        //         if(this.errors.commitment_letter) {
        //             this.msgs[0] = {
        //                 show: true,
        //                 type: 'error',
        //                 text: this.errors.commitment_letter[0]
        //             }
        //         }
        //         if(!this.errors.commitment_letter) {
        //             this.msgs[0] = {
        //                 show: true,
        //                 type: 'error',
        //                 text: res.response.data.message
        //             }
        //         }
        //     }).finally(() => {
        //         this.submitLoading = false
        //     })
        // },

        acceptCommitmentLetter() {
            this.msg = {
                show: false,
                type: '',
                text: ''
            }
            this.commitmentLetter = this.$refs.commitment_letter.files[0]
            this.$emit('setCommitmentLetter', this.$refs.commitment_letter.files[0])

            this.loading1 = true;
            setTimeout(() => {
                this.loading1 = false;
            }, 3000);
            
            this.$refs.commitment_letter.value = ''            
        },

    }
    
}
</script>