<template>
    <div>
        <div class="d-flex flex-row justify-space-between mt-1">
            <!-- <div class="italic f10 pa-2" v-if="$route.name === 'User Course Content'">Reminder: Please note that only the first attempts for activities will be accepted. Make sure to review your work carefully before submitting.</div> -->
            <v-progress-circular
                indeterminate
                v-if="loading || progressLoading"
                width="3"
                size="22"
                class=" mt-1"
                color="primary"/>
        </div>
        <iframe 
            ref="scormIframe"
            id="scorm-iframe"
            :src="scormIframeSrc"
            class="responsive-iframe"
            allowfullscreen
            webkitallowfullscreen="true"
            mozallowfullscreen="true"
        />  
    </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'

export default {
    props: {
        url: {
            default: '',
            type: String
        },
        dialog: {
            default: false,
            type: Boolean
        },
        course_id: {
            default: null,
            type: Number || null
        },
        type: {
            default: '',
            type: String
        },
        model_id: {
            default: null,
            type: Number || null
        },
        data: {
            default: null,
            type: Array || null
        },
        progress: {
            default: null,
            type: Object || null
        }
    },
    data: () => ({
        s3_path: `${process.env.VUE_APP_AWS_S3}`,
        scorm_url: '',
        messageListener: null,
        onloadListener: null,
        scorm_data: null,
        loading: false,
        progressLoading: false,
        passingScore: 0,
        scormIframeSrc: ''
    }),
    watch: {
        dialog(newVal) {
            if (!newVal) {
                this.removeMessageListener();
            } else {
                this.addMessageListener();
            }
        }
    },
    mounted() {
        console.log('mounted')
        this.addMessageListener();
        this.delayIframeSrc();
    },
    methods: {
        
        ...mapMutations(['alertMutation']),
        ...mapActions('usr', ['submitLearnerScormAnswer', 'submitLearnerScormScore', 'submitLearnerScormProgress']),


        addMessageListener() {
            this.scorm_url = this.url
            this.messageListener = (event) => {
                if (event.origin !== this.s3_path) return;
                if (event.data.type === 'reportAnswer') {
                    if(this.$route.name === 'User Course Content') {
                        this.sendLearnerAnswer(event.data.data)
                    }
                }
                if (event.data.type === 'concatLMSData') {
                    localStorage.setItem('scorm_course_progress', JSON.stringify(event.data.data))
                    if(this.$route.name === 'User Course Content') {
                        this.saveLearnerProgress({
                            scorm_course_progress: JSON.stringify(event.data.data),
                        })
                    }
                }
                if (event.data.type === 'finishQuiz') {
                    if(this.$route.name === 'User Course Content') {
                        this.sendLearnerQuizScore(event.data.data)
                    }
                }
                if (event.data.type === 'passingScore') {
                    if(this.$route.name === 'User Course Content') {
                        console.log('received passingscore')
                        this.passingScore = event.data.data
                        console.log(this.passingScore)
                    }
                }
                if (event.data === 'SetReachedEnd') {
                    this.$emit('end');
                }
                if (event.data.type === 'ready') {
                    const iframe = document.getElementById('scormIframe');
                    // setTimeout(() => {
                    //     iframe.contentWindow.postMessage({
                    //         scorm_course_progress: window.localStorage.scorm_course_progress,
                    //     }, this.s3_path);
                    // }, 2);
                }
            };
            window.addEventListener("message", this.messageListener);
        },

        removeMessageListener() {
            if (this.messageListener) {
                this.scorm_url = ''
                window.removeEventListener("message", this.messageListener);
                this.messageListener = null;
            }
        },

        sendLearnerAnswer(data){
            this.loading = true
            this.submitLearnerScormAnswer({
                course_id: this.course_id,
                activity_id: data.type === 'QA_SECTION' ? null : data.id,
                type: this.type,
                model_id: this.model_id,
                title: data.quizTitle,
                question_uuid: data.itemId,
                question: data.questionTitle,
                type_of_question: data.type,
                choices: data.answers,
                correct_answers: data.correctResponse,
                answer: data.response,
                is_correct: data.isCorrect,
                passing_score: data.type === 'QA_SECTION' ? 0 : this.passingScore
            }).then(res => {
                this.scorm_data = res
                this.loading = false
                this.alertMutation({
                    show: true,
                    text: 'Your answer has been submitted',
                    type: "success"
                })
            }).catch(res => {
                this.loading = false
                if(res.response?.status === 422) {
                    this.alertMutation({
                        show: true,
                        text: "You've already answered this activity",
                        type: "success"
                    })
                    this.scorm_data = res.response?.data
                } else {
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong in submitting your answer',
                        type: "error"
                    })
                }
            })
        },

        sendLearnerQuizScore(data){
            this.loading = true
            this.submitLearnerScormScore({
                course_id: this.course_id,
                scorm_activity_id: this.scorm_data.id,
                score: data,
                passed: data.passed
            }).catch(() => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in saving your score',
                    type: "error"
                })
            }).finally(() => {
                this.loading = false
            })
        },

        saveLearnerProgress(data){
            if(!this.progressLoading) {
                this.progressLoading = true
                this.submitLearnerScormProgress({
                    course_id: this.course_id,
                    type: this.type,
                    model_id: this.model_id,
                    model_type: this.type,
                    ...data
                }).catch(() => {
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong in saving your progress',
                        type: "error"
                    })
                }).finally(() => {
                    this.progressLoading = false
                })

            }
        },
        
        delayIframeSrc() {
            // If no progress or the route is not 'User Course Content', load iframe without progress
            if (!this.progress || this.$route.name !== 'User Course Content') {
                this.scormIframeSrc = `${this.scorm_url}`;
                return;
            }

            const scormProgress = this.progress.scorm_course_progress;
            localStorage.setItem('scorm_course_progress', scormProgress)

            // Delay the loading of the iframe with progress for 2 seconds
            setTimeout(() => {
                this.scormIframeSrc = `${this.scorm_url}?scorm_content_progress=${scormProgress ? encodeURIComponent(scormProgress) : ''}`;
            }, 2000); // Delay by 2 seconds
        }

    },
    beforeDestroy(){
        this.removeMessageListener()
        localStorage.removeItem('scorm_course_progress')
    },
    computed: {
        ...mapState({
            user: (state) => state.user,
        }),
        computedIframeSrc() {
            if(this.$route.name === 'User Course Content') {
                if(this.progress) {
                    const scormProgress = this.progress.scorm_course_progress;
                    // concat progress

                    
                    return `${this.scorm_url}?scorm_content_progress=${scormProgress ? encodeURIComponent(JSON.stringify(scormProgress)) : ''}`;
                }
            }

            return `${this.scorm_url}`;
        }
    },
};
</script>

<style>
    .container-frame {
        position: relative;
        overflow: hidden;
        width: 100%; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
    }

    #scorm-iframe {
        height: 70vh !important;
    }

    @media only screen and (max-width: 768px){
    #scorm-iframe {
        height: 54vh;
        width: 100%;
    }
}

</style>
  