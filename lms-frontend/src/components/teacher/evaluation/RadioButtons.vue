<template>
    <v-container>
        <v-radio-group v-model="answer" readonly hide-details>
                <section class="d-flex flex-row align-center my-1" v-for="n in Object.keys(choices)" :key="n">
                    <v-radio
                        hide-details
                        :value="n"
                    >
                    </v-radio>
                    <v-text-field class="mt-n1 col-sm-6" outlined dense hide-details readonly v-model="choices[n]"></v-text-field>
                </section>
        </v-radio-group>
    </v-container>
</template>

<script>
import { mapState } from 'vuex'

export default {
    props: ['answer', 'choices'],
    data: () => ({
        radioGroup: [],
        hover: null,
        // answer: 'choice_0'
    }),
    computed: {
    },
}
</script>