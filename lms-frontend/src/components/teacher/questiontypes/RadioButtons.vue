<template>
    <v-container fluid>
        <v-radio-group v-model="radioGroup" v-if="!preview">
            <v-hover v-for="n in length" :key="n" v-slot="{ hover }">
                <section class="d-flex flex-row align-center mb-2" :class="{ 'on-hover': hover }">
                    <v-radio
                        hide-details
                        :disabled="disabled"
                    >
                    </v-radio>
                    
                    <v-text-field class="mt-n1 col-6" outlined dense hide-details v-model="question.data[`choice_${n-1}`]"></v-text-field>
                    <v-btn icon dense v-if="(hover || $vuetify.breakpoint.smAndDown) && length > 1" @click="$emit('removeChoice', { id: question.id, index: n-1})">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </section>
            </v-hover>
        </v-radio-group>
        <v-radio-group v-model="radioGroup" v-if="preview" disabled>
                <section class="d-flex flex-row align-center my-1" v-for="n in Object.keys(choices).length" :key="n">
                    <v-radio
                        hide-details
                        :value="choices[`choice_${n-1}`]"
                    >
                    </v-radio>
                    <v-text-field class="mt-n1 col-sm-6" outlined dense hide-details disabled v-model="choices[`choice_${n-1}`]"></v-text-field>
                </section>
        </v-radio-group>
    </v-container>
</template>

<script>
import { mapState } from 'vuex'

export default {
    props: {
        question: {
            type: String,
            default: ''
        },
        length: {
            type: Number,
            default: 0
        },
        preview: {
            type: Boolean,
            default: false
        },
        choices: {
            type: Array,
            default: []
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    data: () => ({
        radioGroup: [],
        hover: null
    }),
}
</script>