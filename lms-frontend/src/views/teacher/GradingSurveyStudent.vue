<template>
    <section v-if="!loading">
        <v-btn text color="primary" class=" mb-5"
            @click="(prevRoute && prevRoute.name === 'Instructor Survey') ? $router.back() : $router.push({ name: 'Instructor Survey' , params: { type: $route.params.type, id: $route.params.id, survey_id: $route.params.survey_id}, query: { search: '', page: 1, paginate: 10 }})" >
            BACK
        </v-btn>
        <v-sheet class="custom-border border mb-5">
            <!-- <v-row class="pa-7">
                <v-col>
                    <label class="poppins f13 secondary-2--text fw500">USER</label>
                    <v-select 
                        hide-details
                        dense
                        :items="questions"
                        item-value="id"
                        v-model="user">
                    </v-select>
                </v-col>
            </v-row>
            <v-divider /> -->
            <section class="pa-5">
                <div class="d-flex align-center justify-space-between px-5">
                    <div class="d-flex flex-wrap">
                        <div class="mr-16 d-flex align-center">
                                <h1 class="roboto fw600">
                                    {{ student.name }}
                                </h1>
                            </div>
                            <div class="d-flex flex-wrap align-start">
                                <div class="my-2" :class="($vuetify.breakpoint.mdAndDown || $vuetify.breakpoint.mobile) ? 'mr-10' : ''">
                                    <div class="poppins f13 secondary-2--text fw500">DATE ACCOMPLISHED</div>
                                    <div class="roboto f15 fw500">{{ student.date_submitted }}</div>
                                </div>
                                <v-divider vertical class="mx-5"  v-if="!$vuetify.breakpoint.mdAndDown || !$vuetify.breakpoint.mobile" />
                                <div class="my-2" :class="($vuetify.breakpoint.mdAndDown || $vuetify.breakpoint.mobile) ? 'mr-10' : ''">
                                    <div class="poppins f13 secondary-2--text fw500">NO. OF ATTEMPTS<div>
                                        <div class="roboto f15 fw500">{{ student.attempts }}</div>
                                    </div>
                                </div>
                                </div>
                                <v-divider vertical class="mx-5"  v-if="!$vuetify.breakpoint.mdAndDown || !$vuetify.breakpoint.mobile" />
                                <div class="my-2">
                                    <div class="roboto f13 secondary-2--text fw500">STATUS<div>
                                        <v-select
                                            outlined
                                            :items="items"
                                            item-text="text"
                                            item-value="value"
                                            width="100"
                                            class="no-outline-custom-field poppins f15 fw500 p-0 m-0"
                                            dense
                                            hide-details
                                            v-model="student.status"
                                            v-on:change="updateStatus"
                                            :style="'width: min-content; padding: 0px; margin: 0px'"
                                        >
                                            <template v-slot:item="data">
                                                <span v-if="data.item.value === 1" class="success--text">CHECKED</span>
                                                <span v-if="data.item.value === 0" class="secondary-1--text">PENDING</span>
                                            </template>
                                            <template slot="selection"  slot-scope="data">
                                                <v-alert v-if="student.status === 1" dense color="#7BC14533" class="success--text" :style="'width: min-content; padding: 3px; margin: 0px'">CHECKED</v-alert>
                                                <v-alert v-if="student.status === 0" dense color="#BDBDBD33" class="secondary-2--text" :style="'width: min-content; padding: 3px; margin: 0px'">PENDING</v-alert>
                                            </template>
                                        </v-select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </v-sheet>
        <IndividualEvaluation :evaluation="evaluation" :questions="questions"/>
    </section>
    <circular v-else />
</template>

<style scoped>
    .v-text-field.v-text-field--enclosed:not(.v-text-field--rounded) > .v-input__control > .v-input__slot, .v-text-field.v-text-field--enclosed .v-text-field__details {
        padding: 0px !important;
    }
</style>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'
import IndividualEvaluation from '@/components/teacher/survey/IndividualEvaluation.vue';

