<template>
    <div>
        <div class="overflow-x-hidden" :class="form ?  '' : 'pa-5'">
            <div fluid class=" primary-font fw600 f16" >
                <label class="caption" v-if="form">UPLOAD PRIME RESOLUTION</label>
                <section class="primary-font primary--text fw600 f17" v-else>
                    Upload PRIME Resolution
                </section>
                <v-container
                    class="bulk-border mt-2 d-flex flex-column justify-center"
                    @dragover="dragover"
                    @dragleave="dragleave"
                    @drop="dropPrimeResolution"
                >
                    <div class="d-flex text-center flex-column">
                        <v-icon color="rgba(63, 93, 208, 0.12)" size="68"
                            >mdi-cloud-upload-outline</v-icon
                        >
                        <div class="secondary--text f12 poppins">
                            <a
                            href="#"
                            @click="$refs.prime_resolution.click()"
                            class="primary--text text-decoration-none"
                            >
                            Browse
                            </a>
                            or drag file here ( .pdf, .doc, .docx )
                        </div>
                    </div>
                </v-container>
                <v-container
                    v-if="primeResolution"
                    class="d-flex justify-space-between align-center my-3">
                    <div style="width: 250px;">
                        <div class="roboto fw600 f12" style="margin-bottom: -8px">
                            {{ primeResolution.name }}
                        </div>
                        <span class="roboto fw500 f10 secondary--text">
                            {{ (primeResolution.size / 1000).toFixed(2) }} KB
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
            <div fluid class="primary-font fw600 f16" :class="form ? 'mt-4' : 'mt-9'">
                <label class="caption" v-if="form">UPLOAD ENDORSMENT LETTER</label>
                <section class="primary-font primary--text fw600 f17" v-else>
                    Upload Endorsement Letter
                </section>
                <v-container
                    class="bulk-border mt-2 d-flex flex-column justify-center"
                    @dragover="dragover"
                    @dragleave="dragleave"
                    @drop="dropEndorsementLetter"
                >
                    <div class="d-flex text-center flex-column">
                        <v-icon color="rgba(63, 93, 208, 0.12)" size="68"
                            >mdi-cloud-upload-outline</v-icon
                        >
                        <div class="secondary--text f12 poppins">
                            <a
                            href="#"
                            @click="$refs.endorsement_letter.click()"
                            class="primary--text text-decoration-none"
                            >
                            Browse
                            </a>
                            or drag file here ( .pdf, .doc, .docx )
                        </div>
                    </div>
                </v-container>
                <v-container
                    v-if="endorsementLetter"
                    class="d-flex justify-space-between align-center my-3">
                    <div style="width: 250px;">
                        <div class="roboto fw600 f12" style="margin-bottom: -8px">
                            {{ endorsementLetter.name }}
                        </div>
                        <span class="roboto fw500 f10 secondary--text">
                            {{ (endorsementLetter.size / 1000).toFixed(2) }} KB
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
                    <v-btn icon small @click="remove" v-if="!submitLoading">
                        <v-icon size="18" color="danger-1">mdi-close</v-icon>
                    </v-btn>
                </v-container>
            </div>
            <alert 
                v-if="msg2.show"
                :show = "msg2.show"
                :text = "msg2.text"
                :type = "msg2.type"
                class="mx-3"
            />
            
