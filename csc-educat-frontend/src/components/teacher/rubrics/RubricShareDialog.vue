<template>
    <v-dialog persistent v-model="dialog" max-width="550">
      <v-card>
        <v-card-title class="d-flex align-center justify-space-between ma-0 pa-0">
          <div class="poppins primary--text px-5 py-3 fw600 f14">Share Rubric : {{ data?.title }}</div>
          <v-btn icon @click="$emit('close')" class="mr-2">
            <v-icon size="20">mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
          <v-text-field
            dense
            outlined
            class="general-custom-field secondary-1--text f14 fw600 roboto mb-2"
            prepend-inner-icon="mdi-magnify"
            placeholder="Search Instructor"
            hide-details
            v-model="search"
          />
          <div class="roboto primary--text f12" v-if="selected.length > 0">
            {{ selected.length }} selected
          </div>
          <v-sheet height="300" class="overflow-y-auto scroller">
            <v-list dense flat>
              <v-list-item-group v-model="selected" active-class="active" multiple>
                <v-list-item v-for="(item, i) in items" :key="i" :value="item.id" class="px-0">
                  <template v-slot:default="{ active }">
                    <v-list-item-content>
                      <v-list-item-title class="d-flex justify-space-between align-center">
                        <div class="d-flex flex-row align-center">
                          <v-icon :color="!active ? '' : 'primary'">{{
                            active ? "mdi-checkbox-marked" : "mdi-checkbox-blank-outline"
                          }}</v-icon>
                          <div class="text-wrap roboto f14 mx-2">
                            {{ item.full_name }}
                            
                            <span class="roboto f12 secondary-2--text">({{ item.email }})</span>
                          </div>
                        </div>
                      </v-list-item-title>
                    </v-list-item-content>
                  </template>
                </v-list-item>
              </v-list-item-group>
            </v-list>
          </v-sheet>
        </v-card-text>
        <v-card-actions
          class="d-flex flex-row justify-end align-center mx-2"
        >
          <div class="d-flex align-center">
            <v-btn text dense class="poppins text-capitalize f13" @click="$emit('close')">Cancel</v-btn>
            <v-btn
              dense 
              color="primary"
              class="poppins text-capitalize f13"
              @click="sendCopy"
              :disabled="!selected.length > 0"
              :loading="loading"
              >Send Copy</v-btn
            >
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </template>
  
  <script>
  import { mapActions, mapMutations, mapState } from "vuex";
  export default {
    props: ["dialog", "data"],
    data: () => ({
      share_to_ids: [],
      selected: [],
      loading: false,
      items: [],
      search: ''
    }),
    computed: {
      ...mapState("instructor", {
        instructors: (state) => state.instructors,
      }),
    },
    mounted(){
      this.items = [...this.instructors]
    },
    watch: {
      search(val){
        if (val == '') {
          this.items = [...this.instructors]
        } else {
          this.items = this.items.filter(user => String(`${user.full_name} ${user.email}`).toLowerCase().search(val.toLowerCase()) !== -1)
        }
      },

      dialog(){
        this.selected = []
      }
    },
    methods: {
      ...mapActions("instructor", ["shareRubricAction"]),
      ...mapMutations(["alertMutation"]),
  
      sendCopy() {
        this.loading = true;
        console.log(this.selected);
        this.selected.forEach((element) => {
          this.shareRubricAction({
            rubric_id: this.data.id,
            shared_to_user_id: element,
          }).then(() => {
            this.loading = false;
            this.$emit("close");
            this.alertMutation({
              show: true,
              text: "You successfully shared a rubric",
              type: "success"
            });
          });
        });
      },
    },
  };
  </script>
  
  <style>
  .active {
    background-color: transparent !important;
  }
  </style>
  