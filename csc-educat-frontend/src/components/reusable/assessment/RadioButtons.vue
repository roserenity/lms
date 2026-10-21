<template>
    <v-radio-group
      dense
      @change="$emit('setAnswer', answers)"
      v-model="answers"
      :readonly="readonly"
    >
      <v-row align="start" justify="center">
        <v-col cols="12" lg="10">
          <v-row no-gutters>
            <v-col
              cols="12"
              md="6"
              v-for="(choice, i) in choices" :key="choice.id"
              class="d-flex align-center secondary-1--text f14 my-2"
            >
              <v-radio class="pt-2" :value="Object.keys(choice)[0]"/>
              <div class="roboto f14 fw500 secondary-1--text">{{choice[`${Object.keys(choice)[0]}`]}}</div>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-radio-group>
</template>

<script>
export default {
    props: {
        choices: {
            type: Array,
            default: []
        },
        answer: {
            type: String,
            default: ''
        },
        readonly: {
            type: Boolean,
            default: false
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