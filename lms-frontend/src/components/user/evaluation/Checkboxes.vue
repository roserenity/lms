<template>
    <v-container>
        <section class="my-4 f12">
            <section class="d-flex flex-row align-center my-2" v-for="n in Object.keys(choices)" :key="n">
                <v-checkbox
                    :value="n"
                    hide-details
                    class="mt-0"
                    v-model="answers"
                    :readonly="preview"
                >
                </v-checkbox>
                
                <v-text-field class="mt-n1 col-sm-6" outlined dense hide-details readonly v-model="choices[n]"></v-text-field>
            </section>
        </section>
    </v-container>
</template>

<script>
export default {
    props: ['choices', 'index', 'preview', 'answer'],
    data: () => ({
        hover: null,
        answers: []
    }),
    mounted() {
        if(this.preview) {
            this.answers = this.answer
        }
    },
    watch: {
        answers(e) {
            this.$emit('saveAnswer', {index: this.index, answer: e})
        }
    }
}
</script>