<!--                 
            <v-row dense class="mt-3">
                <v-col>
                    <v-btn text block class="roboto" @click="() => {
                        remove(0)
                        remove(1)
                        $emit('back')
                    }">
                        Back
                    </v-btn>
                </v-col>
                <v-col>
                    <v-btn color="primary" block :disabled="(!loading1 && !loading2) ? (!primeResolution && !endorsementLetter) : true" class="roboto"
                    @click="submitForm()" :loading="submitLoading">
                        Submit
                    </v-btn>
                </v-col>
            </v-row> -->

            <input
                type="file"
                ref="prime_resolution"
                class="d-none"
                accept=".docx,.doc,.pdf"
                @change="acceptPrimeResolution"
            />
            <input
                type="file"
                ref="endorsement_letter"
                class="d-none"
                accept=".docx,.doc,.pdf"
                @change="acceptEndorsementLetter"
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
        error_prime_resolution: {
            default: '',
            type: String
        },
        error_endorsement_letter: {
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
        primeResolution: null,
        endorsementLetter: null,
        loading1: false,
        loading2: false,
        submitLoading: false,
        msg1: {
                show: false,
                type: '',
                text: ''
        },
        msg2: {
                show: false,
                type: '',
                text: ''
        },
    }),
    computed: {
        ...mapState({
            errors: (state) => state.errors,
        }),
    },
    watch: {
        error_prime_resolution(val) {
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
        error_endorsement_letter(val) {
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
    methods: {
        ...mapActions('usr', ['courseCheckoutPRIME']),
        ...mapMutations(['alertMutation']),

        remove(index){
            if(index === 0) {
                this.primeResolution = ''
                this.$refs.prime_resolution.value = ''
                this.$emit('setPrimeResolution', null)
            } else {
                this.endorsementLetter = ''
                this.$refs.endorsement_letter.value = ''
                this.$emit('setEndorsementLetter', null)
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

        dropPrimeResolution(e) {
            e.preventDefault();
            this.$refs.prime_resolution.files = e.dataTransfer.files;
            this.acceptPrimeResolution();
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        dropEndorsementLetter(e) {
            e.preventDefault();
            this.$refs.endorsement_letter.files = e.dataTransfer.files;
            this.acceptEndorsementLetter();
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        submitForm(){
            this.submitLoading = true

            this.msgs = [
                {
                    show: false,
                    type: '',
                    text: ''
                },
                {
                    show: false,
                    type: '',
                    text: ''
                }
            ]

            let _form = new FormData()

            _form.append('course_id', this.course.id)
            _form.append('prime_resolution', this.primeResolution)
            _form.append('endorsement_letter', this.endorsementLetter)
            _form.append('type_of_payment', this.type_of_payment)
            _form.append('type_of_participant', this.type_of_participant)

            this.courseCheckoutPRIME(_form).then(() => {
                this.remove(0)
                this.remove(1)
                this.$emit('close')
                this.$emit('success')
            }).catch(res => {
                if(this.errors.prime_resolution) {
                    this.msgs[0] = {
                        show: true,
                        type: 'error',
                        text: this.errors.prime_resolution[0]
                    }
                }
                if(this.errors.endorsement_letter) {
                    this.msgs[1] = {
                        show: true,
                        type: 'error',
                        text: this.errors.endorsement_letter[0]
                    }
                }
                if(!this.errors.prime_resolution && !this.errors.endorsement_letter) {
                    this.msgs[1] = {
                        show: true,
                        type: 'error',
                        text: res.response.data.message
                    }
                }
            }).finally(() => {
                this.submitLoading = false
            })
        },

        acceptPrimeResolution() {
            this.msg1 = {
                show: false,
                type: '',
                text: ''
            }

            this.primeResolution = this.$refs.prime_resolution.files[0]
            this.$emit('setPrimeResolution', this.$refs.prime_resolution.files[0])

            this.loading1 = true;
            setTimeout(() => {
                this.loading1 = false;
            }, 3000);
            
            this.$refs.prime_resolution.value = ''            
        },

        acceptEndorsementLetter() {
            this.msg2 = {
                show: false,
                type: '',
                text: ''
            }

            this.endorsementLetter = this.$refs.endorsement_letter.files[0]
            this.$emit('setEndorsementLetter', this.$refs.endorsement_letter.files[0])
            this.loading2 = true;
            setTimeout(() => {
                this.loading2 = false;
            }, 3000);
            
            this.$refs.endorsement_letter.value = ''            
        },
    }
    
}
</script>