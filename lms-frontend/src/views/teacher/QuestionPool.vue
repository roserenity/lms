<template>
    <section>
        <v-btn text class="poppins mb-3" color="primary" dense 
            @click="(prevRoute && prevRoute.name === 'Instructor Question Pools') ? $router.back() : $router.push({ name: 'Instructor Question Pools' , params: { id: $route.params.id }, query: { search: '', page: 1, paginate: 10 }})">
            <v-icon small>
                mdi-chevron-left
            </v-icon>
            Back
        </v-btn>
        <LinearProgress v-if="loading"/>
        <v-card class="custom-border border pa-5 mb-2">
            <FormTextFieldRequired
                :label="'NAME'"
                :value.sync="form.name"
                :error="error && error.name"
            />
            <FormSelectObject
                :label="'TYPE'"
                :items="question_pool_types"
                :value.sync="form.type_of_question_pool"
                :class_="'f16'"
                :disabled="questions.length > 0"
            />
            <div v-if="form.type_of_question_pool === 'assessment'" class="mt-2">
                <v-expansion-panels accordion flat>
                    <v-expansion-panel
                        v-for="(item, i) in questions"
                        :key="i"
                        class="panels my-2 secondary-5"
                        active-class="toggled"
                    >
                        <v-expansion-panel-header class="roboto f14 fw500" :id="`panel-header-${i}`">
                        <div>
                            Question {{ i + 1 }}: 
                            <span
                                v-if="item.type_of_question"
                                class="ml-5 fw500 text-uppercase secondary-2--text">
                                {{ item.type_of_question.replace("_", " ").replace("_", " ") }}</span>

                            <span v-if="item.score" class="ml-5 fw500 text-uppercase secondary-4--text"
                            >{{ item.score }} {{ item.score > 1 ? 'pts' : 'pt' }}</span
                            >
                        </div>
                        <v-spacer />
                        <div class="d-flex justify-end">
                            <v-btn icon @click.stop="() => { selected_question_id = item.id, deleteDialog = true }">
                            <v-icon size="18">mdi-trash-can-outline</v-icon>
                            </v-btn>
                        </div>
                        </v-expansion-panel-header>
                        <v-expansion-panel-content style="margin-top: -20px">
                            <Choices 
                                :item="item" 
                                :assessment_type="'graded_assessment'" 
                                :updateLoading="updateLoading"
                                :successSaved="successSaved"
                                @inputAnswer="inputAnswer"
                                @updateQuestion="updateQuestion"
                                @addChoices="addChoices"
                                @cancel="cancel"
                            />
                        </v-expansion-panel-content>
                    </v-expansion-panel>
                </v-expansion-panels>
            </div>
            <DeleteDialog
                type="Question"
                :dialog="deleteDialog"
                :loading="deleteLoading"
                @close="() => { selected_question_id = null, deleteDialog = false }"
                @delete="deleteQuestion"
            />
        </v-card>

        <div v-if="form.type_of_question_pool === 'evaluation'" class="mt-2">
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
                                            :status="'UNPUBLISH'"
                                            :permission="'owner'"/>
                                    </div>
                                    <div class="col-1 px-0">
                                        <div class="d-flex justify-end">
                                            <v-btn icon @click.stop="() => { selected_question_id = question.id, deleteDialog = true }">
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
                                    :selected_question_id="selected_question_id"
                                    @addChoice="addChoice" 
                                    :updateLoading="updateLoading"
                                    :successSaved="successSaved"
                                    @removeChoice="removeChoice" 
                                    @changeChoice="changeChoice"
                                    :status="'UNPUBLISH'"
                                    :permission="'owner'"
                                    @updateQuestion="updateQuestion"
                                    @cancel="cancel"/>
                            </v-expansion-panel-content>
                        </v-expansion-panel>
                    </v-expansion-panels>
                </section>
            </transition-group>
        </div>

        <ButtonPrimary
            @click="addQuestion"
            :label="'Add Question'"
            :icon="'mdi-plus'"
            class="ml-auto mt-3"
            :loading="createLoading"
        />
    </section>
</template>

<script>
import { mapState, mapActions, mapMutations } from 'vuex';
import { question_pool_types } from '@/constants/questionpool';
import Choices from '@/components/teacher/questionpool/Choices.vue';
import EvaluationQuestionHeader from '@/components/teacher/evaluation/EvaluationQuestionHeader.vue';
import EvaluationQuestionBuilder from '@/components/teacher/questionpool/EvaluationQuestionBuilder.vue';

