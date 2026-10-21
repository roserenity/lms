<template>
    <section>
        <!-- <v-card class="custom-border border poppins mb-3 px-5" :class="getColorByBgColor" color="primary">
            <v-card-title class="mb-5">
                {{ evaluation.title ? evaluation.title : 'Untitled' }}
            </v-card-title>
            <v-card-text v-if="evaluation.description" :class="getColorByBgColor" class="f13">{{ evaluation.description }}</v-card-text>
        </v-card> -->
        <section v-for="question in questions" :key="question.id">
            <v-card class="custom-border border poppins mb-5">
                <v-card-text class="poppins fw500 f12 secondary-1--text pb-0 px-10">QUESTION</v-card-text>
                <v-card-text class="f14 py-0 px-10" v-html="question.question  ? $dompurifier(question.question) : ''"></v-card-text>
                <v-card-text class="px-10" v-if="question.image">
                    <v-img
                        v-for="img in question.image"
                        :key="img.id"
                        :src="img.url"
                        contain
                        max-width="250"
                        class="pointer-default"
                    />
                </v-card-text>
                <v-divider />
                <section class="">

                    <RadioButtons class="px-10" v-if="question.type_of_question === 'multiple_choice'" :choices="question.data" :answer="getAnswer(question.id)" />

                    <v-card-text class="px-5" v-if="question.type_of_question === 'multiple_choice_grid'">
                        <Grid :question_id="question.id" type_of_question="multiple_choice_grid" :question="question" :answer="getAnswer(question.id)"/>
                    </v-card-text>

                    <v-card-text class="px-5" v-if="question.type_of_question === 'checkbox_grid'">
                        <Grid type_of_question="checkbox_grid" :question="question" :answer="getAnswer(question.id)"/>
                    </v-card-text>

                    <v-card-text class="px-10" v-if="question.type_of_question === 'paragraph'">
                        <v-textarea class=" col-sm-6 poppins f13" outlined dense hide-details auto-grow readonly :value="getAnswer(question.id)"></v-textarea>
                    </v-card-text>

                    <Checkboxes class="px-10" v-if="question.type_of_question === 'checkbox'" :choices="question.data" :answer="getAnswer(question.id)" />

                    <v-card-text class="px-10" v-if="question.type_of_question === 'short_answer'">
                        <v-text-field class=" col-sm-6 poppins f12" outlined dense hide-details readonly :value="getAnswer(question.id)"></v-text-field>
                    </v-card-text>

                    <TimeInput class="px-10" v-if="question.type_of_question === 'time'" :answer="getAnswer(question.id)"/>
                    
                    <DateInput class="px-10" v-if="question.type_of_question === 'date'" :answer="getAnswer(question.id)"/>

                    <v-card-text class="px-8" v-if="question.type_of_question === 'linear_scale'">
                        <LinearScale :question="question" :answer="getAnswer(question.id)"/>
                    </v-card-text>

                    <v-card-text class="px-8" v-if="question.type_of_question === 'dropdown'">
                        <Dropdown :choices="question.data" :answer="getAnswer(question.id)"/>
                    </v-card-text>

                    <v-card-text class="px-10" v-if="question.type_of_question === 'file_upload'">
                        <div class="container-frame" v-if="getAttachedFiles(question.id)">
                            <iframe 
                                class="responsive-iframe"
                                :src="getAttachedFiles(question.id)" 
                            ></iframe>
                        </div>
                        <div v-else class="poppins fw600 f13 secondary-4--text font-italic"> Nothing attached. </div>
                    </v-card-text>
                </section>
            </v-card>
        </section>
        <!-- <section v-for="question in evaluation.answers" :key="question.id" v-if="$route.params.type === 'course_evaluation'">
            <v-card class="custom-border border poppins mb-5">
                <v-card-text class="poppins fw500 f12 secondary-1--text pb-0 px-10">QUESTION</v-card-text>
                <v-card-text class="f12 py-0 px-10" v-html="question.course_evaluation_question.question  ? $dompurifier(question.course_evaluation_question.question) : ''"></v-card-text>
                <v-card-text class="px-10" v-if="question.course_evaluation_question.image">
                    <v-img
                        v-for="img in question.course_evaluation_question.image"
                        :key="img.id"
                        :src="img.url"
                        contain
                        max-width="250"
                        class="pointer-default"
                    />
                </v-card-text>
                <v-divider />
                
                
                <v-card-text class="px-10" v-if="question.course_evaluation_question.type_of_question === 'file_upload'">
                    <div class="container-frame" v-if="question.image">
                        <iframe 
                            class="responsive-iframe"
                            :src="question.image.url" 
                           ></iframe>
                    </div>
                    <div v-else class="poppins fw600 f13 secondary-4--text font-italic"> Nothing attached. </div>
                </v-card-text>
            </v-card>
        </section>
        <section v-for="question in evaluation.answers" :key="question.id" v-if="$route.params.type === 'module_evaluation'">
            <v-card class="custom-border border poppins mb-5">
                <v-card-text class="poppins fw500 f12 secondary-1--text pb-0 px-10">QUESTION</v-card-text>
                <v-card-text class="f12 py-0 px-10" v-html="question.module_evaluation_question.question  ? $dompurifier(question.module_evaluation_question.question) : ''"></v-card-text>
                <v-card-text class="px-10" v-if="question.module_evaluation_question.image">
                    <v-img
                        v-for="img in question.module_evaluation_question.image"
                        :key="img.id"
                        :src="img.url"
                        contain
                        max-width="250"
                        class="pointer-default"
                    />
                </v-card-text>
                <v-divider />
                <RadioButtons class="px-10" v-if="question.module_evaluation_question.type_of_question === 'multiple_choice'" :choices="question.module_evaluation_question.data" :answer="JSON.parse(question.data).answer" />
                <Checkboxes class="px-10" v-if="question.module_evaluation_question.type_of_question === 'checkbox'" :choices="question.module_evaluation_question.data" :answer="JSON.parse(question.data).answer" />
                <v-card-text class="px-8" v-if="question.module_evaluation_question.type_of_question === 'dropdown'">
                    <Dropdown :choices="question.module_evaluation_question.data" :answer="JSON.parse(question.data).answer"/>
                </v-card-text>
                <TimeInput class="px-10" v-if="question.module_evaluation_question.type_of_question === 'time'" :answer="JSON.parse(question.data).answer"/>
                <DateInput class="px-10" v-if="question.module_evaluation_question.type_of_question === 'date'" :answer="JSON.parse(question.data).answer"/>
                <v-card-text class="px-8" v-if="question.module_evaluation_question.type_of_question === 'linear_scale'">
                    <LinearScale :question="question" :answer="JSON.parse(question.data).answer"/>
                </v-card-text>
                <v-card-text class="px-5" v-if="question.module_evaluation_question.type_of_question === 'checkbox_grid'">
                    <Grid type_of_question="checkbox_grid" :question="question" :answer="JSON.parse(question.data).answer"/>
                </v-card-text>
                <v-card-text class="px-5" v-if="question.module_evaluation_question.type_of_question === 'multiple_choice_grid'">
                    <Grid type_of_question="multiple_choice_grid" :question="question" :answer="JSON.parse(question.data).answer"/>
                </v-card-text>
                <v-card-text class="px-10" v-if="question.module_evaluation_question.type_of_question === 'short_answer'">
                    <v-text-field class=" col-sm-6 poppins f12" outlined dense hide-details readonly v-model="JSON.parse(question.data).answer"></v-text-field>
                </v-card-text>
                <v-card-text class="px-10" v-if="question.module_evaluation_question.type_of_question === 'paragraph'">
                    <v-textarea class=" col-sm-6 poppins f13" outlined dense hide-details auto-grow readonly v-model="JSON.parse(question.data).answer"></v-textarea>
                </v-card-text>
                <v-card-text class="px-10" v-if="question.module_evaluation_question.type_of_question === 'file_upload'">
                    <div class="container-frame" v-if="question.image">
                        <iframe 
                            class="responsive-iframe"
                            :src="question.image.url" 
                           ></iframe>
                    </div>
                    <div v-else class="poppins fw600 f13 secondary-4--text font-italic"> Nothing attached. </div>
                </v-card-text>
            </v-card>
        </section> -->
                <!-- 
                <v-card-text v-if="question.type_of_question === 'file_upload'">
                    <v-file-input
                        truncate-length="15"
                        class="col-sm-6"
                    ></v-file-input>
                </v-card-text> -->
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
import { mapActions, mapMutations, mapState } from 'vuex'
import RadioButtons from '../evaluation/RadioButtons.vue';
import Checkboxes from '../evaluation/Checkboxes.vue';
import Dropdown from '../evaluation/Dropdown.vue';
import LinearScale from '../evaluation/LinearScale.vue';
import Grid from '../evaluation/Grid.vue';
import TimeInput from '../evaluation/TimeInput.vue';
import DateInput from '../evaluation/DateInput.vue';

