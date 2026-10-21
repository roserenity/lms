<template>
  <v-dialog persistent v-model="dialog" max-width="650">
    <v-card>
      <v-card-title class="d-flex align-center justify-space-between ma-0 pa-0">
        <div class=" f14 poppins primary--text px-5 py-4 fw600">QUESTIONS</div>
        <v-btn icon @click="$emit('close')" class="mr-2">
          <v-icon size="20">mdi-close</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text>
        <h3 class="secondary-1--text poppins">
          {{ data.title ? data.title : 'Untitled' }}
        </h3>
        <div class="secondary--text f14 fw600 roboto">Course: {{ data.course.title }}</div>
        
        <v-sheet outlined max-height="300" class=" my-5 overflow-y-auto scroller">
          <v-simple-table dense>
            <template v-slot:default>
              <tbody>
                <tr v-for="(item, i) in data.assessment_questions" :key="item.id">
                  <td class="secondary-2--text">{{i+1}}.</td>
                  <td>
                    <span
                      v-html="!item.question ? '-' : $dompurifier(item.question)"
                      class="overflow-y-hidden secondary-2--text fw400 roboto inner_p"
                    ></span>
                  </td>
                </tr>
              </tbody>
            </template>
            
          </v-simple-table>
        </v-sheet>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
export default {
  props: ["dialog", "data"],
  data: () => ({
    selected: [],
    loading: false,
  }),

  methods: {
    ...mapActions("instructor", []),
    ...mapMutations(["alertMutation"]),
  },
};
</script>
