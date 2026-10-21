<template>
  <v-dialog persistent v-model="dialog" max-width="550">
    <v-card class="py-1">
      <v-card-title class="d-flex align-center justify-space-between ma-0 pa-0">
        <div class="poppins primary--text px-5 py-3 fw600 f14">Share Assessment : {{ data?.title }}</div>
        <v-btn icon @click="$emit('close')" class="mr-2">
          <v-icon size="20">mdi-close</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text class="">
        <v-text-field
          dense
          outlined
          class="general-custom-field secondary-1--text f14 fw600 roboto mb-2"
          prepend-inner-icon="mdi-magnify"
          placeholder="Search"
          v-model="search"
          hide-details
        />
        <div class="roboto primary--text f14" v-if="selected.length > 0">
          {{ selected.length }} selected
        </div>
        <!-- <div
          v-for="item in instructors"
          :key="item.id"
          class="d-flex justify-space-between align-center"
        >
          <div class="d-flex align-center">
            <v-checkbox dense hide-details @change="addUser(item.id)" />
            <div class="mt-2 roboto f14 black--text fw500">
              {{ `${item.first_name} ${item.last_name}` }}
            </div>
          </div>
          <div class="mt-2 roboto f14 black--text fw500">
            {{ `${item.email}` }}
          </div>
        </div> -->
        <v-sheet height="350" class="overflow-y-auto scroller">
          <v-list dense flat>
            <v-list-item-group v-model="selected" active-class="active" multiple>
              <v-list-item v-for="(item, i) in instructorList" :key="i" :value="item.id">
                <template v-slot:default="{ active }">
                  <v-list-item-content>
                    <v-list-item-title class="d-flex justify-space-between align-center">
                      <div class="d-flex align-center">
                        <v-icon small class="mr-1" :color="!active ? '' : 'primary'">{{
                          active ? "mdi-checkbox-marked" : "mdi-checkbox-blank-outline"
                        }}</v-icon>
                        <div class="roboto f14 mr-2" v-text="item.full_name" />
                        <div class="roboto f14 secondary-2--text">({{ item.email }})</div>
                      </div>
                    </v-list-item-title>
                  </v-list-item-content>
                </template>
              </v-list-item>
            </v-list-item-group>
          </v-list>
        </v-sheet>
      </v-card-text>
      <div class="error--text f11 mx-2 mb-2 text-center" v-if="error">Something went wrong</div>
      <v-card-actions
        class="d-flex align-center mx-2 justify-end"
      >
        <div class="d-flex align-center">
          <v-btn text class="poppins text-capitalize f13">Cancel</v-btn>
          <v-btn
            color="primary"
            class="poppins text-capitalize f13"
            :disabled="!selected.length > 0"
            @click="sendCopy"
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
    share: '',
    share_to_ids: [],
    selected: [],
    loading: false,
    search: '',
    error: false
  }),
  watch: {
    dialog(val) {
      if(!val) {
        this.selected = []
        this.share_to_ids = []
        this.search = ''
      }
    }
  },
  computed: {
    ...mapState("instructor", {
      instructors: (state) => { return state.instructors }
    }),
    instructorList(){
      if(this.search === '') {
        return this.instructors
      } else {
        return this.instructors.filter(user => String(`${user.full_name} ${user.email}`).toLowerCase().search(this.search.toLowerCase()) !== -1)
      }
    }
  },
  methods: {
    ...mapActions("instructor", ["shareAssessmentAction"]),
    ...mapMutations(["alertMutation"]),

    sendCopy() {
      if(this.selected.length > 0) {
        this.error = false
        this.loading = true;
        this.selected.forEach((element) => {
          this.shareAssessmentAction({
            assessment_id: this.data.id,
            shared_to_user_id: element,
          }).then(() => {
            this.loading = false;
            this.$emit("close");
            this.alertMutation({
              show: true,
              text: "You successfully shared an assessment",
              type: "success"
            });
          }).catch(() => {
            this.error = true
            this.loading = false;
          });
        });

      }
    },
  },
};
</script>

<style>
.active {
  background-color: transparent !important;
}
</style>