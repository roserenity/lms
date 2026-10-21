<template>
    <section>
        <v-btn 
            text 
            color="primary" 
            dense 
            class="px-1" 
            @click="$emit('back')">
            <v-icon small>
            mdi-chevron-left
            </v-icon>
            BACK
        </v-btn>
        <v-card class="custom-border border poppins mb-3 white--text" color="primary">
            <v-card-title class="mb-5">
                {{  evaluation.title }}
            </v-card-title>
            <v-card-text v-html="evaluation.description ? $dompurifier(evaluation.description) : ''" class="white--text f12"></v-card-text>
        </v-card>

        <v-card class="custom-border border poppins mb-2" v-for="(question, i) in questions" :key="question.id" v-if="!loading">
            <v-card-text class="poppins fw500 f13 secondary-1--text pb-0">QUESTION</v-card-text>
            <v-card-text v-html="question.question ? $dompurifier(question.question) : ''" class="f16 py-0"></v-card-text>
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
            <RadioButtons v-if="question.type_of_question === 'multiple_choice'" :choices="question.data" :index="i" :preview="true" :answer="question.answer && question.answer.data && JSON.parse(question.answer.data).answer"/>
            <Checkboxes v-if="question.type_of_question === 'checkbox'" :choices="question.data" :index="i" :preview="true" :answer="question.answer && question.answer.data && JSON.parse(question.answer.data).answer"/>
            <Dropdown v-if="question.type_of_question === 'dropdown'" :choices="question.data" :index="i" :preview="true" :answer="question.answer && question.answer.data && JSON.parse(question.answer.data).answer"/>
            <DateInput v-if="question.type_of_question === 'date'" :index="i" :preview="true" :answer="question.answer && question.answer.data && JSON.parse(question.answer.data).answer"/>
            <TimeInput v-if="question.type_of_question === 'time'" :index="i" :preview="true" :answer="question.answer && question.answer.data && JSON.parse(question.answer.data).answer"/>
            <v-card-text v-if="question.type_of_question === 'short_answer'">
                <v-text-field class=" col-sm-6 poppins f12" outlined dense hide-details readonly :value="question.answer && question.answer.data && JSON.parse(question.answer.data).answer"></v-text-field>
            </v-card-text>
            <v-card-text v-if="question.type_of_question === 'paragraph'">
                <v-textarea class=" col-sm-6 poppins f12" outlined dense hide-details auto-grow readonly :value="question.answer && question.answer.data && JSON.parse(question.answer.data).answer"></v-textarea>
            </v-card-text>
            <v-card-text v-if="question.type_of_question === 'linear_scale'">
                <LinearScale :question="question" :index="i" :preview="true" :answer="question.answer && question.answer.data && JSON.parse(question.answer.data).answer"/>
            </v-card-text>
            <v-card-text v-if="question.type_of_question === 'checkbox_grid'">
                <Grid :question="question" type_of_question="checkbox_grid" :index="i" :preview="true" :answer="question.answer && question.answer.data && JSON.parse(question.answer.data).answer"/>
            </v-card-text>
            <v-card-text v-if="question.type_of_question === 'multiple_choice_grid'">
                <Grid :question="question" type_of_question="multiple_choice_grid" :index="i" :preview="true" :answer="question.answer && question.answer.data && JSON.parse(question.answer.data).answer"/>
            </v-card-text>
            <v-card-text class="px-10" v-if="question.type_of_question === 'file_upload'">
                <div class="container-frame" v-if="question.answer && question.answer.image">
                    <iframe 
                        class="responsive-iframe"
                        :src="question.answer.image.url" 
                        ></iframe>
                </div>
                <div v-else class="poppins fw600 f13 secondary-4--text font-italic"> Nothing attached. </div>
            </v-card-text>
        </v-card>
        <LinearProgress v-if="loading" class="mb-2"/>
    </section>
</template>

<style>
.container-frame {
  position: relative;
  overflow: hidden;
  width: 100%; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
}

.responsive-iframe {
  height: 500px;
}

@media only screen and (max-width: 768px){
  .responsive-iframe {
    height: 300px;
    width: 100%;
  }
}

</style> 

<script>
import { mapActions } from "vuex"
import RadioButtons from '../evaluation/RadioButtons.vue'
import Checkboxes from '../evaluation/Checkboxes.vue'
import Dropdown from '../evaluation/Dropdown.vue'
import DateInput from '../evaluation/DateInput.vue'
import TimeInput from '../evaluation/TimeInput.vue'
import LinearScale from '../evaluation/LinearScale.vue'
import Grid from '../evaluation/Grid.vue'

export default {
    props: ['evaluation', 'current_content_type'],
    components: {
        RadioButtons,
        Checkboxes,
        Dropdown,
        DateInput,
        TimeInput,
        LinearScale,
        Grid
    },
    data: () => ({
        questions: [],
        loading: false
    }),
    created(){
        this.loading = true

        if(this.current_content_type === 'course_evaluation'){
            this.getCourseEvaluationContentAction(this.evaluation.id).then(res => {
                this.questions = res.course_evaluation_questions
            }).finally(() => {
                this.loading = false
            })
        } else {
            this.getModuleEvaluationContentAction(this.evaluation.id).then(res => {
                this.questions = res.module_evaluation_questions
            }).finally(() => {
                this.loading = false
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
        ...mapActions('usr', [
            'getCourseEvaluationContentAction',
            'getModuleEvaluationContentAction'
        ]),
    }
}
</script>