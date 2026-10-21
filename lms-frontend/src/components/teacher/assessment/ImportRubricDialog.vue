<template>
  <v-dialog persistent v-model="dialog" max-width="550">
    <v-card class="">
      <v-card-title class="d-flex align-center justify-space-between ma-0 pa-0">
        <h4 class="poppins primary--text px-5 py-4 fw600">Import Rubric</h4>
      </v-card-title>
      <v-card-text class="my-2">
        <label class="caption">TITLE</label>
        <v-select 
          outlined 
          dense 
          :loading="loading"
          :items="data"
          v-model="selected"
          item-value="id"
          hide-details="auto"
          clearable
          >
            <template v-slot:item="{item, attrs, on}">
                <v-list-item-content class="roboto">
                    <v-list-item-title>
                      {{ item.title }}
                    </v-list-item-title>
                    <v-list-item-subtitle>
                      {{ item.description }}
                    </v-list-item-subtitle>
                </v-list-item-content>
            </template>
            <template slot="selection"  slot-scope="data">
                <div class="roboto f14 fw500 my-1">
                  <div class="secondary-3--text">{{data.item.title}}</div>
                  <div class="secondary--text">{{data.item.description}}</div>
                </div>
            </template>
        </v-select>
      </v-card-text>
      <v-divider />
      <v-card-actions class="d-flex align-center justify-end">
        <v-btn text @click="$emit('close')">Cancel</v-btn>
        <ButtonPrimary
            @click="select()"
            :loading="loading1"
            :label="'Save'"
        />
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
export default {
  props: ["dialog", "data", "loading", "rubricID"],
  data: () => ({
    loading1: false,
    selectedRubric: null
  }),

  methods: {
    ...mapActions("instructor", ["showRubricAction"]),
    ...mapMutations(["alertMutation"]),

    select() {
      this.loading1 = true
      this.showRubricAction(this.selectedRubric).then(() => {
        this.loading1 = false
        this.$emit("getRubricID", this.selectedRubric)
        this.$emit("close");
      }).catch(() => {
        this.loading1 = false
      });
    },
    
  },

  computed: {
    selected: {
      get(){
        return this.rubricID
      },
      set(val){
        this.selectedRubric = val
        return val
      }
    }
  },

  mounted() {
    this.selected = this.rubricID
  }
};
</script>
