<template>
    <v-slider
        @change="$emit('setAnswer', answers)"
        v-model="answers"
        :tick-labels="labels"
        :max="max"
        step="1"
        ticks="always"
        tick-size="7"
        class="f11 px-5"
        :readonly="readonly">
    </v-slider>
</template>

<script>
export default {
    props: {
        max: {
            type: Number,
            default: 1
        },
        answer: {
            type: Number || String,
            default: ""
        },
        readonly: {
            type: Boolean,
            default: false
        },
        labels: {
            type: Array,
            default: []
        }
    },
    mounted(){
        this.answers = this.answer
    },
    data: () => ({
        answers: ''
    })
}
</script>