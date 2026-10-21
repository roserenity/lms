<template>
    <v-container>
        <section class="my-4 f12">
            <!-- <v-select
                outlined
                dense
                hide-details
                class=" col-sm-6 poppins f13"
                :items="Object.values(choices)"
                v-model="choices['choice_1']"
                readonly
                ></v-select> -->
                <v-select
                outlined
                dense
                hide-details
                class=" col-sm-6 poppins f13"
                :items="Object.values(choices)"
                v-model="answers"
                :readonly="preview"
                ></v-select>
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
            this.answers = this.choices[this.answer]
        }
    },
    watch: {
        answers(e) {
            let index = Object.values(this.choices).findIndex(item => item === e)
            let _key = Object.keys(this.choices)[index]
            this.$emit('saveAnswer', {index: this.index, answer: _key})
        }
    }
}
</script>