<template>
    <section ref="course_eval">
        <section v-if="!showPreview" >
            <v-sheet class="pa-5 custom-border border mb-3">
                <section class="d-flex flex-row align-center">
                    <Tabs id="course-evaluation" :value.sync="tab" :tabs="items"/>
                    <v-spacer v-if="tab === 1"></v-spacer>
                    <section v-if="tab === 1">
                        <section v-if="$vuetify.breakpoint.smAndUp" class="d-flex flex-row align-center">
                            <v-btn dense text class="f12 secondary-1--text" @click="previewEvaluation">
                                <v-icon class="mr-1">mdi-eye-outline</v-icon>
                                Preview
                            </v-btn>
                            <v-btn dense text class="f12 secondary-1--text mx-1" @click="importQuestionDialog = true" v-if="!$isReadOnly.isReadOnly(status, permission)">
                                <v-icon class="mr-1">mdi-file-import-outline</v-icon>
                                Import Questions
                            </v-btn>
                            <v-btn dense text class="f12 secondary-1--text" @click="addQuestion" v-if="!$isReadOnly.isReadOnly(status, permission)">
                                <v-icon class="mr-1">mdi-plus-circle-outline</v-icon>
                                Question
                            </v-btn>
                            <!-- <v-btn dense text class="f12 secondary-1--text"  @click="addTopic">
                                <v-icon class="mr-1">mdi-card-text-outline</v-icon>
                                Topic
                            </v-btn> -->
                        </section>
                        <section v-else>
                            <v-menu offset-y>
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn
                                        icon
                                        v-bind="attrs"
                                        v-on="on"
                                    >
                                        <v-icon>
                                            mdi-dots-vertical
                                        </v-icon>
                                    </v-btn>
                                </template>
                                <v-list class="f14 secondary-1--text primary-font">
                                    <v-list-item @click="previewEvaluation" class="d-flex align-center">
                                        <v-icon class="mr-1">mdi-eye-outline</v-icon>
                                        Preview
                                    </v-list-item>
                                    <v-list-item @click="addQuestion" class="d-flex align-center" v-if="!$isReadOnly.isReadOnly(status, permission)">
                                        <v-icon class="mr-1">mdi-plus-circle-outline</v-icon>
                                        Question
                                    </v-list-item>
                                    <!-- <v-list-item class="d-flex align-center" @click="addTopic">
                                        <v-icon class="mr-1">mdi-card-text-outline</v-icon>
                                        Topic
                                    </v-list-item> -->
                                </v-list>
                            </v-menu>
                        </section>
                    </section>
                </section>
                <v-tabs-items v-model="tab" style="background-color: transparent"  id="main-container" v-if="!loading">
                    <v-tab-item v-for="(item, i) in items" :key="i" class="">
                        <EvaluationSettings 
                            v-if="item === 'Settings'" 
                            :evaluation="evaluation" 
                            @updateEvaluationSettings="updateEvaluationSettings"
                            :status="status"
                            :permission="permission"/>
                    </v-tab-item>
                </v-tabs-items>
                <circular v-else />
            </v-sheet>
            <LinearProgress v-if="loading2" />
            <section v-if="tab === 1">
                <draggable
                        v-model="questions"
                        ghost-class="ghost"
                        :disabled="$vuetify.breakpoint.mobile ? true : $isReadOnly.isReadOnly(status, permission)"
                        v-if="questions.length > 0" 
                        @end="sortCourseEvaluationQuestions(questions)"
                    >
                    <transition-group type="transition" name="flip-list">
                        <section v-for="(question, i) in questions" :key="question.id" class="py-2 mt-3">
                            <v-expansion-panels
                                    flat
                                    accordion
                                    class="sortable">
                                <v-expansion-panel class="custom-border border">
                                    <v-expansion-panel-header class="align-end">
                                        <div class="col-12 ma-0 pa-0 d-flex flex-row">
                                            <div class="col-11 pa-0">
                                                <EvaluationQuestionHeader 
                                                    @setSurveyType="setSurveyType" 
                                                    :panel_key="i" 
                                                    :evaluation="question" 
                                                    :status="status"
                                                    :permission="permission"/>
                                            </div>
                                            <div class="col-1 px-0">
                                                <div class="d-flex justify-end">
                                                    <v-btn icon @click.stop="removeQuestion(question)" v-if="!$isReadOnly.isReadOnly(status, permission)">
                                                    <v-icon size="18">mdi-trash-can-outline</v-icon>
                                                    </v-btn>
                                                </div>
                                            </div>
                                        </div>
                                    </v-expansion-panel-header>
                                    <v-expansion-panel-content>
                                        <EvaluationQuestionBuilder 
                                            :id="question.id" 
                                            :index="i" 
                                            :type_of_question="question.type_of_question" 
                                            :question="question" 
                                            @addChoice="addChoice" 
                                            @removeChoice="removeChoice" 
                                            @changeChoice="changeChoice"
                                            :status="status"
                                            :permission="permission"/>
                                    </v-expansion-panel-content>
                                </v-expansion-panel>
                            </v-expansion-panels>
                        </section>
                    </transition-group>
                </draggable>

                <section>
                    <v-btn dense color="primary" @click="saveQuestions" v-if="!$isReadOnly.isReadOnly(status, permission)">
                        SAVE
                    </v-btn>
                </section>
                <v-btn
                    absolute
                    fab
                    fixed
                    bottom
                    right
                    class="mb-15 mr-15"
                    color="primary" 
                    elevation="2"
                    @click="addQuestion"
                    v-if="!$isReadOnly.isReadOnly(status, permission)"
                    >
                    <v-icon>mdi-plus-circle-outline</v-icon>
                </v-btn>
                <v-btn
                    absolute
                    fab
                    fixed
                    bottom
                    right
                    class="mb-15"
                    color="primary" 
                    elevation="2"
                    @click="goto('course_eval')"
                    >
                    <v-icon>mdi-chevron-up</v-icon>
                </v-btn>
            </section>
        </section>
        <section v-if="showPreview" >
            <EvaluationPreview @close="showPreview = false" :evaluation="evaluation"/> 
        </section>
        <delete-toast 
            :dialog="deletedialog"
            type="Evaluation Question"
            @cancel="deletedialog=false"
            @delete="deleteQuestion"
        />
        <ImportQuestionPoolDialog 
            :dialog="importQuestionDialog"
            :type_of_question_pool="'evaluation'"
            :submit_loading="submitLoading"
            @close="importQuestionDialog = false"
            @submit="assignQuestionPool"
        />
    </section>
