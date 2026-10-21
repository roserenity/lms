<template>
    <section>
        <v-btn 
            text 
            color="primary" 
            dense 
            class="px-1" 
            @click="back">
            <v-icon small>
            mdi-chevron-left
            </v-icon>
            BACK
        </v-btn>
        <v-card class="custom-border border poppins mb-3 white--text" color="primary">
            <v-card-title class="mb-5">
                {{  evaluation.title }}
            </v-card-title>
            <v-card-text v-html="evaluation.description ? $dompurifier(evaluation.description) : ''" class="f12 white--text"></v-card-text>
        </v-card>

        <v-form ref="form">
            <v-card class="custom-border border poppins mb-2" v-for="(question, i) in questions" :key="question.id" v-if="!loading">
                <v-card-text class="poppins fw500 f13 secondary-1--text pb-0">QUESTION <span v-if="question.is_require_to_answer"class="error--text">*</span></v-card-text>
                <v-card-text class="f17 py-0">
                    <span v-html="question.question ? $dompurifier(question.question) : ''"></span>
                </v-card-text>
                <v-card-text v-if="error.includes(i)">
                    <span class="danger--text">This is required.</span>
                </v-card-text>
                <v-slide-group
                    show-arrows
                    class="pa-3"
                    >
                    <v-slide-item
                        v-for="img in question.image"
                        :key="img.id"
                    >
                        <v-img
                            :src="img.url"
                            contain
                            max-width="250"
                            class="pointer-default"
                        />
                    </v-slide-item>
                </v-slide-group>
                <v-divider />
                <RadioButtons v-if="question.type_of_question === 'multiple_choice'" :choices="question.data" :index="i" @saveAnswer="saveAnswer" :preview="false" :answer="null"/>
                <Checkboxes v-if="question.type_of_question === 'checkbox'" :choices="question.data" :index="i" @saveAnswer="saveAnswer" :preview="false" :answer="null"/>
                <Dropdown v-if="question.type_of_question === 'dropdown'" :choices="question.data" :index="i" @saveAnswer="saveAnswer" :preview="false" :answer="null"/>
                <DateInput v-if="question.type_of_question === 'date'" :index="i" @saveAnswer="saveAnswer" :preview="false" :answer="null"/>
                <TimeInput v-if="question.type_of_question === 'time'" :index="i" @saveAnswer="saveAnswer" :preview="false" :answer="null"/>
                <v-card-text v-if="question.type_of_question === 'short_answer'">
                    <v-text-field class=" col-sm-6 poppins f12" outlined dense hide-details @change="addText($event, i)"></v-text-field>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'paragraph'">
                    <v-textarea 
                        class=" col-sm-6 poppins f12" 
                        outlined 
                        dense 
                        hide-details="auto"
                        auto-grow 
                        counter="255"
                        :rules="[
                            v => !v || v.length <= 255 || 'Max 255 characters'
                        ]"
                        @change="addText($event, i)"></v-textarea>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'linear_scale'">
                    <LinearScale :question="question" :index="i" @saveAnswer="saveAnswer" :preview="false" :answer="null"/>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'checkbox_grid'">
                    <Grid :question="question" type_of_question="checkbox_grid" :index="i" @saveAnswer="saveAnswer" :preview="false" :answer="null"/>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'multiple_choice_grid'">
                    <Grid :question="question" type_of_question="multiple_choice_grid" :index="i" @saveAnswer="saveAnswer" :preview="false" :answer="null"/>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'file_upload'">
                    <FileInput :index="i" @saveAnswer="saveAnswer" :answer="answers[i]" @removeAttachment="removeAttachment"/>
                </v-card-text>
            </v-card>
        </v-form>
        
        <LinearProgress v-if="loading" class="mb-2"/>

        <ButtonPrimary
            :label="'Submit'"
            :disabled="loading"
            @click="submit"
         />
        
        <DialogLeave :dialog="dialog" @cancel="dialog = false" @submit="submit" />
    </section>
</template>

<script>
import { mapActions, mapMutations } from "vuex"
import RadioButtons from '@/components/user/evaluation/RadioButtons.vue'
import Checkboxes from '@/components/user/evaluation/Checkboxes.vue'
import Dropdown from '@/components/user/evaluation/Dropdown.vue'
import DateInput from '@/components/user/evaluation/DateInput.vue'
import TimeInput from '@/components/user/evaluation/TimeInput.vue'
import Grid from '@/components/user/evaluation/Grid.vue'
import LinearScale from '@/components/user/evaluation/LinearScale.vue'
import FileInput from '@/components/user/evaluation/FileInput.vue'
import DialogLeave from '@/components/user/course/DialogLeave'

