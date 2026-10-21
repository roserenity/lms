<template>
    <v-container>
        <!-- <v-radio-group v-model="answer" readonly> -->
        <v-radio-group @change="changeAnswer" v-model="answers">
            <section class="d-flex flex-row align-center my-1" v-for="n in Object.keys(choices)" :key="n">
                <v-radio
                    hide-details
                    :value="n"
                    :readonly="preview"
                >
                </v-radio>
                <v-text-field class="mt-n1 col-sm-6" outlined dense hide-details readonly v-model="choices[n]"></v-text-field>
            </section>
        </v-radio-group>
    </v-container>
</template>

<script>

export default {
    props: {
        choices: {
            type: Object,
            default: {}
        }, 
        index: {
            type: Number | null,
            default: null
        },
        question_id: {
            type: Number | null,
            default: null
        },
        preview: {
            type: Boolean,
            default: false
        },
        answer: {
            type: String | null,
            default: null
        },
    },
    data: () => ({
        radioGroup: [],
        hover: null,
        answers: ''
    }),
    mounted(){
        if(this.preview){
            this.answers = this.answer
        }
    },
    methods: {
        changeAnswer(e) {
            this.$emit('saveAnswer', {index: this.index, answer: e})
        }
    }
}
</script>