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
                :items="assessment_questions"
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
            <div class="poppins f14 d-flex flex-column align-start mb-3">
                <label class="fw500 secondary-2--text mr-2">
                    QUESTION:
                </label>
                <div v-html="selectedQuestion.question ? $dompurifier(selectedQuestion.question) : ''"></div>
            </div>

            <!-- <div class="poppins f14 d-flex flex-column align-start mb-3" v-if="selectedQuestion.correct_answer">
                <label class="fw500 secondary-2--text mr-2">
                    CORRECT ANSWER:
                </label>
                <div></div>
            </div> -->

            <v-sheet class="rounded pa-2 mb-5  f12" color="dark-1" v-if="selectedQuestion.assessment_answer.correct_answer && ['multiple_choice', 'true_or_false', 'justified_multiple_choice', 'modified_true_or_false', 'ordering'].includes(selectedQuestion.type_of_question)">
                <label class="fw500 poppins secondary-2--text mr-2">
                    CORRECT ANSWER:
                </label>
                <div class="my-1 f12" v-if="selectedQuestion.type_of_question === 'ordering'">
                    <div class="poppins secondary-1--text" v-for="(item) in selectedQuestion.assessment_answer.correct_answer.split(',')" :key="item">
                    <v-card outlined class="pa-2 my-1">
                        {{ selectedQuestion.assessment_answer.choices[item.replace('choice_','')][item] }}
                    </v-card>
                    </div>
                </div>
                <div class="d-flex align-center my-1" v-else>
                    <div class="poppins secondary-1--text">{{
                    selectedQuestion.assessment_answer.choices[selectedQuestion.assessment_answer.correct_answer.replace('choice_','')][selectedQuestion.assessment_answer.correct_answer]
                    }}</div>
                </div>
            </v-sheet>
            
            <GradingPieGraph 
                v-if="object_answer.includes(selectedQuestion.type_of_question)"
                :id="selectedQuestion.id"
                :graphLabel="selectedQuestion.assessment_answer.choices.map(item => Object.values(item)[0])"
                :graphData="selectedQuestion.type_of_question === 'sliders' ? getAnswersSliders(selectedQuestion.assessment_answer.choices) : getCountPie(selectedQuestion.assessment_answer.choices)"
            />
            <GradingBarGraphGrid 
                v-if="selectedQuestion.type_of_question === 'ordering'"
                :id="selectedQuestion.id"
                :data="getDataSliders(selectedQuestion.assessment_answer.choices)"
            />
            <FormLabel :label="'JUSTIFICATIONS: '" class="mt-5" v-if="selectedQuestion.type_of_question === 'justified_multiple_choice'"/>
            <FormLabel :label="'EXPLANATIONS: '" class="mt-5" v-if="selectedQuestion.type_of_question === 'modified_true_or_false'"/>

            <GradingAnswerWrapped class="fade" v-if="selectedQuestion.type_of_question === 'essay' && !selectedQuestion.require_file_attachment && !loading" type="html" :answers="getAnswers()"/>
            <GradingAnswerWrapped class="fade" v-if="selectedQuestion.type_of_question === 'identification' && !loading" type="short_answer" :answers="getAnswers()"/>
            <GradingAnswerWrapped class="fade" v-if="selectedQuestion.type_of_question === 'essay' && selectedQuestion.require_file_attachment && !loading" type="essay_with_attachment" :answers="getAnswersWithAttachments()"/>
            <GradingAnswerWrapped class="fade" v-if="(selectedQuestion.type_of_question === 'justified_multiple_choice' || selectedQuestion.type_of_question === 'modified_true_or_false') && selectedQuestion.require_support_answer && !loading && !selectedQuestion.require_file_attachment" type="short_answer" :answers="getSupportAnswers()"/>
            <GradingAnswerWrapped class="fade" v-if="(selectedQuestion.type_of_question === 'justified_multiple_choice' || selectedQuestion.type_of_question === 'modified_true_or_false') && selectedQuestion.require_support_answer && !loading && selectedQuestion.require_file_attachment" type="short_answer_with_attachment" :answers="getSupportAnswersWithAttachments()"/>
        </v-card>
    </section>
</template>


<style>
.inner_p > p {
    margin-bottom: 0 !important;
}
</style>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import GradingPieGraph from '../grading/GradingPieGraph.vue';
import GradingAnswerWrapped from '../grading/GradingAnswerWrapped.vue';
import GradingBarGraphGrid from '../grading/GradingBarGraphGrid.vue';