</template>

<script>
import draggable from "vuedraggable";
import { mapActions, mapState, mapMutations } from "vuex";
import EvaluationSettings from "../evaluation/EvaluationSettings.vue";
import EvaluationQuestionHeader from "../evaluation/EvaluationQuestionHeader.vue";
import EvaluationQuestionBuilder from "../evaluation/EvaluationQuestionBuilder.vue";
import EvaluationPreview from "../evaluation/EvaluationPreview.vue";
import ImportQuestionPoolDialog from "../questionpool/ImportQuestionPoolDialog.vue";

export default {
    props: ['evaluation', 'loading', "status", "permission"],
    components: {
        draggable,
        EvaluationSettings,
        EvaluationQuestionHeader,
        EvaluationQuestionBuilder,
        EvaluationPreview,
        ImportQuestionPoolDialog
    },
    data: () => ({
        items: ['Settings', 'Form'],
        tab: 0,
        panel: [],
        showPreview: false,
        deletedialog: false,
        itemToRemove: null,
        loading2: false,
        submitLoading: false,
        importQuestionDialog: false
    }),
    computed: {
    
        questions: {
            get() {
                return this.$store.state["instructor"].questions;
            },
            set(val) {
                return this.$store.commit("instructor/evaluationQuestionMutation", val);
            },
        }
    },
    methods: {
        ...mapActions("instructor", [ 'updateCourseEvaluationAction', 'addCourseEvaluationQuestionAction', 'updateCourseEvaluationQuestionAction', 'deleteCourseEvaluationQuestionAction', 'sortCourseEvaluationQuestionsAction', 'assignQuestionPoolToCourseEvaluationAction']),

        ...mapMutations(["alertMutation"]),

        ...mapMutations("instructor", ["evaluationQuestionMutation"]),

        assignQuestionPool(ids) {
            this.submitLoading = true
            this.assignQuestionPoolToCourseEvaluationAction({
                question_pool_ids: ids,
                course_evaluation_id: this.evaluation.id,
            }).then(() => {
                this.submitLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Questions successfully imported.',
                    type: "success"
                })
                this.importQuestionDialog = false
                
                this.loading2 = true

                this.$api.get(`instructor/course/evaluations/${this.evaluation.id}`).then( res => {
                    this.evaluationQuestionMutation(res.data.data.course_evaluation_questions)
                    this.loading2 = false
                }).catch(() => {
                    this.loading2 = false
                })
            }).catch(() => {
                this.submitLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
                this.importQuestionDialog = false
            })
        },

        updateEvaluationSettings(item) {
            this.updateCourseEvaluationAction(item).then(() => {
                this.alertMutation({
                    show: true,
                    text: 'Course Evaluation updated.',
                    type: "success"
                })
            })
        },

        previewEvaluation(){
            this.showPreview = true
        },

        addQuestion() {
            this.addCourseEvaluationQuestionAction({
                state: this.questions,
                data: {
                    course_evaluation_id: this.evaluation.id,
                    question: null,
                    type_of_question: 'multiple_choice',
                    is_require_to_answer: false,
                    data: {choice_0: ""}
                }
            }).then(() => {
                this.alertMutation({
                    show: true,
                    text: 'Successfully added a new question.',
                    type: "success"
                })

            })
        },

        changeChoice(e){
            let { index, new_choices } = e
            this.questions[index].data = new_choices
            this.evaluationQuestionMutation(this.questions)
        },

        setSurveyType(e) {
            let { id, type, panel_key } = e
            let index = this.questions.findIndex(item => item.id === id)
            let _question = this.questions

            if(!this.panel.includes(panel_key)){
                this.panel.push(panel_key)
            }

            if(index >= 0){
                _question[index].type_of_question = type

                if(type === 'linear_scale') {
                    let _data = {
                        scale_1: '',
                        scale_5: ''
                    }
                    this.questions[index].data = _data

                } else if(type === 'multiple_choice_grid' || type === 'checkbox_grid') {
                    this.questions[index].data = {
                        row_0: '',
                        row_1: '',
                        row_2: '',
                        col_0: '',
                        col_1: '',
                        col_2: '',
                    }
                } else {
                    this.questions[index].data = {}
                }

                this.evaluationQuestionMutation(this.questions)
            }
        },
        
        addChoice(e) {
            let { id, name } = e
            let index = this.questions.findIndex(e => e.id === id)
            if(index >= 0){
                if(name) {
                    this.questions[index].data = {...this.questions[index].data, [name]: ''}
                } else {
                    if(this.questions[index].data.length === 0 ) {
                        let _choice = {
                            choice_0: ''
                        }
                        this.questions[index].data = _choice
                    } else {
                        if(this.questions[index].type_of_question === 'multiple_choice_grid' || this.questions[index].type_of_question === 'checkbox_grid') {
                            this.questions[index].data = {...this.questions[index].data, [name]: ''}
                        } else {
                            this.questions[index].data = {...this.questions[index].data, [`choice_${Object.keys(this.questions[index].data).length }`]: ''}
                        }
                    }
                }

                this.evaluationQuestionMutation(this.questions)
            }
        },

        removeChoice(e) {
            let { id, index } = e
            let counter = 0
            let _index = this.questions.findIndex(e => e.id === id)

            if(_index >= 0){
                if(this.questions[_index].type_of_question !== 'multiple_choice_grid' && this.questions[_index].type_of_question !== 'checkbox_grid'){
                    delete this.questions[_index].data[`choice_${index}`]

                    let _choices = {}

                    for(let choice in this.questions[_index].data) {
                        _choices[`choice_${counter}`] = this.questions[_index].data[choice]
                        counter++
                    }

                    this.questions[_index].data = _choices
                    this.evaluationQuestionMutation(this.questions)
                } else {
                    delete this.questions[_index].data[`${index}`]

                    let _choices = {}

                    counter = 0

                    Object.keys(this.questions[_index].data).filter(item => item.includes(`${index.split('_')[0]}`)).forEach(item => {
                        _choices[`${index.split('_')[0]}_${counter}`] = this.questions[_index].data[item]
                        counter++
                    })

                    Object.keys(this.questions[_index].data).filter(item => !item.includes(`${index.split('_')[0]}`)).forEach(item => {
                        _choices[item] = this.questions[_index].data[item]
                    })

                    this.questions[_index].data = _choices

                    this.evaluationQuestionMutation(this.questions)
                }
            }
        },

        saveQuestions(){
            let payload = {
                course_evaluation_id: [],
                course_evaluation_question_id: [],
                question: [],
                type_of_question: [],
                is_require_to_answer: [],
                data: [],
                sort_no: []
            }
            this.questions.forEach((item, i) => {
                payload.course_evaluation_id.push(item.course_evaluation_id)
                payload.course_evaluation_question_id.push(item.id)
                payload.question.push(item.question)
                payload.type_of_question.push(item.type_of_question)
                payload.is_require_to_answer.push(item.is_require_to_answer)
                payload.data.push(item.data)
                payload.sort_no.push(item.sort_no)
            }) 
            this.updateCourseEvaluationQuestionAction(payload).then(() => {
                this.alertMutation({
                    show: true,
                    text: 'Course Evaluation Questions updated.',
                    type: 'success'
                })
            }).catch(() => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: 'error'
                })
            })
        },

        removeQuestion(payload){
            this.itemToRemove = payload
            this.deletedialog = true
        },

        deleteQuestion() {
            this.deleteCourseEvaluationQuestionAction({state: this.questions, data: this.itemToRemove}).then(() => {
                this.deletedialog = false
                this.alertMutation({
                    show: true,
                    text: 'Course Evaluation Question deleted.',
                    type: 'success'
                })
            })
        },

        goto(name) {
            var element = this.$refs[name];
            var top = element.offsetTop - 40;
            window.scrollTo({ left: 0, top: top, behavior: 'smooth' });
        },

        sortCourseEvaluationQuestions(items){
            let payload = {
                course_evaluation_id: [],
                course_evaluation_question_id: [],
                question: [],
                type_of_question: [],
                is_require_to_answer: [],
                data: [],
                sort_no: []
            }
            this.questions.forEach((item, i) => {
                payload.course_evaluation_id.push(item.course_evaluation_id)
                payload.course_evaluation_question_id.push(item.id)
                payload.question.push(item.question)
                payload.type_of_question.push(item.type_of_question)
                payload.is_require_to_answer.push(item.is_require_to_answer)
                payload.data.push(item.data)
                payload.sort_no.push(item.sort_no)
            }) 
            this.updateCourseEvaluationQuestionAction(payload).then(() => {
                let _ids = [];
                items.forEach((el) => {
                    _ids.push(el.id);
                });
                
                this.loading2 = true
                this.sortCourseEvaluationQuestionsAction({
                    course_evaluation_id: this.evaluation.id,
                    course_evaluation_question_id: _ids
                }).then(() => {
                    this.alertMutation({
                        show: true,
                        text: 'Successfully sorted questions',
                        type: "success"
                    })
                    this.loading2 = false
                }).catch(() => {
                    this.loading2 = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong in sorting questions',
                        type: "error"
                    })
                })
            }).catch(() => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in sorting questions',
                    type: "error"
                })
            })
        }
        
    }
};
</script>
