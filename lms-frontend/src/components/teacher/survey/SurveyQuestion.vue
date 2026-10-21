<template>
    <section>
        <!-- <SurveyQuestionHeader /> -->
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
        <v-card class="py-5 custom-border border fade" v-if="!loading2">
            <section v-if="getTypeOfQuestion === 'multiple_choice' || getTypeOfQuestion === 'checkbox'">
                <section class="pa-2" v-for="(item, key) in selections" :key="key">
                    <v-sheet color="secondary" class="rounded mx-5 pa-2">
                        {{  item.text }}
                    </v-sheet>
                    <SurveyQuestionRespondentMenu :responses_count="item.count" :respondents="item.respondents"/>                </section>
            </section>
            <section v-if="getTypeOfQuestion === 'dropdown'">
                <section class="pa-2" v-for="(item, key) in selections" :key="key">
                    <v-sheet color="secondary" class="rounded mx-5 pa-2">
                        {{  item.text }}
                    </v-sheet>
                    <SurveyQuestionRespondentMenu :responses_count="item.count" :respondents="item.respondents"/>
                </section>
            </section>
            <section v-if="getTypeOfQuestion === 'linear_scale'">
                <section class="pa-2" v-for="(item, key) in selections" :key="key">
                    <v-sheet color="secondary" class="rounded mx-5 pa-2">
                        {{  item.text }}
                    </v-sheet>
                    <SurveyQuestionRespondentMenu :responses_count="item.count" :respondents="item.respondents"/>
                </section>
            </section>
            <section v-if="getTypeOfQuestion === 'date' || getTypeOfQuestion === 'time' || getTypeOfQuestion === 'short_answer' || getTypeOfQuestion === 'paragraph'" >
                <section class="pa-2" v-for="(key, index) in answers" :key="index">
                    <v-sheet v-if="getTypeOfQuestion === 'date'" color="secondary" class="rounded mx-5 pa-2">
                        {{ $dateFormat.mmDDyy(key) }}
                    </v-sheet>
                    <v-sheet v-else-if="getTypeOfQuestion === 'time'" color="secondary" class="rounded mx-5 pa-2">
                        <input type="time" readonly :value="key"/>
                    </v-sheet>
                    <v-sheet v-else color="secondary" class="rounded mx-5 pa-2">
                        {{ key }}
                    </v-sheet>
                </section>
            </section>
            <!-- <section v-if="getTypeOfQuestion === 'file_upload'" >
                <section class="pa-2" v-for="(key, index) in getAnswersFile" :key="index">
                    <v-sheet color="secondary" class="rounded mx-5 pa-2">
                        <a :href="key" class="secondary-1--text" target="_blank">{{ key }}</a>
                    </v-sheet>
                </section>
            </section> -->
            <section v-if="getTypeOfQuestion === 'multiple_choice_grid' || getTypeOfQuestion === 'checkbox_grid'" >
                <section class="pa-2" v-for="(item, key) in selections" :key="key">
                    <v-sheet color="secondary" class="rounded mx-5 pa-2">
                        {{  item.text }}
                    </v-sheet>
                    <SurveyQuestionRespondentMenu :responses_count="item.count" :respondents="item.respondents"/>

                </section>
            </section>
        </v-card>
    </section>
</template>

<style>
.inner_p > p {
    margin-bottom: 0 !important;
}
</style>

<script>
import { mapState } from 'vuex'
import SurveyQuestionRespondentMenu from './SurveyQuestionRespondentMenu.vue'
// import SurveyQuestionHeader from './SurveyQuestionHeader.vue';

