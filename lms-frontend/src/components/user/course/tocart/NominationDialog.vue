<template>
    <section>
        <v-dialog v-model="nominationDialog" persistent width="370">
            <v-card width="370" class="overflow-x-hidden">
                <section class="d-flex justify-end">
                    <v-btn icon @click="() => {
                        remove()
                        submitLoading = false
                        nominationDialog = false
                    }">
                        <v-icon>
                            mdi-close
                        </v-icon>
                    </v-btn>
                </section>
                <v-container fluid class="text-center primary-font fw600 f16" >
                    Upload nomination form
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
                            <div class="secondary--text f12 poppins">
                                <a
                                href="#"
                                @click="$refs.file.click()"
                                class="primary--text text-decoration-none"
                                >
                                Browse
                                </a>
                                or drag file here ( .pdf, .docx, .doc )
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
                        v-if="file"
                        class="d-flex justify-space-between align-center my-3">
                        <div style="width: 150px;">
                            <div class="roboto fw600 f12" style="margin-bottom: -8px">
                                {{ file.name }}
                            </div>
                            <span class="roboto fw500 f10 secondary--text">
                                {{ (file.size / 1000).toFixed(2) }} KB
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
                </v-container>
                <section class="d-flex flex-row justify-space-evenly pa-3">
                    <v-btn outlined small dense @click="() => {
                        remove()
                        nominationDialog = false
                    }">
                        GO BACK
                    </v-btn>
                    <v-spacer />
                    <v-btn small outlined dense color="success" :disabled="!loading ? !file : true" @click="submitNominationForm()" :loading="submitLoading">
                        SUBMIT
                    </v-btn>
                </section>
                <input
                    type="file"
                    ref="file"
                    class="d-none"
                    accept=".pdf,.docx,.doc"
                    @change="accept"
                />
            </v-card>
        </v-dialog>
        <v-dialog v-model="dialog" persistent width="370">
            <v-card v-if="!nominationDialog" width="370">
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
                        <v-btn outlined dense color="error" @click="$emit('save', null), $emit('close')">
                            NO
                        </v-btn>
                        <v-spacer />
                        <v-btn outlined dense color="success" @click="nominationDialog = true">
                            YES
                        </v-btn>
                    </v-container>
                </v-sheet>
            </v-card>
        </v-dialog>
    </section>
</template>

<script>
import Section from '@/views/admin/Section.vue'
import { mapActions, mapState, mapMutations } from "vuex"

export default {
  components: { Section },
    props: {
        dialog: Boolean,
        course: Object,
        nominationLoading: Boolean
    },
    data: () => ({
        nominationDialog: false,
        file: null,
        loading: false,
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
    watch: {
        nominationDialog() {
            this.msg = {
                show: false,
                type: '',
                text: ''
            }

            this.remove()
        }
    },
    methods: {
        ...mapActions('usr', ['courseCheckout']),

        remove(){
            this.file = ''
            this.$refs.file.value = ''
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

        submitNominationForm(){
            this.submitLoading = true

            this.msg = {
                show: false,
                type: '',
                text: ''
            }

            let _form = new FormData()

            _form.append('course_id', this.course.id)
            _form.append('nomination_form', this.file)

            this.courseCheckout(_form).then(() => {
                this.remove()
                this.nominationDialog = false
                this.$emit('success')
            }).catch(res => {
                if(this.errors.nomination_form) {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: this.errors.nomination_form[0]
                    }
                } else {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: res.response.data.message
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

            this.file = this.$refs.file.files[0]
            this.loading = true;
            setTimeout(() => {
                this.loading = false;
            }, 3000);
            
            this.$refs.file.value = ''            
        },
    }
    
}
</script>