export default {
    props: ['evaluation', 'current_content_type'],
    components: {
        RadioButtons,
        Checkboxes,
        DateInput,
        TimeInput,
        LinearScale,
        Grid,
        Dropdown,
        FileInput,
        DialogLeave
    },
    data: () => ({
        dialog: false,
        questions: [],
        answers: [],
        loading: false,
        error: []
    }),
    created(){
        this.loading = true

        if(this.current_content_type === 'course_evaluation'){
            this.getCourseEvaluationContentAction(this.evaluation.id).then(res => {
                let _evaluation = res
                if(_evaluation.is_shuffle_questions) {
                    this.questions = _evaluation.course_evaluation_questions.sort(() => Math.random() - 0.5)
                } else {
                    this.questions = _evaluation.course_evaluation_questions
                }
                this.setAnswers(_evaluation)
            }).finally(() => {
                this.loading = false
            })
        } else {
            this.getModuleEvaluationContentAction(this.evaluation.id).then(res => {
                let _evaluation = res
                if(_evaluation.is_shuffle_questions) {
                    this.questions = _evaluation.module_evaluation_questions.sort(() => Math.random() - 0.5)
                } else {
                    this.questions = _evaluation.module_evaluation_questions
                }
                this.setAnswers(_evaluation)
            })
        }
    },
    computed: {
        getColorByBgColor() {
            if(!localStorage.getItem('customization')) return '#ffffff'
            let _bg = JSON.parse(localStorage.getItem('customization')).navigation_background_color
            return (parseInt(_bg.replace('#', ''), 16) > 0xffffff / 2) ? 'black--text' : 'white--text';
        }
    },
    methods: {
        
        ...mapMutations(['alertMutation']),

        ...mapActions('usr', [
            'getCourseEvaluationContentAction',
            'getModuleEvaluationContentAction'
        ]),

        setAnswers(_evaluation){
            this.questions.forEach((item, i) => {
                this.answers.push({
                    id: this.current_content_type === 'course_evaluation' ? _evaluation.course_id : _evaluation.module_id,
                    evaluation_id: _evaluation.id,
                    evaluation_question_id: item.id,
                    type_of_question: item.type_of_question,
                    answer: '',
                    file_attachment: '',
                })
            })
            this.loading = false
        },

        back(){
            this.$emit('back')
        },
        
        submit() {
            this.error = [];

            const is_form_valid = this.$refs.form.validate();

            this.questions.forEach((item, i) => {
                if (!item.is_require_to_answer) return;

                const answer = this.answers[i];

                if (item.type_of_question === 'file_upload') {
                    const file = answer?.file_attachment;
                    if (!file || file === '' || file === 0) {
                        this.error.push(i);
                    }
                } else if (item.type_of_question === 'multiple_choice_grid' || item.type_of_question === 'checkbox_grid') {
                    if (item.data) {
                        let row = Object.keys(item.data).filter(item => item.includes('row_'))
                        if(this.answers[i].answer === '' || Object.keys(this.answers[i].answer.answer).length !== row.length){
                            this.error.push(i)
                        }
                    }
                } else {
                    const ans = answer?.answer;
                    if (ans === '' || ans === undefined || (Array.isArray(ans) && ans.length === 0)) {
                        this.error.push(i);
                    }
                }
            });

            if (is_form_valid && this.error.length === 0) {
                this.$emit('submit', this.answers);
            } else {
                this.alertMutation({
                    show: true,
                    text: 'There are unanswered questions. Please complete all questions to proceed.',
                    type: 'error'
                });
            }
        },

        saveAnswer(e) {
            let { index, answer } = e

            if(this.answers[index].type_of_question === 'file_upload') {
                this.answers[index].file_attachment = answer
            } else {
                this.answers[index].answer = { answer: answer }
            }

        },

        removeAttachment(index) {
            this.answers[index].file_attachment = ''
        },

        addText(val, index) {
            this.saveAnswer({ index: index, answer: val })
        }
    }
}
</script>