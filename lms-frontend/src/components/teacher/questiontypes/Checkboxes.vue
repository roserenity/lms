<template>
    <v-container fluid>
        <v-hover v-for="n in length" :key="n" v-slot="{ hover }" v-if="!preview">
            <section class="d-flex flex-row align-center mb-2" :class="{ 'on-hover': hover }">
                <v-checkbox
                    :value="n"
                    hide-details
                    class="mt-0"
                    :disabled="disabled"
                >
                </v-checkbox>
                
                <v-text-field class="mt-n1 col-6" outlined dense hide-details v-model="question.data[`choice_${n-1}`]"></v-text-field>
                <v-btn icon dense v-if="(hover || $vuetify.breakpoint.smAndDown) && length > 1" @click="$emit('removeChoice', { id: question.id, index: n-1})">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </section>
        </v-hover>
        <section class="my-4 f12" v-if="preview">
            <section class="d-flex flex-row align-center my-2" v-for="n in Object.keys(choices).length" :key="n">
                <v-checkbox
                    :value="choices[`choice_${n-1}`]"
                    hide-details
                    class="mt-0"
                    disabled
                >
                </v-checkbox>
                
                <v-text-field class="mt-n1 col-sm-6" outlined dense hide-details disabled v-model="choices[`choice_${n-1}`]"></v-text-field>
            </section>
        </section>
    </v-container>
</template>

<script>
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
        hover: null
    })
}
</script>