<template>
    <v-container fluid>
        <section class="d-flex" v-if="!preview">
          <section class="mx-2">
            <label class="poppins fw500 f12 secondary-2--text mb-2">ROWS</label>
            <section v-for="n in Object.keys(question.data).filter(item => item.includes('row_')).length" class="d-flex flex-row">
              <v-text-field
                outlined
                dense
                solo
                hide-details
                flat
                required
                v-model="question.data[`row_${n-1}`]"
                class="f12 general-custom-field secondary-1--text fw600 roboto my-1"
              />
              <v-btn icon dense @click="$emit('removeChoice', { id: question.id, index: `row_${n-1}`})">
                  <v-icon>mdi-close</v-icon>
              </v-btn>
            </section>
            <v-btn
              text
              color="primary"
              class="roboto f12 fw600 no-space"
              @click="$emit('addChoice', { id: question.id, name: `row_${Object.keys(question.data).filter(item => item.includes('row_')).length}`})"
              >Add rows
            </v-btn>
          </section>
          <section class="mx-2">
            <label class="poppins fw500 f12 secondary-2--text mb-2">COLUMNS</label>
            <section v-for="n in Object.keys(question.data).filter(item => item.includes('col_')).length" class="d-flex flex-row">
              <v-text-field
                outlined
                dense
                solo
                hide-details
                flat
                required
                v-model="question.data[`col_${n-1}`]"
                class="f12 general-custom-field secondary-1--text fw600 roboto my-1"
              />
              <v-btn icon dense @click="$emit('removeChoice', { id: question.id, index: `col_${n-1}`})">
                  <v-icon>mdi-close</v-icon>
              </v-btn>
            </section>
            <v-btn
              text
              color="primary"
              class="roboto f12 fw600 no-space"
              @click="$emit('addChoice', { id: question.id, name: `col_${Object.keys(question.data).filter(item => item.includes('col_')).length}`})"
              >Add columns
            </v-btn>
          </section>
        </section>

        <section class="d-flex" v-if="preview && type_of_question === 'checkbox_grid'">
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
                  <td v-for="i in Object.keys(question.data).filter(item => item.includes('col_'))">
                    <v-checkbox
                      disabled
                      class="mt-0"
                      :style="'padding: inherit'"
                      hide-details
                    ></v-checkbox>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </section>

        <section class="d-flex" v-if="preview && type_of_question === 'multiple_choice_grid'">
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
                    <v-radio-group v-model="radioGroup[index]"  hide-details row class="mt-0" disabled>
                      <v-radio
                          hide-details
                          class="ma-auto"
                          :value="j"
                        >
                        </v-radio>
                    </v-radio-group>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </section>

        <!-- <section class="d-flex flex-column" v-if="preview && type_of_question === 'multiple_choice_grid'">
          <v-radio-group v-model="radioGroup" disabled row v-for="i in row" hide-details>
            <section class="d-flex flex-column align-center" v-for="j in column">
                <span v-if="i === 1">COLUMN j</span>
                <v-radio
                  hide-details
                >
                </v-radio>
            </section>
          </v-radio-group>
        </section> -->
    </v-container>
</template>

<script>
export default {
  props: ['preview', 'question_id', 'question', 'type_of_question'],
  data: () => ({
    row: 6,
    column: 8,
    radioGroup: {},
  })
}
</script>