export default {
    components: {
        IndividualEvaluation
    },
    mounted(){
        this.getQuestions()

        this.loading = true
        if(this.$route.params.type === 'course_evaluation'){
            this.$api.get(`/instructor/course/evaluation/${this.$route.params.survey_id}/respondent/${this.$route.params.student_id}/attempts`).then(res => {
                let _user = res.data.data
                this.student = {
                    ...this.student,
                    name: `${_user.first_name ? _user.first_name : 'User'} ${_user.last_name ? _user.last_name : 'Account'}`,
                    attempts: _user.student_course_evaluations.length,
                }
                if(_user.student_course_evaluations.length > 0) {
                    let last_attempt = _user.student_course_evaluations[_user.student_course_evaluations.length-1]
                    this.student = {
                        ...this.student,
                        date_submitted: this.$dateFormat.mmDDyy(last_attempt.created_at),
                        status: last_attempt.is_checked,
                    }
                    this.$api.get(`/instructor/course/evaluation/attempt/${last_attempt.id}/respondent/${this.$route.params.student_id}/answers`).then(res => {
                        this.evaluation = res.data.data.student_course_evaluations[0]
                        this.evaluationAnswersMutation(this.evaluation.answers)
                    }).finally(() => {
                        this.loading = false
                    })
                } else {
                    this.loading = false
                }
            })
        }
        if(this.$route.params.type === 'module_evaluation') {
            this.$api.get(`/instructor/module/evaluation/${this.$route.params.survey_id}/respondent/${this.$route.params.student_id}/attempts`).then(res => {
                let _user = res.data.data
                this.student = {
                    ...this.student,
                    name: `${_user.first_name ? _user.first_name : 'User'} ${_user.last_name ? _user.last_name : 'Account'} ${_user.suffix ? _user.suffix : ''}`,
                    attempts: _user.student_module_evaluations.length,
                }
                if(_user.student_module_evaluations.length > 0) {
                    let last_attempt = _user.student_module_evaluations[_user.student_module_evaluations.length-1]
                    this.student = {
                        ...this.student,
                        date_submitted: this.$dateFormat.mmDDyy(last_attempt.created_at),
                        status: last_attempt.is_checked,
                    }
                    this.$api.get(`/instructor/module/evaluation/attempt/${last_attempt.id}/respondent/${this.$route.params.student_id}/answers`).then(res => {
                        this.evaluation = res.data.data.student_module_evaluations[0]
                        this.evaluationAnswersMutation(this.evaluation.answers)
                    }).finally(() => {
                        this.loading = false
                    })
                } else {
                    this.loading = false
                }
            })
        }
    },
    data: () => ({
        user: null,
        student: {
            name: 'User Account',
            date_submitted: '-',
            attempts: '-',
            status: '-',
        },
        evaluation: null,
        loading: false,
        items: [
            { text: 'CHECKED', value: 1 },
            { text: 'PENDING', value: 0 },
        ],
        prevRoute: null,
    }),
    methods: {
        ...mapActions('instructor', ['getCourseEvaluationQuestionsAction', 'getModuleEvaluationQuestionsAction']),
        ...mapMutations('instructor', ['evaluationAnswersMutation']),

        updateStatus() {
            if(this.$route.params.type === 'course_evaluation'){
                this.$api.put(`instructor/course/evaluation/attempt/${this.evaluation.answers[0].student_course_evaluation_id}/check`).then(res => {
                    console.log(res.data.data)
                })
            }
            if(this.$route.params.type === 'module_evaluation'){
                this.$api.put(`instructor/module/evaluation/attempt/${this.evaluation.answers[0].student_module_evaluation_id}/check`).then(res => {
                    console.log(res.data.data)
                })
            }
        },

        getQuestions() {
            this.loading = true
            if(this.$route.params.type === 'course_evaluation') {
                this.getCourseEvaluationQuestionsAction(this.$route.params.survey_id).then(() => {
                }).finally(() => {
                    this.loading = false
                })
            }
            if(this.$route.params.type === 'module_evaluation') {
                this.getModuleEvaluationQuestionsAction(this.$route.params.survey_id).then(() => {
                }).finally(() => {
                    this.loading = false
                })
            }
        }
    },
    computed: {
        ...mapState('instructor', {
            questions: (state) => state.questions,
            evaluation_answers: (state) => state.evaluation_answers,
        }),
    },

    beforeRouteEnter(to, from, next) {
        next(vm => {
            vm.prevRoute = from          
        })
    },
}
</script>