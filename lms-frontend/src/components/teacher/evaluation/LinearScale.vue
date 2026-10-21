<template>
  <v-container>
    <section>
      <v-radio-group
        v-model="answer"
        row
        id="radio-group"
        class="mx-1"
      >
        
        <section class="d-flex flex-column align-center justify-end f12 poppins mr-1 mb-3">
          {{ question.data['scale_1'] }}
        </section>
        <section class="d-flex flex-column align-center justify-center f12 poppins mb-3" v-for="i in max_length" :key="i">
          <span class="mr-2">
            {{ `${i}` }}
          </span>
          <v-radio
            :value="`scale_${i}`"
            class="mx-1"
            hide-details
          ></v-radio>
        </section>
        <section class="d-flex flex-column align-center justify-end f12 poppins mb-3">
          {{  question.data[`scale_${max_length}`] }}
        </section>
      </v-radio-group>
    </section>
  </v-container>    
</template>

<style>
  #radio-group {
    flex-wrap: nowrap !important; 
    overflow-x: auto !important;
    overflow-y: hidden !important;
  }
</style>

<script>
export default {
  props: ['question', 'answer'],
  data: () => ({
    // answer: 'scale_2'
  }),
  computed: {
    max_length(){
      return parseInt(Object.keys(this.question.data)[1].replace('scale_', ''))
    }
  }
}
</script>