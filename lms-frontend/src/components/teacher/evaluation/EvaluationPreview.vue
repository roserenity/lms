<template>
    <v-container>
        <v-btn
            dense
            text class="f12 text-capitalize primary-font"
            color="primary"
            @click="$emit('close')">
            Back
        </v-btn>
        <v-card class="custom-border border poppins mb-3 white--text" color="primary">
            <v-card-title class="mb-5">
                {{ evaluation.title ? evaluation.title : 'Untitled' }}
            </v-card-title>
            <v-card-text v-if="evaluation.description" class="f13 white--text" v-html="evaluation.description ? $dompurifier(evaluation.description) : ''"></v-card-text>
        </v-card>

        <section v-for="question in evaluation.course_evaluation_questions" :key="question.id">
            <v-card class="custom-border border poppins mb-2">
                <v-card-text class="poppins fw500 f12 secondary-1--text pb-0">QUESTION</v-card-text>
                <v-card-text class="f12 py-0" v-html="question.question ? $dompurifier(question.question) : ''"></v-card-text>
                <v-card-text>
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
                <RadioButtons v-if="question.type_of_question === 'multiple_choice'" :preview="true" :choices="question.data" />
                <Checkboxes v-if="question.type_of_question === 'checkbox'" :preview="true" question_id="1" length="1" :choices="question.data" />
                <DateInput v-if="question.type_of_question === 'date'"/>
                <TimeInput v-if="question.type_of_question === 'time'"/>
                <v-card-text v-if="question.type_of_question === 'short_answer'">
                    <v-text-field class=" col-sm-6 poppins f12" outlined dense hide-details disabled></v-text-field>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'paragraph'">
                    <v-textarea class=" col-sm-6 poppins f13" outlined dense hide-details auto-grow clearable disabled></v-textarea>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'dropdown'">
                    <Dropdown :preview="true" :choices="question.data" />
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'linear_scale'">
                    <LinearScale :preview="true" :question="question"/>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'checkbox_grid'">
                    <Grid :preview="true" type_of_question="checkbox_grid" :question="question"/>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'multiple_choice_grid'">
                    <Grid :preview="true" type_of_question="multiple_choice_grid" :question="question"/>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'file_upload'">
                    <v-file-input
                        truncate-length="15"
                        class="col-sm-6"
                    ></v-file-input>
                </v-card-text>
            </v-card>
        </section>
        <section v-for="question in evaluation.module_evaluation_questions" :key="question.id">
            <v-card class="custom-border border poppins mb-2">
                <v-card-text class="poppins fw500 f12 secondary-1--text pb-0">QUESTION</v-card-text>
                <v-card-text class="f12 py-0"> {{ question.question }} <span v-if="question.is_require_to_answer">*</span></v-card-text>
                <v-card-text>
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
                <RadioButtons v-if="question.type_of_question === 'multiple_choice'" :preview="true" :choices="question.data" />
                <Checkboxes v-if="question.type_of_question === 'checkbox'" :preview="true" question_id="1" length="1" :choices="question.data" />
                <DateInput v-if="question.type_of_question === 'date'"/>
                <TimeInput v-if="question.type_of_question === 'time'"/>
                <v-card-text v-if="question.type_of_question === 'short_answer'">
                    <v-text-field class=" col-sm-6 poppins f12" outlined dense hide-details disabled></v-text-field>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'paragraph'">
                    <v-textarea class=" col-sm-6 poppins f13" outlined dense hide-details auto-grow clearable disabled></v-textarea>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'dropdown'">
                    <Dropdown :preview="true" :choices="question.data" />
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'linear_scale'">
                    <LinearScale :preview="true" :question="question"/>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'checkbox_grid'">
                    <Grid :preview="true" type_of_question="checkbox_grid" :question="question"/>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'multiple_choice_grid'">
                    <Grid :preview="true" type_of_question="multiple_choice_grid" :question="question"/>
                </v-card-text>
                <v-card-text v-if="question.type_of_question === 'file_upload'">
                    <v-file-input
                        truncate-length="15"
                        class="col-sm-6"
                    ></v-file-input>
                </v-card-text>
            </v-card>
        </section>
    </v-container>
</template>

<script>
import RadioButtons from '../questiontypes/RadioButtons.vue'
import Checkboxes from '../questiontypes/Checkboxes.vue'
import DateInput from '../questiontypes/DateInput.vue'
import TimeInput from '../questiontypes/TimeInput.vue'
import Dropdown from '../questiontypes/Dropdown.vue'
import LinearScale from '../questiontypes/LinearScale.vue'
import Grid from '../questiontypes/Grid.vue'

export default {
    props: ['evaluation'],
    components: {
        RadioButtons,
        Checkboxes,
        DateInput,
        TimeInput,
        Dropdown,
        LinearScale,
        Grid
    },
    computed: {
        getColorByBgColor() {
            if(!localStorage.getItem('customization')) return '#ffffff'
            let _bg = JSON.parse(localStorage.getItem('customization')).navigation_background_color
            return (parseInt(_bg.replace('#', ''), 16) > 0xffffff / 2) ? 'black--text' : 'white--text';
        }
    },
}
</script>