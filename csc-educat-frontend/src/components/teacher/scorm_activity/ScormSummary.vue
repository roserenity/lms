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
                :items="scorm_activity_questions"
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
                    <div v-html="selectedQuestion.question ? $dompurifier(selectedQuestion.question) : ''" />
                </v-col>
            </v-row>

            <section v-if="!loading" class="fade">
                <GradingPieGraph 
                    v-if="selectedQuestion.type_of_question === 'MULTIPLE_CHOICE'"
                    :id="selectedQuestion.id"
                    :graphLabel="selectedQuestion.choices.map(choice => choice.title)"
                    :graphData="getCountPie(selectedQuestion.choices.map(choice => choice.title))"
                />
                <GradingBarGraph 
                    v-if="selectedQuestion.type_of_question === 'MULTIPLE_RESPONSE'"
                    :id="selectedQuestion.id"
                    :graphLabel="selectedQuestion.choices.map(choice => choice.title)"
                    :graphData="getCountBar(selectedQuestion.choices.map(choice => choice.title))"
                />
                <GradingAnswerWrapped v-if="['FILL_IN_THE_BLANK', 'QA_SECTION'].includes(selectedQuestion.type_of_question)" type="paragraph" :answers="getAnswers()"/>
                <GradingBarGraphGrid 
                    v-if="selectedQuestion.type_of_question === 'MATCHING'"
                    :id="selectedQuestion.id"
                    :data="getData()"
                />
            </section>
        </v-card>
    </section>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import GradingAnswerWrapped from '../grading/GradingAnswerWrapped.vue';
import GradingBarGraph from '../grading/GradingBarGraph.vue';
import GradingPieGraph from '../grading/GradingPieGraph.vue';
import GradingBarGraphGrid from '../grading/GradingBarGraphGrid.vue';

export default {
    components: {
        GradingAnswerWrapped,
        GradingBarGraph,
        GradingPieGraph,
        GradingBarGraphGrid,
    },
    data: () => ({
        selectedQuestion: null,
        loading: false,
        selected: null
    }),
    computed: {
        ...mapState('instructor', {
            scorm_activity_questions: (state) => state.scorm_activity_questions,
            scorm_activity_answers: (state) => state.scorm_activity_answers,
        }),
    },
    methods: {
        ...mapActions('instructor', ['getScormActivityQuestionAnswersAction']),

        prevQuestion(){

        },

        nextQuestion() {

        },

        getLearnerAnswers(id){
            if(!this.loading) {
                this.loading = true
                this.getScormActivityQuestionAnswersAction({ course_id: this.$route.params.id, scorm_activity_id: this.$route.params.scorm_activity_id, question_id: id}).then(() => {
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                })
            }
        },

        getAnswers(){
            if(['FILL_IN_THE_BLANK', 'QA_SECTION'].includes(this.selectedQuestion.type_of_question)) {
                let answers = []

                this.scorm_activity_answers.forEach(item => {
                    answers.push(item.answer)
                })

                return answers
            }
        },

        getCountPie(choices) {
            let _data = []

            choices.forEach( choice => {
                let _counter = 0
                    this.scorm_activity_answers.forEach((answer) => {
                        if(answer.answer[0].title === choice) {
                            _counter++
                        }
                    })
                _data.push(_counter)
            })

            return _data
        },

        getCountBar(choices) {
            let _data = []

            choices.forEach( choice => {
                let _counter = 0
                    this.scorm_activity_answers.forEach((answer) => {
                        answer.answer.forEach(item => {
                            if(item.title === choice) {
                                _counter++
                            }
                        })
                    })
                _data.push(_counter)
            })
            
            return _data
        },

        getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        },

        getData() {
            
            const frequencyMatrix = {};
            const allChoices = new Set();
            const allTargets = new Set();

            // Loop through each response and count the matches
            this.scorm_activity_answers.forEach(response => {
                response.answer.forEach(pair => {
                const source = pair.source.title;
                const target = pair.target.title;

                allChoices.add(source);
                allTargets.add(target);

                if (!frequencyMatrix[source]) {
                    frequencyMatrix[source] = {};
                }

                if (!frequencyMatrix[source][target]) {
                    frequencyMatrix[source][target] = 0;
                }

                frequencyMatrix[source][target]++;
                });
            });

            const choicesArray = Array.from(allChoices);
            const targetsArray = Array.from(allTargets);

            const datasets = choicesArray.map(choice => {
                return {
                    label: choice,
                    backgroundColor: this.getRandomColor(), // Generate a random color for each dataset
                    data: targetsArray.map(target => frequencyMatrix[choice][target] || 0)
                };
            });

            let chartData = {
                label: targetsArray, // Targets on X-axis
                datasets
            };

            return chartData
        }
    },
    watch: {
        selected(val) {
            this.selectedQuestion = this.scorm_activity_questions.find(item => item.id === val)
            this.getLearnerAnswers(val)
        }
    }
}

</script>