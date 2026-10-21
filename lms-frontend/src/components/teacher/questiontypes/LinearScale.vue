<template>
  <v-container fluid>
    <section v-if="!preview">
      <v-container class="d-flex flex-wrap mb-2">
        <select
          class="input-sm mr-2 roboto fw500 f12 secondary-3--text"
          value="0"
          disabled
        >
          <option
            value="0"
            class="roboto fw500 f12 secondary-3--text"
          >
            1
          </option>
        </select>
        -
        <select
          class="input-sm ml-2"
          v-model="max_scale"
        >
          <option
            v-for="(value, i) in max"
            :key="i"
            :value="value"
            class="roboto fw500 f12 secondary-3--text"
          >
            {{ value }}
          </option>
        </select>
      </v-container>
      <v-container class="d-flex flex-row align-center mb-2">
          <label class="poppins fw500 f12 secondary-2--text">1</label>
          <v-text-field
            outlined
            dense
            solo
            hide-details
            flat
            required
            class="f12 general-custom-field secondary-1--text fw600 roboto mt-n1 col-6 ml-3"
            v-model="question.data.scale_1"
          />
      </v-container>
      <v-container class="d-flex flex-row align-center">
          <label class="poppins fw500 f12 secondary-2--text"> {{  Object.keys(question.data)[1].replace('scale_', '') }}</label>
          <v-text-field
            outlined
            dense
            solo
            hide-details
            flat
            required
            class="f12 general-custom-field secondary-1--text fw600 roboto mt-n1 col-6"
            :class="parseInt(Object.keys(question.data)[1].replace('scale_', '')) < 10 ? 'ml-3' : 'ml-1'"
            v-model="question.data[Object.keys(question.data)[1]]"
          />
      </v-container>
    </section>
    <section v-if="preview">
      <v-radio-group
        id="radio-group"
        class="mx-1"
        :disabled="true"
        row
      >
        
        <section class="d-flex flex-column align-center justify-end f12 poppins mr-1 mb-3">
          {{ question.data['scale_1'] }}
        </section>
        <section class="d-flex flex-column align-center justify-center f12 poppins mb-3" v-for="i in max_length" :key="i">
          <span class="mr-2">
            {{ `${i}` }}
          </span>
          <v-radio
            :value="`radio-${i}`"
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
  props: ['preview', 'question', 'index'],
  data: () => ({
      min: [ 0, 1 ],
      max: [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ],
  }),
  computed: {
    max_scale: {
      get() {
        return Object.keys(this.question.data)[1].replace('scale_', '')
      },
      set(val) {
        let _prev_value = {
          [Object.keys(this.question.data)[1]]:this.question.data[Object.keys(this.question.data)[1]]
        }
        let _new_val = {
          scale_1: this.question.data['scale_1'],
          [`scale_${val}`]: Object.values(_prev_value)[0]
        }
        
        this.$emit('changeChoice' , { index: this.index, new_choices: _new_val})
      }
    },

    max_length(){
      return parseInt(Object.keys(this.question.data)[1].replace('scale_', ''))
    }
  }
}
</script>