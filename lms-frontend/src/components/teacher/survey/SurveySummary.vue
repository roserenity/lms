<template>
    <section>
        <v-card class="d-flex flex-row align-center pa-3 mb-5 custom-border border">
            <v-btn icon @click="prevQuestion">
                <v-icon color="primary">
                    mdi-chevron-left
                </v-icon>
            </v-btn>
            <v-select 
                hide-details
                dense
                :items="questions"
                item-value="id"
                :loading="loading"
                v-model="selected">
                <template slot="item" slot-scope="data">
                    <v-list-item-content>
                        <v-list-item-title class="roboto f14 fw500">
                            <span class="secondary-3--text inner_p" v-html="data.item.question ? $dompurifier(data.item.question) : ''"></span>
                        </v-list-item-title>
                    </v-list-item-content>
                </template>
                <template slot="selection"  slot-scope="data">
                    <span class="f12 secondary-2--text fw500">QUESTION: &nbsp;</span><span class="roboto f14 fw500 inner_p" v-html="data.item.question ? $dompurifier(data.item.question) : ''">
                    </span>
                </template>
            </v-select>
            <v-btn icon @click="nextQuestion">
                <v-icon color="primary">
                    mdi-chevron-right
                </v-icon>
            </v-btn>
        </v-card>
        <v-card v-if="selected" class="pa-5 mb-5 custom-border border">
            <v-row no-gutters class="">
                <v-col cols="12" md="10" class="poppins f14 d-flex flex-column align-start mb-3">
                    <label class="fw500 secondary-2--text mr-2">
                        QUESTION:
                    </label>
                    <div v-html="selectedQuestion.question ? $dompurifier(selectedQuestion.question) : ''"></div>
                </v-col>
                <!-- <v-col cols="12" md="4" class="text-right">
                    <v-card-text class="poppins">
                        {{ question.hasOwnProperty('response_count') ? question.response_count : 0 }} responses
                    </v-card-text>
                </v-col>  -->
            </v-row>

            <section v-if="!loading" class="fade">
                <GradingBarGraph
                    v-if="!no_answers.includes(selectedQuestion.type_of_question) && !object_answer.includes(selectedQuestion.type_of_question)"
                    :id="selectedQuestion.id"
                    :graphLabel="selectedQuestion.type_of_question !== 'linear_scale' ? Object.values(selectedQuestion.data) : linearScaleLabel(Object.keys(selectedQuestion.data))"
                    :graphData="getCountBar(selectedQuestion.data, selectedQuestion.id, index, selectedQuestion.type_of_question)"/>
                <GradingPieGraph 
                    v-if="selectedQuestion.type_of_question === 'multiple_choice' || selectedQuestion.type_of_question === 'dropdown'"
                    :id="selectedQuestion.id"
                    :graphLabel="Object.values(selectedQuestion.data)"
                    :graphData="getCountPie(selectedQuestion.data, selectedQuestion.id, index, selectedQuestion.type_of_question)"
                />
                <GradingBarGraphGrid 
                    v-if="selectedQuestion.type_of_question === 'checkbox_grid'"
                    :id="selectedQuestion.id"
                    :data="getData(selectedQuestion.data, selectedQuestion.id, index, 'checkbox_grid')"
                />
                <GradingBarGraphGrid 
                    v-if="selectedQuestion.type_of_question === 'multiple_choice_grid'"
                    :id="selectedQuestion.id"
                    :data="getData(selectedQuestion.data, selectedQuestion.id, index, 'multiple_choice_grid')"
                />
                <GradingAnswerWrapped v-if="selectedQuestion.type_of_question === 'date'" type="date" :answers="getAnswers(selectedQuestion.id, index)"/>
                <GradingAnswerWrapped v-if="selectedQuestion.type_of_question === 'time'" type="time" :answers="getAnswers(selectedQuestion.id, index)"/>
                <GradingAnswerWrapped v-if="selectedQuestion.type_of_question === 'short_answer'" type="short_answer" :answers="getAnswers(selectedQuestion.id, index)"/>
                <GradingAnswerWrapped v-if="selectedQuestion.type_of_question === 'paragraph'" type="paragraph" :answers="getAnswers(selectedQuestion.id, index)"/>
                <GradingAnswerWrapped v-if="selectedQuestion.type_of_question === 'file_upload'" type="link" :answers="getAnswers(selectedQuestion.id, index)"/>
            </section>
        </v-card>
    </section>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'