export default {
    props: ['evaluation', 'questions'],
    components: {
        RadioButtons,
        Checkboxes,
        Dropdown,
        LinearScale,
        Grid,
        TimeInput,
        DateInput
    },
    methods: {
        getAnswer(id){
            if(this.$route.params.type === 'course_evaluation'){
                let _answer = this.evaluation_answers.find(item => item.course_evaluation_question_id === id)
                if(_answer) {
                    return JSON.parse(_answer.data).answer
                }

                return _answer
            }
            if(this.$route.params.type === 'module_evaluation'){
                
                let _answer = this.evaluation_answers.find(item => item.module_evaluation_question_id === id)
                if(_answer) {
                    return JSON.parse(_answer.data).answer
                }

                return _answer
            }
        },
        getAttachedFiles(id){
            if(this.$route.params.type === 'course_evaluation'){
                let _answer = this.evaluation_answers.find(item => item.course_evaluation_question_id === id)
                if(_answer && _answer.image) {
                    return _answer.image.url
                }

                return null
            }
            if(this.$route.params.type === 'module_evaluation'){
                
                let _answer = this.evaluation_answers.find(item => item.module_evaluation_question_id === id)
                if(_answer && _answer.image) {
                    return _answer.image.url
                }

                return null
            }
        }
    },
    computed: {
        getColorByBgColor() {
            if(!localStorage.getItem('customization')) return '#ffffff'
            let _bg = JSON.parse(localStorage.getItem('customization')).navigation_background_color
            return (parseInt(_bg.replace('#', ''), 16) > 0xffffff / 2) ? 'black--text' : 'white--text';
        },
        ...mapState('instructor', {
            evaluation_answers: (state) => state.evaluation_answers,
        }),
    },
}
</script>