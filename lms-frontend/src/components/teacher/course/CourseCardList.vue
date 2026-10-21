<template>
  <v-card>
      <v-row no-gutters>
          <v-col cols="11" class="d-flex flex-row" @click="$router.push({ name: 'Instructor Manage Course', params: { id: item.id, status: item.status.toLowerCase() } })">
              <section>
                  <v-img
                      :src="item.image ? item.image.url : require('@/assets/images/default-course-bg.png')"
                      width="80"
                      height="100%"
                      class=""
                      v-on:error="item.image = null"
                  >
                  </v-img>
                  <!-- <v-sheet 
                      :color="!item.image ? 'primary' : 'transparent'" 
                      class="hover"
                      width="80"
                      v-else>
                  </v-sheet> -->
              </section>
              <section class="mx-3 my-1">
                  <div class="poppins fw600 primary--text f10">
                    {{ item.status }}ED &nbsp; | &nbsp; {{  item.type_of_learning }}
                  </div>
                  <span class="poppins fw600 black--text text-left break-word">
                      {{ item.title ? item.title : 'Untitled' }}
                  </span>
                  <div class="poppins fw600 text-left f10" v-if="item.course_code">
                    {{ item.course_code }}
                  </div>
                  <!-- <span class="poppins fw600 black--text text-left d-inline-block text-truncate" style="max-width: 35vw">
                      {{ item.title ? item.title : 'Untitled' }}
                  </span> -->
                  <div class="poppins fw400 secondary-3-text text-left f10">
                      {{ $dateFormat.fromNow(item.created_at) }}
                  </div>
              </section>
          </v-col>
          <v-col cols="1">
              <v-menu offset-y v-if="$route.name != 'Instructor Trash'">
                  <template v-slot:activator="{ on, attrs }">
                      <v-btn
                          icon
                          v-bind="attrs"
                          v-on="on"
                          dense
                          class="float-right"
                          v-if="$isReadOnly.showOnlyToOwner(item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner')"
                      >
                      <v-icon >
                          mdi-dots-vertical
                      </v-icon>
                      </v-btn>
                  </template>
                  <v-list>
                      <v-list-item @click="$router.push({ name: 'Instructor Manage Course', params: { id: item.id, status: item.status.toLowerCase() } })">
                        <v-list-item-title class="roboto f14 d-flex align-center">
                            <v-icon size="18" class="mr-2">
                              {{
                                item.status === "PUBLISH"
                                ? "mdi-eye-outline"
                                : ($isReadOnly.isReadOnly(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner') ? 'mdi-eye-outline' : 'mdi-pencil')
                              }}
                            </v-icon>
                            <div class="">
                              {{
                                item.status === "PUBLISH"
                                ? "View"
                                : ($isReadOnly.isReadOnly(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner') ? 'View' : 'Edit')
                              }}
                            </div>
                        </v-list-item-title>
                      </v-list-item>
                      <!-- <v-list-item @click="$router.push({ name: 'Instructor Manage Course', params: { id: item.id, status: item.status.toLowerCase() } })">
                          {{
                              item.status === "PUBLISH"
                              ? "View"
                              : ($isReadOnly.isReadOnly(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner') ? 'View' : 'Edit')
                          }}
                      </v-list-item> -->
                      <v-list-item @click="publish(item)" v-if="$isReadOnly.showCardAction(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner')">
                        <v-list-item-title class="roboto f14 d-flex align-center">
                            <v-icon size="18" class="mr-2">
                              {{
                                  item.status === "PUBLISH"
                                  ? "mdi-cancel"
                                  : item.status === "DRAFT"
                                  ? "mdi-publish"
                                  : "mdi-publish"
                              }}
                            </v-icon>
                            <div class="">
                              {{
                                  item.status === "PUBLISH"
                                  ? "Unpublish"
                                  : item.status === "DRAFT"
                                  ? "Publish"
                                  : "Publish"
                              }}
                            </div>
                        </v-list-item-title>
                      </v-list-item>
                      <v-list-item @click="moveToTrashDialog = true" v-if="$isReadOnly.showCardAction(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner')">
                        <v-list-item-title class="roboto f14 d-flex align-center">
                            <v-icon size="18" class="mr-2">mdi-trash-can-outline</v-icon>
                            <div class="">Move to Trash</div>
                        </v-list-item-title>
                      </v-list-item>
                  </v-list>
              </v-menu>
              <v-menu offset-y v-if="$route.name == 'Instructor Trash'">
                  <template v-slot:activator="{ on, attrs }">
                      <v-btn
                          icon
                          v-bind="attrs"
                          v-on="on"
                          dense
                          class="float-right"
                          v-if="$isReadOnly.showOnlyToOwner(item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner')"
                      >
                      <v-icon >
                          mdi-dots-vertical
                      </v-icon>
                      </v-btn>
                  </template>
                  <v-list dense class="poppins f14 fw500">
                      <v-list-item @click="restore(item.id)">
                        <v-icon class="mr-3" small>mdi-restore</v-icon>Restore
                      </v-list-item>
                      <v-list-item @click="deleteDialog =  true">
                        <v-icon class="mr-3" small>mdi-trash-can-outline</v-icon>Delete
                      </v-list-item>
                  </v-list>
              </v-menu>
          </v-col>
      </v-row>
      <DeleteDialog
        :dialog="deleteDialog"
        :item="item"
        :course_id="item.id"
        type="Course"
        @close="deleteDialog = false"
        @fetchCourses="$emit('fetchCourses')"
      />

      <MoveToTrashDialog
        :dialog="moveToTrashDialog"
        :item="item"
        :course_id="item.id"
        type="Course"
        @close="moveToTrashDialog = false"
        @fetchCourses="$emit('fetchCourses')"
      />
  </v-card>
</template>

<script>

import DeleteDialog from "./DeleteDialog.vue"
import MoveToTrashDialog from "./MoveToTrashDialog.vue";
import { mapMutations, mapActions } from "vuex";
export default {
props: ["i", "item", "gallery"],
data : () => ({
  moveToTrashDialog: false,
  deleteDialog: false,
  asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
}),
components: {
  MoveToTrashDialog,
  DeleteDialog
},
methods: {
  ...mapMutations(["alertMutation"]),
  ...mapMutations("instructor", ["statusCourseMutation"]),
  ...mapActions("instructor", ["restoreCourseAction", "addToTrashAction", "getCoursesAction", "getTrashAction", "getInstructorAssessmentsAction"]),

  error(){
    this.alertMutation({
      show: true,
      text: 'Something went wrong.',
      type: "error"
    })
  },

  publish(data) {
    if (data.status == "PUBLISH") {
      this.statusCourseMutation({
        id: data.id,
        status: true,
        action: 'unpublish'
      });
    } else {
      this.statusCourseMutation({
        id: data.id,
        status: true,
        action: 'publish'
      });
    }
  },

  restore(course_id) {
    this.restoreCourseAction({ course_id: course_id }).then(()=>{
      this.alertMutation({
        show: true,
        text: 'Course restored',
        type: "success"
      })
      this.$emit('fetchCourses')
    }).catch(() => {
      this.error()
    })
  },
},

computed: {
  getPrimary() {
    return this.$vuetify.theme.themes.light.primary
  }
}
};
</script>