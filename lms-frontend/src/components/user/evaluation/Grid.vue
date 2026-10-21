<template>
  <v-container>
      <section class="d-flex" v-if="type_of_question === 'checkbox_grid'">
        <v-simple-table>
          <template v-slot:default>
            <thead>
              <tr>
                <th class="text-left">
                </th>
                <th class="text-center" v-for="i in Object.keys(question.data).filter(item => item.includes('col_'))">
                  {{ question.data[i] }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="i in Object.keys(question.data).filter(item => item.includes('row_'))"
              >
                <td>{{ question.data[i] }}</td>
                <td v-for="j in Object.keys(question.data).filter(item => item.includes('col_'))">
                  <v-checkbox
                    class="mt-0"
                    :style="'padding: inherit'"
                    hide-details
                    :value="`${i}_${j}`"
                    v-model="checkbox_answers"
                    :readonly="preview"
                  ></v-checkbox>
                </td>
              </tr>
            </tbody>
          </template>
        </v-simple-table>
      </section>

      <section class="d-flex" v-if="type_of_question === 'multiple_choice_grid'">
        <v-simple-table>
          <template v-slot:default>
            <thead>
              <tr>
                <th class="text-left">
                </th>
                <th class="text-center" v-for="i in Object.keys(question.data).filter(item => item.includes('col_'))">
                  {{ question.data[i] }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(i, index) in Object.keys(question.data).filter(item => item.includes('row_'))"
              >
                <td>{{ question.data[i] }}</td>
                <td v-for="j in Object.keys(question.data).filter(item => item.includes('col_'))">
                  <v-radio-group v-model="radioGroup[index]"  hide-details row class="mt-0" :readonly="preview">
                    <v-radio
                        hide-details
                        class="ma-auto"
                        :value="`${i}_${j}`"
                      >
                      </v-radio>
                  </v-radio-group>
                </td>
              </tr>
            </tbody>
          </template>
        </v-simple-table>
      </section>
  </v-container>
</template>

<script>
export default {
props: ['question', 'type_of_question', 'index', 'preview', 'answer'],
  data: () => ({
    // radioGroup: {
    //   0: 'row_0_col_2',
    //   1: 'row_1_col_1'
    // },
    //checkbox_answers: ['row_1_col_1', 'row_0_col_2', 'row_0_col_1',]
    radioGroup: {},
    checkbox_answers: []
  }),
  mounted() {
    if(this.preview) {
      this.radioGroup = this.answer
      this.checkbox_answers = this.answer
    }
  },
  watch: {
    checkbox_answers(e) {
        this.$emit('saveAnswer', {index: this.index, answer: e})
    },
    radioGroup(e) {
        this.$emit('saveAnswer', {index: this.index, answer: e})
    }
  }
}
</script>