export default {
    mounted() {
        this.getQuestionPool()
    },
    components: {
        Choices,
        EvaluationQuestionHeader,
        EvaluationQuestionBuilder
    },
    data: () => ({
        form: {
            name: '',
            type_of_question_pool: 'assessment'
        },
        question_pool_types,
        selected_question_id: null,
        deleteDialog: false,
        createLoading: false, 
        updateLoading: false,
        deleteLoading: false,
        successSaved: false,
        loading: false,
        prevRoute: null,
    }),
    computed: {
        ...mapState({
            errors: (state) => state.errors,
        }),
        
        ...mapState("instructor", {
            questions: (state) => state.questions,
        }),
    },
    methods: {
        ...mapActions('instructor', ['getQuestionPoolAction', 'addQuestionPoolQuestionAction', 'deleteQuestionPoolQuestionAction', 'updateQuestionPoolQuestionAction']),
        ...mapMutations("instructor", ["addQuestionPoolQuestionChoicesMutation", "inputQuestionPoolQuestionAnswerMutation", "questionPoolsQuestionMutation"]),
        ...mapMutations(['alertMutation']),

        getQuestionPool(){
            this.loading = true
            this.getQuestionPoolAction(this.$route.params.id).then(res => {
                this.form.name = res.name
                this.form.type_of_question_pool = res.type_of_question_pool
                this.loading = false
            }).catch(e => {
                console.log(e)
                this.loading = false
            })
        },

        setSurveyType(e) {
            let { id, type, panel_key } = e
            let index = this.questions.findIndex(item => item.id === id)
            let _question = this.questions

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

                this.questionPoolsQuestionMutation(this.questions)
            }
        },
        
        inputAnswer(answer) {
            this.inputQuestionPoolQuestionAnswerMutation(answer);
        },
        
        deleteQuestion() {
            this.deleteLoading = true
            this.deleteQuestionPoolQuestionAction(this.selected_question_id).then(res => {
                this.alertMutation({
                    show: true,
                    text: 'Question deleted successfully',
                    type: "success"
                })
                this.deleteLoading = false
                this.getQuestionPool()
                this.deleteDialog = false
            }).catch(e => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
                this.deleteLoading = false
                this.deleteDialog = false
            })
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
                            choice_0: 'Type your option here'
                        }
                        this.questions[index].data = _choice
                    } else {
                        if(this.questions[index].type_of_question === 'multiple_choice_grid' || this.questions[index].type_of_question === 'checkbox_grid') {
                            this.questions[index].data = {...this.questions[index].data, [name]: 'Type your option here'}
                        } else {
                            this.questions[index].data = {...this.questions[index].data, [`choice_${Object.keys(this.questions[index].data).length }`]: 'Type your option here'}
                        }
                    }
                }

                this.questionPoolsQuestionMutation(this.questions)
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
                    this.questionPoolsQuestionMutation(this.questions)
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

                    this.questionPoolsQuestionMutation(this.questions)
                }
            }
        },

        addChoices(choices){
            this.addQuestionPoolQuestionChoicesMutation(choices)
            console.log(choices)
        },

        changeChoice(e){
            let { index, new_choices } = e
            this.questions[index].data = new_choices
            this.questionPoolsQuestionMutation(this.questions)
        },

        cancel(){
            this.getQuestionPool()
        },

        updateQuestion(question){
            this.updateLoading = true;
            this.selected_question_id = question.id
            this.updateQuestionPoolQuestionAction({
                name: this.form.name,     
                type_of_question_pool: this.form.type_of_question_pool,  
                ...question}).then(() => {
                this.updateLoading = false;
                this.successSaved = true;
                setTimeout(() => {
                    this.successSaved = false;
                }, 2000);
            }).catch(() => {
                this.updateLoading = false;
                this.alertMutation({
                    show: true,
                    text: "Something went wrong.",
                    type: "error"
                    })
                });
                this.selected_question_id = null
            },

        addQuestion(){
            this.createLoading = true
            this.addQuestionPoolQuestionAction({
                question_pool_id: Number(this.$route.params.id),
                question: 'Type your question here',
                score: 1,
                data: {"choice_0": "Type your option 1", "choice_1": "Type your option 2"},
                choices: [{"choice_0": "Type your answer 1", "feedback": null}, {"choice_1": "Type your answer 2", "feedback": null}, {"choice_2": "Type your answer 3", "feedback": null}],
                correct_answer: 'choice_0',
                name: this.form.name,     
                type_of_question_pool: this.form.type_of_question_pool,      
            }).then(() => {
                this.createLoading = false
                this.getQuestionPool()
                this.alertMutation({
                    show: true,
                    text: 'Successfully added a new question',
                    type: "success"
                })
            }).catch(() => {
                this.createLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
            })
        }
    },

    beforeRouteEnter(to, from, next) {
        next(vm => {
            vm.prevRoute = from          
        })
    },
}

</script>

<style scoped>
.toggled {
  background-color: #ffff !important;
  border: 1px solid #bdbdbd !important;
  border-radius: 3px !important;
}
.tox.tox-tinymce {
  border-radius: 5px !important;
}
</style>