export default {
    props: ['submitted_from', 'submitted_to'],

    components: {
        GradingPieGraph,
        GradingAnswerWrapped,
        GradingBarGraphGrid
    },

    data: () => ({
        loading: false,
        selected: null,
        selectedQuestion: null,
        object_answer: ['multiple_choice', 'justified_multiple_choice', 'true_or_false', 'sliders', 'modified_true_or_false'],
    }),

    created() {
        this.getQuestions()
    },

    computed: {
        ...mapState('instructor', {
            assessment_questions: (state) => state.assessment_questions,
            assessment_answers: (state) => state.assessment_answers
        }),
    },

    methods: {
        ...mapActions('instructor', ['getInstructorAssessmentAction', 'getAssessmentAnswersAction']),
        ...mapMutations(['alertMutation']),

        getQuestions(){
            this.loading = true
            this.getInstructorAssessmentAction(this.$route.params.id).then(() => {
                this.loading = false
            }).catch(() => {
                this.loading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in fetching questions',
                    type: "error"
                })
            })
        },
        
        prevQuestion() {
            if(this.assessment_questions.findIndex( item => item.id === this.selected) === 0) {
                this.selected = this.assessment_questions.length -1
            } else {
                this.selected = this.assessment_questions[this.assessment_questions.findIndex( item => item.id === this.selected) - 1].id
            }
        },
        
        nextQuestion() {
            if(this.assessment_questions.findIndex( item => item.id === this.selected) === (this.assessment_questions.length -1)) {
                this.selected = this.assessment_questions[0].id
            } else {
                this.selected = this.assessment_questions[this.assessment_questions.findIndex( item => item.id === this.selected) + 1].id
            }
        },

        getCountPie(choices) {
            let _choices = choices.map(item => Object.keys(item)[0])
            let _data = []
            let _response_count = 0

            _choices.forEach( choice => {
                let _counter = 0
                this.assessment_answers.forEach((answer) => {
                    
                    if(answer.answer === choice) {
                        _counter++
                    }

                })
                _data.push(_counter)
            })
            
            return _data
        },

        getAnswers() {
            let _data = []

            this.assessment_answers.forEach((answer) => {
                if(answer.answer) {
                    _data.push(answer.answer)
                }
            })

            return _data 
        },

        getAnswersWithAttachments() {
            let _data = []

            this.assessment_answers.forEach((answer) => {
                if(answer.answer) {
                    _data.push({
                        answer: answer.answer,
                        attachment: answer.file
                    })
                }
            })

            return _data 
        },

        getSupportAnswers() {
            let _data = []

            this.assessment_answers.forEach((answer) => {
                if(answer.support_answer) {
                    _data.push(answer.support_answer)
                }
            })

            return _data 
        },

        getSupportAnswersWithAttachments() {
            let _data = []

            this.assessment_answers.forEach((answer) => {
                if(answer.support_answer) {
                    _data.push({
                        answer: answer.support_answer,
                        attachment: answer.file
                    })
                }
            })

            return _data 
        },

        getAnswersSliders(choices) {
            let _choices = choices.map(item => Object.keys(item)[0])
            let _data = []

            _choices.forEach( choice => {
                let _counter = 0
                this.assessment_answers.forEach((answer) => {
                    
                    if(choice === `choice_${answer.answer}`) {
                        _counter++
                    }

                })
                _data.push(_counter)
            })
            
            return _data 
        },

        generateColor() {
            const red = Math.floor(Math.random() * 256);
            const green = Math.floor(Math.random() * 256);
            const blue = Math.floor(Math.random() * 256);
            return `rgb(${red}, ${green}, ${blue})`;
        },

        getDataSliders(data) {
            let _data = {
                label: [],
                datasets: []
            }

            let _cols = []
            let _rows = []
            let _colors = []

            _data.label = data.map(item => Object.values(item)[0])
            _cols = data.map(item => Object.keys(item)[0])

            data.forEach((item, key) => {
                _rows.push(key + 1)
            })

            _rows.forEach(_i => {
                let _randColor = this.generateColor()

                while(_colors.includes(_randColor)) {
                    _randColor = this.generateColor()
                }

                _colors.push(_randColor)

                let _dataset = {
                    label: _i,
                    backgroundColor: _randColor,
                    data: []
                }

                _cols.forEach(_j => {
                    let _counter = 0
                    this.assessment_answers.forEach((answer) => {
                        if(answer.answer) {
                            let _answers = answer.answer.split(',')

                            if(_answers[_i-1] === _j) {
                                _counter++
                            }
                        }
                    })
                    _dataset.data.push(_counter)
                })
                _data.datasets.push(_dataset)
            })

            return _data
        }
    },

    watch: {
        selected(val) {
            this.selectedQuestion = this.assessment_questions.find(item => item.id === val)
            if(val) {
                this.loading = true
                this.getAssessmentAnswersAction({ assessment_id: this.selectedQuestion.assessment_id, assessment_question_id: this.selectedQuestion.id, submitted_from: this.submitted_from, submitted_to: this.submitted_to}).then(() => {
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong in fetching answers',
                        type: "error"
                    })
                })
            }
        }
    }

}

</script>