export default {
    props: {
        loading: {
            default: false,
            type: Boolean
        }
    },
    data: () => ({
        selected: null,
        object_answer: ['multiple_choice', 'dropdown'],
        no_answers: ['file_upload', 'date', 'time', 'short_answer', 'paragraph'],
        respondents: [],
        loading2: false,
        selections: [],
        answers:[]
    }),
    components: {
        SurveyQuestionRespondentMenu
        // SurveyQuestionHeader
    },
    mounted() {
    },
    computed: {
        ...mapState('instructor', {
            questions: (state) => state.questions,
            evaluation_answers: (state) => state.evaluation_answers,
            course_evaluations: (state) => state.course_evaluations,
            module_evaluations: (state) => state.module_evaluations
        }),
        getTypeOfQuestion(){
            if(this.selected) {
                return this.questions.find( item => item.id === this.selected).type_of_question
            } else {
                return ''
            }
        },
        getData() {
            if(this.selected) {
                return this.questions.find( item => item.id === this.selected).data
            } else {
                return ''
            }
        },
        getAnswersFile() {
            if(this.selected) {
                let _arr = []
                // this.evaluation_answers.forEach( answer => {
                //     let _answer = null
                //     if(this.$route.params.type === 'course_evaluation'){
                //         _answer = answer.answers[answer.answers.findIndex( item => item.course_evaluation_question_id === this.selected)]
                //     }
                //     if(this.$route.params.type === 'module_evaluation'){
                //         _answer = answer.answers[answer.answers.findIndex( item => item.module_evaluation_question_id === this.selected)]
                //     }

                //     console.log(_answer)
                //     if(_answer.image) {
                //         _arr.push(_answer.image.url)
                //     }
                // })

                return _arr
            } else {
                return []
            }
        },
        
    },
    methods: {
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
        getNumberOfResponses(answer_key, index) {
            let _count = 0
            let _respondents = []

            this.evaluation_answers[this.questions[index].id].forEach((answer) => {
                if(answer.data) {
                    if(JSON.parse(answer.data)){
                        if(JSON.parse(_answer.data).answer === answer_key) {
                            _count++
                            // _respondents.push(`${_user.first_name ? _user.first_name : 'User'} ${_user.last_name ? _user.last_name : 'Account'}`)
                        }
                    }
                }
            })

            return _count
        },

        getNumberOfResponsesUnique(answer_key) {
            let type_of_question = this.getTypeOfQuestion

            if(this.selected){
                this.selections.forEach((item, key) => {

                    let _count = 0
                    let _respondents = []

                    this.evaluation_answers[this.selected].forEach((answer) => {
                        if(['multiple_choice_grid', 'checkbox_grid'].includes(type_of_question)) {
                            if(answer.data) {
                                if(JSON.parse(answer.data)){
                                    let getAnswers = JSON.parse(answer.data).answer
                                    if(Object.values(getAnswers).includes(item.value)){
                                        _count++;
                                        //_respondents.push(`${answer.user.first_name ? answer.user.first_name : 'User'} ${answer.user.last_name ? answer.user.last_name : 'Account'} ${answer.user.suffix ? answer.user.suffix : ''}`)
                                    }
                                }
                            }
                        }

                    })
                    
                    this.selections[key].count = _count
                    // this.selections[key].respondents = _respondents
                })
            }

           
        },

        getAnswersGrid() {
            if(this.selected) {
                let _arr = []
                let getChoices = null
                let question = this.questions.find(item => item.id === this.selected)
                
                const result = {};

                if(question) {
                    getChoices = question.data
                    if(getChoices) {
                        for (const rowKey in getChoices) {
                            if (rowKey.startsWith("row_")) {
                                const rowIndex = rowKey.replace("row_", "");
                                for (const colKey in getChoices) {
                                    if (colKey.startsWith("col_")) {
                                        
                                        const colIndex = colKey.replace("col_", "");
                                        _arr.push({text: `${getChoices[rowKey]} - ${getChoices[colKey]}`, value: `${rowKey}_${colKey}`, count: 0, respondents: []})
                                    }
                                }
                            }
                        }

                    }
                }

                this.selections = _arr
            } else {
                this.selections = []
            }

            this.getNumberOfResponsesUnique()
        },

        
        getAnswers() {
            if(this.selected) {
                let _arr = []
                this.evaluation_answers[this.selected].forEach((answer) => {
                    if(answer.data) {
                        if(JSON.parse(answer.data)){
                            if(JSON.parse(answer.data).answer) {
                                _arr.push(JSON.parse(answer.data).answer)
                            }
                        }
                    }
                })

                this.answers = _arr
            } else {
                return []
            }

        },

        getAnswersLinear() {

            let _arr = []
            for(let i = 1; i <= parseInt(Object.keys(this.getData)[1].split('_')[1]); i++) {
                let _count = 0
                let _respondents = []
                this.evaluation_answers[this.selected].forEach((answer) => {
                    if(answer.data) {
                        if(JSON.parse(answer.data)){
                            if(JSON.parse(answer.data).answer === `scale_${i}`) {
                                _count++
                                _respondents.push((`${answer.user.full_name}`));
                            }
                        }
                    }
                })

                _arr.push({text: `${i}`, value: `${i}`, count: _count, respondents: _respondents})
                
            }
            

            this.selections = _arr
        },

        getAnswersInChoice(){
            let _arr = []

            let question = this.questions.find( item => item.id === this.selected).data
            Object.keys(question).forEach((item) => {
                let _count = 0
                let _respondents = []
                this.evaluation_answers[this.selected].forEach((answer) => {
                    if(answer.data) {
                        if(JSON.parse(answer.data)){
                            if(JSON.parse(answer.data).answer.isArray()) {
                                if(JSON.parse(answer.data).answer.includes(item)) {
                                    _count++
                                    _respondents.push((`${answer.user.full_name}`));
                                }
                            } else {
                                if(JSON.parse(answer.data).answer === item) {
                                    _count++
                                    _respondents.push((`${answer.user.full_name}`));
                                }
                            }
                        }
                    }
                })
                _arr.push({text: `${question[item]}`, value: `${item}`, count: _count, respondents: _respondents})
            })

            this.selections = _arr
        },

        getAnswersSelected(){
            let _arr = []
            let question = this.questions.find( item => item.id === this.selected).data
            Object.keys(question).forEach((item) => {
                let _count = 0
                let _respondents = []
                 console.log('here1')

                if(this.evaluation_answers[this.selected]) {
                    this.evaluation_answers[this.selected].forEach((answer) => {
                        if(answer.data) {
                            if(JSON.parse(answer.data)){
                                if(JSON.parse(answer.data).answer === item) {
                                    _count++
                                    _respondents.push((`${answer.user.full_name}`));
                                }
                            }
                        }
                    })
                    
                }
                _arr.push({text: `${question[item]}`, value: `${item}`, count: _count, respondents: _respondents})
            })

            this.selections = _arr
        }
    },
    watch: {
        selected(){
            this.loading1 = false
            if(['multiple_choice_grid', 'checkbox_grid'].includes(this.getTypeOfQuestion)){
                this.getAnswersGrid()
            }
            if(['linear_scale'].includes(this.getTypeOfQuestion)){
                this.getAnswersLinear()
            }
            if(['date', 'time', 'short_answer', 'paragraph'].includes(this.getTypeOfQuestion)){
                this.getAnswers()
            }
            if(['multiple_choice', 'checkbox'].includes(this.getTypeOfQuestion)){
                this.getAnswersInChoice()
            }
            
            if(['dropdown'].includes(this.getTypeOfQuestion)){
                this.getAnswersSelected()
            }
        }
    }
}
</script>
