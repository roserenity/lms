<template>
  <v-card class="custom-border d-flex flex-column justify-space-between" :width="gallery ? 250 : '100%'">
    <section>
      <v-sheet 
        :color="!item.image ? 'primary' : 'transparent'" 
        :style="gallery && 'height: 132px'" 
        class="hover border-top"
        v-if="gallery">
        <v-menu offset-y v-if="!item.image && $route.name != 'Instructor Trash'">
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              icon
              class="float-right"
              v-bind="attrs"
              v-on="on"
              :class="!gallery && 'ml-auto'"
              v-if="$isReadOnly.showOnlyToOwner(item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner')"
            >
              <v-icon >
                mdi-dots-vertical
              </v-icon>
            </v-btn>
          </template>
            <v-list>
              <v-list-item @click="moveToTrashDialog = true">
                Move to Trash
              </v-list-item>
            </v-list>
        </v-menu>
        <v-menu offset-y v-if="!item.image && $route.name == 'Instructor Trash' && !gallery">
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              icon
              class="float-right"
              v-bind="attrs"
              v-on="on"
              :class="!gallery && 'ml-auto'"
            >
              <v-icon >
                mdi-dots-vertical
              </v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="restore(item.id)">
              Restore
            </v-list-item>
          </v-list>
        </v-menu>
        <img
          v-if="item.image && gallery"
          :src="item.image ? item.image.url : require('@/assets/images/default-course-bg.png')"
          :style="$vuetify.breakpoint.mobile ? gallery ? 'height: 132px; width: 250px' : 'height: 40px; width: 250px':gallery ? 'height: 132px; width: 250px' : 'height: 40px; width: 250px'"
          class="border-top"
          v-on:error="require('@/assets/images/default-course-bg.png')"
        >
          <v-menu offset-y v-if="item.image && $route.name != 'Instructor Trash'">
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                class="float-right"
                v-bind="attrs"
                v-on="on"
                :class="!gallery && 'ml-auto'"
                v-if="$isReadOnly.showOnlyToOwner(item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner')"
              >
                <v-icon >
                  mdi-dots-vertical
                </v-icon>
              </v-btn>
            </template>
            <v-list class="poppins f14">
              <v-list-item  v-if="!gallery" @click="publish(item)">
                {{
                  item.status === "PUBLISH"
                    ? "UNPUBLISH"
                    : item.status === "DRAFT"
                    ? "PUBLISH"
                    : "PUBLISH"
                }}
              </v-list-item>
              <v-list-item @click="moveToTrashDialog = true">
                <v-list-item-title class="roboto f14 d-flex align-center">
                    <v-icon size="18" class="mr-2">mdi-trash-can-outline</v-icon>
                    <div class="">Move to Trash</div>
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </img>

        
      </v-sheet>
      <div light dense class="d-flex flex-column ma-3 align-start">
        <div class="poppins fw600 primary--text text-left f10">
            {{ item.status }}ED &nbsp; | &nbsp; {{  item.type_of_learning }}
        </div>
        <span v-if="gallery" class="poppins fw600 black--text text-left d-inline-block text-wrap" style="max-width: 90%">
          {{ item.title ? item.title : 'Untitled' }}
        </span>
        <span v-else-if="!gallery" class="poppins fw600 black--text text-left text-wrap">
          {{ item.title ? item.title : 'Untitled' }}
        </span>
        
        <div class="poppins fw600 text-left f10" v-if="item.course_code">
          {{ item.course_code }} 
        </div>

        <!-- <span v-if="item.description && item.description.length > 0 && !gallery" v-html="item.description ? $dompurifier(item.description) : ''" class="fw400 text-wrap f12" >
        </span> -->
        <div class="poppins fw400 secondary-3-text text-left f10">
          {{ $dateFormat.fromNow(item.created_at) }}
        </div>
      </div>
    </section>
    
    <!-- <v-alert
      elevation="2"
      v-if="!gallery"
    >
      <v-row class="pa-3">
        <v-col v-if="!item.image" cols="2" width="auto" class="border" :style="`background-color: ${getPrimary};`" >
          
        </v-col>

        <v-col v-else cols="2" class="border" :style="`background-image: url(${item.image.url ? item.image.url : this.defaultImage}); background-size: cover`" >
          
        </v-col >
        <v-col cols="7" class="d-flex flex-column">
          <div class="poppins fw600 secondary-2-text text-left f10">
            {{ item.status }}ED
          </div>
          <span v-if="gallery" class="poppins fw600 black--text text-left d-inline-block text-truncate" style="max-width: 90%">
            {{ item.title }}
          </span>
          <span v-else-if="!gallery" class="poppins fw600 black--text text-left text-wrap">
            {{ item.title }}
          </span>
          <span v-if="item.description && item.description.length > 0 && !gallery" class="fw400 text-wrap f12" v-html="item.description.length > 200 ? `${item.description.slice(0, 200)}...` : item.description"></span>
          <div class="poppins fw400 secondary-3-text text-left f10">
            {{ $dateFormat.fromNow(item.created_at) }}
          </div>
        </v-col>
        <v-spacer />
        <v-col cols="2" class="d-flex flex-row align-start text-right ml-auto" :style="$route.name === 'Instructor Trash' ? (!item.image ? 'padding-left: 100px;' : 'justify-content: end') : (!item.image ? 'padding-left: 50px;' : 'justify-content: end')">
          <section class="d-flex flex-column" v-if="!gallery && $route.name != 'Instructor Trash'">
            <v-btn block dense class="poppins f10 mb-3 text-capitalize" color="primary" @click="$router.push({ name: 'Instructor Manage Course', params: { id: item.id, status: item.status.toLowerCase() } })">
              {{
                item.status === "PUBLISH"
                  ? "&nbsp; &nbsp; View &nbsp; &nbsp;"
                  : ($isReadOnly.isReadOnly(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner') ? '&nbsp; &nbsp; View &nbsp; &nbsp;' : 'Edit')
              }}
            </v-btn>
            <v-btn block dense class="poppins f10 mb-3 text-capitalize" color="primary" @click="publish(item)" v-if="$isReadOnly.showCardAction(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner')">
              {{
                item.status === "PUBLISH"
                  ? "UNPUBLISH"
                  : item.status === "DRAFT"
                  ? "&nbsp; PUBLISH &nbsp;"
                  : "&nbsp; PUBLISH &nbsp;"
              }}
            </v-btn>
          </section>
          <section class="d-flex flex-column" v-if="!gallery && $route.name == 'Instructor Trash'">
            <v-btn block dense class="poppins f10 mb-3 text-capitalize" color="primary" @click="restore(item.id)">
                Restore
            </v-btn>
            <v-btn block dense class="poppins f10 mb-3 text-capitalize" color="primary" @click="deleteDialog =  true">
              Delete
            </v-btn>
          </section>
          <section :style="!$isReadOnly.showOnlyToOwner(item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner') && 'margin-right: 40px;'">
            <v-menu offset-y v-if="!gallery && $route.name != 'Instructor Trash'">
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  v-bind="attrs"
                  v-on="on"
                  v-if="$isReadOnly.showOnlyToOwner(item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner')"
                >
                  <v-icon >
                    mdi-dots-vertical
                  </v-icon>
                </v-btn>
              </template>
                <v-list>
                  <v-list-item @click="moveToTrashDialog = true">
                    Move to Trash
                  </v-list-item>
                </v-list>
            </v-menu>
          </section>
        </v-col>
      </v-row>
    </v-alert> -->
  
    <section v-if="gallery" class="rounded-b">
      <v-row no-gutters v-if="gallery">
        <v-col :cols="$isReadOnly.showCardAction(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner') ? 6: 12" class="d-flex justify-center">
          <v-btn
            block
            color="secondary-5"
            class="poppins f10 fw500"
            dense
            @click="
              $router.push({ name: 'Instructor Manage Course', params: { id: item.id, status: item.status.toLowerCase() } })
            "
            tile
            :class="$isReadOnly.showCardAction(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner') ? 'rounded-bl' : 'rounded-b'"
            v-if="$route.name != 'Instructor Trash'"
          >
          {{
            item.status === "PUBLISH"
              ? "View"
              : ($isReadOnly.isReadOnly(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner') ? 'View' : 'Edit')
          }}
          </v-btn>
          <v-btn
            v-else
            block
            dense
            color="secondary-5"
            class="poppins f10 fw500 rounded-bl"
            tile
            @click="deleteDialog =  true"
          >
            Delete
          </v-btn>
        </v-col>
        <v-col cols="6" class="d-flex justify-center" v-if="$isReadOnly.showCardAction(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner')">
          <v-btn
            dense
            block
            tile
            color="primary"
            class="fw500 poppins f10 "
            :class="$isReadOnly.showCardAction(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner') ? 'rounded-br' : 'rounded-b'"
            @click="publish(item)"
            v-if="$route.name != 'Instructor Trash'"
          >
            {{
              item.status === "PUBLISH"
                ? "Unpublish"
                : item.status === "DRAFT"
                ? "&nbsp; Publish &nbsp;"
                : "&nbsp; Publish &nbsp;"
            }}
          </v-btn>
          <v-btn
            dense
            block
            tile
            color="primary poppins f10 fw500 rounded-br"
            class="fw500 poppins f10"
            v-if="$route.name == 'Instructor Trash'"
            @click="restore(item.id)"
          >
            Restore
          </v-btn>
        </v-col>
      </v-row>
    </section>
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
    defaultImage: `${process.env.VUE_APP_ASSET_PATH}/default-course-bg.png`
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
        }).catch(() => {
          this.error()
        })
      } else {
        this.statusCourseMutation({
          id: data.id,
          status: true,
          action: 'publish'
        }).catch(() => {
          this.error()
        })
      }
    },

    restore(course_id) {
      this.restoreCourseAction({ course_id: course_id }).then(()=>{
        this.$emit('fetchCourses')
        this.alertMutation({
          show: true,
          text: 'Course restored',
          type: "success"
        })
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