import GradingBarGraph from '../grading/GradingBarGraph.vue';
import GradingBarGraphGrid from '../grading/GradingBarGraphGrid.vue';
import GradingPieGraph from '../grading/GradingPieGraph.vue';
import GradingAnswerWrapped from '../grading/GradingAnswerWrapped.vue';

export default {
    components: {
        GradingBarGraph,
        GradingPieGraph,
        GradingBarGraphGrid,
        GradingAnswerWrapped
    },
    props: ['submitted_from', 'submitted_to', 'loading'],
    data: () => ({
        selected: null,
        selectedQuestion: null,
        loading: false,
        responseCount: {},
        no_answers: ['file_upload', 'date', 'time', 'short_answer', 'paragraph', 'checkbox_grid', "multiple_choice_grid"],
        object_answer: ['multiple_choice', 'dropdown']
    }),
    computed: {
        ...mapState('instructor', {
            questions: (state) => state.questions,
            evaluation_answers: (state) => state.evaluation_answers
        }),
    },
    created() {
        this.getQuestions()
    },
    methods:{
        ...mapActions('instructor', ['getCourseEvaluationQuestionsAction', 'getModuleEvaluationQuestionsAction', 'getCourseEvaluationQuestionAnswers', 'getModuleEvaluationQuestionAnswers']),
        ...mapMutations('instructor', ['evaluationQuestionMutation']),
        
        nextQuestion() {
            if(this.questions.findIndex( item => item.id === this.selected) === (this.questions.length -1)) {
                this.selected = this.questions[0].id
            } else {
                this.selected = this.questions[this.questions.findIndex( item => item.id === this.selected) + 1].id
            }
        },

        prevQuestion() {
            if(this.questions.findIndex( item => item.id === this.selected) === 0) {
                this.selected = this.questions.length -1
            } else {
                this.selected = this.questions[this.questions.findIndex( item => item.id === this.selected) - 1].id
            }
        },

        getQuestions() {
            this.loading = true
            if(this.$route.params.type === 'course_evaluation') {
                this.getCourseEvaluationQuestionsAction(this.$route.params.survey_id).then(() => {
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                })
            }

            if(this.$route.params.type === 'module_evaluation') {
                this.getModuleEvaluationQuestionsAction(this.$route.params.survey_id).then(() => {
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                })
            }
        },

        getCountPie(choices, question_id, index, type) {
            let _choices = Object.keys(choices)
            let _data = []
            let _response_count = 0

            _choices.forEach( choice => {
                let _counter = 0
                    this.evaluation_answers.forEach((answer) => {
                        if(answer[0].data) {
                            if(answer[0].data) {
                                if(JSON.parse(answer[0].data).answer === choice){
                                    _counter++
                                }
                            }
                        }
                    })
                _data.push(_counter)
            })
            this.evaluationQuestionMutation(this.questions)
            return _data
        },

        getCountBar(choices, question_id, index, type) {
            let _choices = Object.keys(choices)
            let _data = []
            let _response_count = 0

            if(type !== 'linear_scale') {
                _choices.forEach( choice => {
                    let _counter = 0
                        this.evaluation_answers.forEach((answer) => {
                            if(answer.length > 0 && answer[0].data) {
                                if(Array.isArray(JSON.parse(answer[0].data).answer)) {
                                    if(Object.values(JSON.parse(answer[0].data).answer).includes(choice)){
                                        _counter++
                                        _response_count++;
                                    }
                                } else if(JSON.parse(answer[0].data).answer === choice) {
                                    _counter++
                                    _response_count++;
                                }
                            }
                        })
                    _data.push(_counter)
                })
            } else {
                let labels = Array.from({ length: parseInt(_choices[1].split('_')[1]) }, (v, index) => (index) + 1)
                labels.forEach(i => {
                    let _counter = 0
                        this.evaluation_answers.forEach((answer) => {
                            if(answer.length > 0 && answer[0].data) {
                                if(JSON.parse(answer[0].data).answer) {
                                    if(JSON.parse(answer[0].data).answer === `scale_${i}`){
                                         _counter++
                                    }
                                }
                            }
                        })
                    _data.push(_counter)
                })
            }

            //this.questions[index].response_count = _response_count
            this.evaluationQuestionMutation(this.questions)
            return _data
        },

        getAnswers(question_id, index) {
            let _data = []
            let _response_count = 0
                this.evaluation_answers.forEach((answer) => {
                    if(answer.length > 0 && answer[0].data) {
                        if(JSON.parse(answer[0].data).answer) {
                            _data.push(JSON.parse(answer[0].data).answer)
                            _response_count++;
                        }
                    }
                })
            //*  */this.questions[index].response_count = _response_count
            this.evaluationQuestionMutation(this.questions)

            return _data 
        },

        linearScaleLabel(data) {
            let labels = Array.from({ length: parseInt(data[1].split('_')[1]) }, (v, index) => (index) + 1)
            return labels
        },

        generateColor() {
            const red = Math.floor(Math.random() * 256);
            const green = Math.floor(Math.random() * 256);
            const blue = Math.floor(Math.random() * 256);
            return `rgb(${red}, ${green}, ${blue})`;
        },

        getData(data, question_id, index, type) {
            let _data = {
                label: [],
                datasets: []
            }
            let _cols = []
            let _rows = []
            let _colors = []
            let _response_count = 0
            

            Object.keys(data).forEach(key => {
                if(key.substring(0, 3) === 'col') {
                    _data.label.push(data[key])
                }
                if(key.substring(0, 3) === 'col') {
                    _cols.push(key)
                }
                if(key.substring(0, 3) === 'row') {
                    _rows.push(key)
                }
            })

            _rows.forEach(_i => {
                let _randColor = this.generateColor()

                while(_colors.includes(_randColor)) {
                    _randColor = this.generateColor()
                }

                _colors.push(_randColor)

                let _dataset = {
                    label: data[_i],
                    backgroundColor: _randColor,
                    data: []
                }
                _cols.forEach(_j => {
                    let _counter = 0
                        if(type === 'checkbox_grid') {
                            if(this.evaluation_answers) {
                            this.evaluation_answers.forEach((answer) => {
                                if(answer.length > 0 && answer[0].data) {
                                    if(JSON.parse(answer[0].data).answer) {
                                        if(Object.values(JSON.parse(answer[0].data).answer).includes(`${_i}_${_j}`)){
                                            _counter++
                                        }
                                    }
                                }
                            })
                        }
                        } else if(type === 'multiple_choice_grid') {
                            this.evaluation_answers.forEach((answer) => {
                                if(answer.length > 0 && answer[0].data) {
                                    if(JSON.parse(answer[0].data).answer) {
                                        if(Object.values(JSON.parse(answer[0].data).answer).includes(`${_i}_${_j}`)){
                                            _counter++
                                        }
                                    }
                                }
                            })
                        }
                    _dataset.data.push(_counter)
                })
                _data.datasets.push(_dataset)
            })

            // this.questions[index].response_count = this.evaluation_answers?.[question_id].length ?? 0;
            this.evaluationQuestionMutation(this.questions)
            return _data
        }
    },

    watch: {
        selected(val) {
            this.selectedQuestion = this.questions.find(item => item.id === val)
            if(val) {
                this.loading = true
                if(this.$route.params.type === 'course_evaluation') {
                    this.getCourseEvaluationQuestionAnswers({course_evaluation_id: this.$route.params.survey_id, course_evaluation_question_id: this.selected, submitted_to: this.submitted_to, submitted_from: this.submitted_from}).then(() => {
                        this.loading = false
                    }).catch(() => {
                        this.loading = false
                    })
                }

                if(this.$route.params.type === 'module_evaluation') {
                    this.getModuleEvaluationQuestionAnswers({module_evaluation_id: this.$route.params.survey_id, module_evaluation_question_id: this.selected, submitted_to: this.submitted_to, submitted_from: this.submitted_from}).then(() => {
                        this.loading = false
                    }).catch(() => {
                        this.loading = false
                    })
                }
            }
        }
    }
}
</script>
