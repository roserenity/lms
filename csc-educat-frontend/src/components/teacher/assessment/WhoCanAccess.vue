<template>
  <section class="mx-3 col-xl-6 col-lg-6">
    <div class="">
      <div class="poppins fw500 f13 secondary-2--text mb-4">WHO CAN ACCESS</div>
      <div class="d-flex align-center">
        <v-select
          outlined
          dense
          class="general-custom-field roboto f14 secondary-1--text fw500"
          :items="course.class_categories"
          item-value="id"
          v-model="selectedClasses"
          multiple
          hide-details
          v-on:input="editAccess($event)"
          :disabled="$isReadOnly.isReadOnlyNotCourseOwner(course.status, permission)"
        >
          <template slot="item" slot-scope="item">
            <v-list-item-content>
              <v-list-item-title class="poppins f14 fw500">
                <span class="secondary--text">{{ item.item.class_code }}-</span>
                <span class="secondary-3--text">{{ item.item.class_name }}</span>
              </v-list-item-title>
            </v-list-item-content>
          </template>
          <template slot="selection" slot-scope="item">
            <div class="poppins f14 fw500 my-1">
              <span class="secondary--text">{{ item.item.class_code }}-</span>
              <span class="secondary-3--text">{{ item.item.class_name }},&nbsp; </span>
            </div>
          </template>
        </v-select>
        <div class="">
          <!-- <v-btn text color="primary" class="text-capitalize roboto" @click="show = true">
            <v-icon left>mdi-account-edit-outline</v-icon>
            Edit Access
          </v-btn> -->
        </div>
      </div>
    </div>
    <v-dialog v-model="show" max-width="550">
      <v-card>
        <v-card-title class="d-flex align-center justify-space-between">
          <h5 class="primary--text poppins fw600">Edit Access</h5>
          <v-btn icon @click="show = false">
            <v-icon size="22">mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-row no-gutters class="mx-5 mb-3">
          <v-col cols="8">
            <v-select
              outlined
              dense
              hide-details
              class="general-custom-field roboto f14 secondary-1--text fw500"
            ></v-select>
          </v-col>
          <v-col cols="4" class="d-flex justify-end align-center">
            <div class="f14 primary--text poppins fw500 mr-2">Import list</div>
          </v-col>
        </v-row>
        <v-divider class="pb-3" />
        <v-sheet class="px-5 pb-3">
          <v-row no-gutters>
            <v-col cols="8">
              <v-text-field
                outlined
                dense
                hide-details
                class="general-custom-field roboto f14 secondary-1--text fw500"
              ></v-text-field>
            </v-col>
            <v-col cols="4" class="d-flex justify-end align-center">
              <div class="primary--text poppins fw500 mr-2"></div>
            </v-col>
          </v-row>
        </v-sheet>
        <v-divider />
        <v-card-actions class="d-flex justify-end align-center">
          <v-btn text class="poppins text-capitalize f13">Cancel</v-btn>
          <v-btn color="primary" class="poppins text-capitalize f13">Save Changes</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </section>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";

export default {
  props: ['item', 'status', 'permission'],
  data: () => ({
    show: false,
  }),
  computed: {
    ...mapState("instructor", {
      course: (state) => state.course,
      classes: (state) => state.classes,
      assessment: (state) => state.assessment,
      assessment_classes: (state) => state. assessment_classes,
      assessment_users: (state) => state.assessment_users,
      selectedClasses: (state) => {
        let _category_ids = []
        state.assessment_users.forEach(user => {
          _category_ids.push(user.class_category_id)
        })
        return Array.from(new Set(_category_ids))
      }
    }),

  },
  methods: {
    ...mapActions('instructor', ['showTaggedUsersAction', 'tagClassAssessmentAction', 'getTagClassAssessmentAction']),
    ...mapMutations('instructor', ['classesMutation']),

    editAccess(e) {
      let _classes = []
      let _user_id = []
      e.forEach(element => {
        _classes.push(this.assessment_classes.filter(_class => _class.id === element)[0])
      });

      _classes.forEach(_class => {
        _class.enrollments.forEach(element => {
          _user_id.push(element.user_id)
        });
      })

      this.tagClassAssessmentAction({ 
        id: this.item.id, 
        course_id: this.item.course_id, 
        user_id: _user_id
      }).then(()=>{
        this.getTagClassAssessmentAction({
          id: this.item.id
        })
        // this.showTaggedUsersAction({
        //   course_id: this.item.course_id,
        //   id: this.item.id,
        //   state_classes: this.assessment_classes
        // })
      })
    }
  },
};
</script>
