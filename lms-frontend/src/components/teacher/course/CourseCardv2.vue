<template>
    <v-sheet elevation="1" class="course-card-border border-top border-bottom align-content-stretch" :class="[!gallery && 'my-5', !this.$vuetify.breakpoint.smAndDown && 'mx-2']">
      <v-sheet 
        :color="!item.image ? 'primary' : 'transparent'" 
        :style="gallery && 'height: 150px'" 
        class="hover border-top">
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
              <!-- <v-list-item class="roboto f14"  v-if="!gallery"  
                @click="
                  $router.push({ name: 'Instructor Manage Course', params: { id: item.id, status: item.status.toLowerCase() } })
                ">
                  Edit
              </v-list-item>
              <v-list-item v-if="!gallery" @click="publish(item)">
                {{
                  item.status === "PUBLISH"
                    ? "Unpublish"
                    : item.status === "DRAFT"
                    ? "Publish"
                    : "Publish"
                }}
              </v-list-item> -->
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
        <v-img
          v-if="item.image && gallery"
          :src="item.image.url"
          :style="$vuetify.breakpoint.mobile ? gallery ? 'height: 150px; width: 100%' : 'height: 40px; width: 100%':gallery ? 'height: 150px; width: 100%' : 'height: 40px; width: 100%'"
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
              <!-- <v-list-item class=""  v-if="!gallery"  
                @click="
                  $router.push({ name: 'Instructor Manage Course', params: { id: item.id, status: item.status.toLowerCase() } })
                ">
                  Edit
              </v-list-item> -->
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
                Move to Trash
              </v-list-item>
            </v-list>
          </v-menu>
        </v-img>
      </v-sheet>
      <v-alert
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
            <span v-if="item.description && item.description.length > 0 && !gallery" class="fw400 text-wrap f12" v-html="item.description.length > 200 ? `${item.description.slice(0, 200)}...` : $dompurifier(item.description)"></span>
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
              <!-- <v-menu offset-y v-if="!gallery && $route.name == 'Instructor Trash'">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    icon
                    v-bind="attrs"
                    v-on="on"
                  >
                    <v-icon >
                      mdi-dots-vertical
                    </v-icon>
                  </v-btn>
                </template>
                  <v-list class="poppins f14">
                    <v-list-item @click="restore(item.id)">
                      Restore
                    </v-list-item>
                  </v-list>
              </v-menu> -->
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
      </v-alert>
  
      <v-sheet v-if="gallery" class="ma-3 pb-1">
        <div light dense class="d-flex flex-column" :class="gallery ? 'align-start mb-1' : 'justify-center ml-3 my-3'">
          <div class="poppins fw600 secondary-2-text text-left f10">
              {{ item.status }}ED
          </div>
          <span v-if="gallery" class="poppins fw600 black--text text-left d-inline-block text-truncate" style="max-width: 90%">
            {{ item.title }}
          </span>
          <span v-else-if="!gallery" class="poppins fw600 black--text text-left text-wrap">
            {{ item.title }}
          </span>
          <span v-if="item.description && item.description.length > 0 && !gallery" v-html="item.description ? $dompurifier(item.description) : ''" class="fw400 text-wrap f12" >
          </span>
          <div class="poppins fw400 secondary-3-text text-left f10">
            {{ $dateFormat.fromNow(item.created_at) }}
          </div>
        </div>
        <v-sheet v-if="gallery">
            <v-row no-gutters>
              <!-- display if owner or editor -->
              <v-col :cols="$isReadOnly.showCardAction(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner') ? 6: 12" class="d-flex justify-center">
                <v-btn
                  :block="!$isReadOnly.showCardAction(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner')"
                  text
                  color="primary"
                  class="poppins f10 fw500 mb-2"
                  dense
                  @click="
                    $router.push({ name: 'Instructor Manage Course', params: { id: item.id, status: item.status.toLowerCase() } })
                  "
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
                dense
                text
                color="primary"
                class="poppins f10 fw500 mb-2"
                @click="deleteDialog =  true"
              >
                Delete
              </v-btn>
            </v-col>
            <v-col cols="6" class="d-flex justify-center" v-if="$isReadOnly.showCardAction(item.status, item.my_permission.length > 0 ? item.my_permission[0].pivot.permission : 'owner')">
              <v-btn
                dense
                color="primary"
                class="fw500 poppins f10 mb-2"
                @click="publish(item)"
                v-if="$route.name != 'Instructor Trash'"
              >
                {{
                  item.status === "PUBLISH"
                    ? "UNPUBLISH"
                    : item.status === "DRAFT"
                    ? "&nbsp; PUBLISH &nbsp;"
                    : "&nbsp; PUBLISH &nbsp;"
                }}
              </v-btn>
              <v-btn
                dense
                color="primary"
                class="fw500 poppins f10 mb-2"
                v-if="$route.name == 'Instructor Trash'"
                @click="restore(item.id)"
              >
                Restore
              </v-btn>
            </v-col>
          </v-row>
        </v-sheet>
      </v-sheet>
      <DeleteDialog
        :dialog="deleteDialog"
        :item="item"
        :course_id="item.id"
        type="Course"
        @close="deleteDialog = false"
      />
  
      <MoveToTrashDialog
        :dialog="moveToTrashDialog"
        :item="item"
        :course_id="item.id"
        type="Course"
        @close="moveToTrashDialog = false"
        @moveToTrash="moveToTrash"
      />
    </v-sheet>
  </template>
  
  <script>
  import DeleteDialog from "./DeleteDialog.vue"
  import MoveToTrashDialog from "../../../components/teacher/course/MoveToTrashDialog.vue";
  import { mapMutations, mapActions } from "vuex";
  export default {
    props: ["i", "item", "courses", "gallery"],
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
      ...mapMutations("instructor", ["statusCourseMutation", "alertMutation"]),
      ...mapActions("instructor", ["restoreCourseAction", "addToTrashAction", "getCoursesAction"]),
  
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
          this.$emit('showRestoreSuccess')
        })
      },
  
      moveToTrash(course_id){
        this.moveToTrashAction({ course_id: course_id }).then(()=>{
          this.getCoursesAction();
        })
      }
    },
  
    computed: {
      getPrimary() {
        return this.$vuetify.theme.themes.light.primary
      }
    }
  };
  </script>
  