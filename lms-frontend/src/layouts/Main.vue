<template>
  <v-app color="app-body" class="fade pa-0" v-if="!loading">
    <SessionTimeOut />
    <!-- Role base components -->
    <UserTopbar v-if="role == 'user'"
      :user="user"
      @startTour="startTour=true"
      @showDrawer="drawer=!drawer"
    />
    <TeacherTopbar
      v-if="role == 'instructor'"
      :course="course"
      :user="user"
      @add="addCourse = true"
      @showDrawer="drawer=!drawer"
      @enroll="addStudent = true"
    />
    <AdminTopbar v-if="role == 'admin'" :user="user" @add="addUser = true" @showDrawer="drawer=!drawer" @enroll="enrollStudent = true"/>
    <SuperTopbar v-if="role == 'super-admin'" :user="user" @add="addTenant = true" @showDrawer="drawer=!drawer"/>
    <!-- Navigation drawer -->
    <v-navigation-drawer
      :app="!overlap"
      :hide-overlay="!overlap"
      v-model="drawer"
      :color="mini ? 'mini_navigation_background_color' : 'navigation_background_color'"
      mini-variant-width="80"
      width="220"
      :mini-variant="mini"
    >
    <template v-slot:prepend>
        <v-sheet height="100" :color="customization.hasOwnProperty('navigation_banner_color') ? (customization.navigation_banner_color ? customization.navigation_banner_color : 'white') : 'white'" 
          class="d-flex align-center justify-center"
          :class="!mini && 'pa-5'">
          <v-img 
            v-if="!mini"
            :src="customization.hasOwnProperty('horizontal_logo') ? customization.horizontal_logo : require('../assets/default/horizontal_logo.png')"
            v-on:error="delete customization.horizontal_logo"
            :lazy-src="customization.hasOwnProperty('horizontal_logo') ? customization.horizontal_logo : require('../assets/default/horizontal_logo.png')"
            eager
            contain/>
            <v-container v-else>
              <v-img
                :src="customization.hasOwnProperty('vertical_logo') ? customization.vertical_logo : require('../assets/default/vertical_logo.png')"
                v-on:error="delete customization.vertical_logo"
                class="ma-1"
              />
            </v-container>
        </v-sheet>
      </template>
      <Leftbar
        :user="user"
        :drawer="drawer"
        :mini="mini"
        :role="role"
        :lists="
          role == 'user'
            ? $userRoutes
            : role == 'instructor'
            ? $teacherRoutes
            : role == 'super-admin'
            ? $superRoutes
            : $adminRoutes
        "
        @collapse="collapse"
      />
      <template v-slot:append>
        <v-divider/>
        <v-list dense class="drawer" transparent>
          <v-list-item class="px-5"  :to="{name: `${role.charAt(0).toUpperCase()+ role.slice(1)} Account Profile`}" v-if="!mini && role != 'super-admin'"
              :class="$route.meta.parent == `${role.charAt(0).toUpperCase()+ role.slice(1)} Account Profile` && 'navigation_link_color--text'"
              :style="$route.meta.parent == `${role.charAt(0).toUpperCase()+ role.slice(1)} Account Profile` ? { borderRight: `5px solid ${getlinkColor} !important`} : ''"
            >
            <v-list-item-title  class="roboto f14 d-flex align-center"
              :class="[mini && 'justify-center', $route.meta.parent == `${role.charAt(0).toUpperCase()+ role.slice(1)} Account Profile` ? 'navigation_link_color--text' : 'default_color_1--text']">
              <v-icon :size="!mini ? 18 : 20" :color="$route.meta.parent == `${role.charAt(0).toUpperCase()+ role.slice(1)} Account Profile` ? 'navigation_link_color' : 'secondary-2'">mdi-cog-outline</v-icon>
              <div v-if="!mini" class="ma-2 fw500">Account Settings</div>
            </v-list-item-title>
          </v-list-item>

          <v-list-item class="px-5"  :to="{name: `${role.charAt(0).toUpperCase()+ role.slice(1)} Account Profile`}" v-if="mini && role != 'super-admin'"
              :class="$route.meta.parent == `${role.charAt(0).toUpperCase()+ role.slice(1)} Account Profile` ?'mini_navigation_icon_color--text' : 'secondary-2--text'"
              :style="$route.meta.parent == `${role.charAt(0).toUpperCase()+ role.slice(1)} Account Profile` ? { borderRight: `5px solid ${getlinkColor}`} : ''"
            >
            <v-list-item-title class="roboto f14 d-flex align-center"
              :class="[mini && 'justify-center', $route.meta.parent == `${role.charAt(0).toUpperCase()+ role.slice(1)} Account Profile` ? 'mini_navigation_icon_color--text' : 'secondary-2--text']">
              <v-icon :size="!mini ? 18 : 20" :color="!mini ? '' : getColorByBgColorMini">mdi-cog-outline</v-icon>
              <div v-if="!mini" class="ma-2 fw500">Account Settings</div>
            </v-list-item-title>
          </v-list-item>

          <v-list-item class="px-5"  v-if="role =='user' && !mini" :to="{ name: 'User Help Desk' }"
              :class="$route.name == `User Help Desk` && 'navigation_link_color--text'"
              :style="$route.name == `User Help Desk` && { borderRight: `5px solid ${getlinkColor} !important` }"
            >
            <v-list-item-title class="roboto f14 d-flex align-center"
              :class="[mini && 'justify-center', $route.name == `User Help Desk` ? 'navigation_link_color--text' : 'default_color_1--text']">
              <v-icon :size="!mini ? 18 : 20" :color="$route.name == `User Help Desk` ? 'navigation_link_color' : 'secondary-2'">mdi-help-circle-outline</v-icon>
              <div v-if="!mini" class="ma-2 fw500">Help Desk</div>
            </v-list-item-title>
          </v-list-item>

          <v-list-item class="px-5"  v-if="role =='user' && mini" :to="{ name: 'User Help Desk' }"
              :class="$route.name == `User Help Desk` ? 'mini_navigation_link_color--text' : 'default_color_1--text'"
              :style="$route.name == `User Help Desk` && { borderRight: `5px solid ${getlinkColor}` }"
            >
            <v-list-item-title class="roboto f14 d-flex align-center"
              :class="$route.name == `User Help Desk` ? 'justify-center mini_navigation_link_color--text' : 'justify-center default_color_1--text'">
              <v-icon :size="!mini ? 18 : 20" :color="!mini ? '' : getColorByBgColorMini">mdi-help-circle-outline</v-icon>
            </v-list-item-title>
          </v-list-item>

          <v-list-item  class="px-5"  @click="logout" v-if="!mini"
              :class="'default_color_1--text'"
            >
            <v-list-item-title class="roboto f14 d-flex align-center"
              :class="'default_color_1--text'">
              <v-icon :size="!mini ? 18 : 20" :color="!mini ? 'secondary-2' : 'secondary-2'">mdi-logout</v-icon>
              <div v-if="!mini" class="ma-2 fw500">Logout</div>
            </v-list-item-title>
          </v-list-item>

          <v-list-item  class="px-5"  @click="logout" v-if="mini"
              :class="'default_color_1--text'"
            >
            <v-list-item-title class="roboto f14 d-flex align-center"
              :class="['justify-center default_color_1--text']">
              <v-icon :size="!mini ? 18 : 20" :color="!mini ? 'secondary-2--text' : getColorByBgColorMini">mdi-logout</v-icon>
              <div v-if="!mini" class="ma-2 fw500">Logout</div>
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </template>
    </v-navigation-drawer>
    <!-- <MyCoursesDrawer
      v-if="role === 'instructor' && $route.meta.parent === 'Instructor My Courses' && ($vuetify.breakpoint.md || $vuetify.breakpoint.lg || $vuetify.breakpoint.xl)"
      :role="role"
    /> -->

    <!-- <UserCourseDrawer v-if="role === 'user' && myCoursesDrawer" :role="role"/> -->

    <!-- <UserRightBarTodo
      :role="role"
      v-if="
      $route.name !== 'User Assessment'
      && $route.name !== 'User Learning Material'
      && $route.name !== 'User Account Settings'
      && $route.meta.parent !== 'User Account Settings'
      "
    /> -->

    <v-main class="app-body">
      <v-container fluid class="px-4">
        <v-row justify="center" dense>
          <v-col cols="12" xl="11">
            <router-view @createCourse="addCourse = true" @statusCourse="statusCourse" />
          </v-col>
        </v-row>
      </v-container>
    </v-main>
    <!-- Super Admin Components -->
    <SuperAddTenant
      :dialog="addTenant"
      @close="addTenant = false"
      @saveTenant="saveTenant"
    />

    <!-- Admin Components -->
    <AddUserModal v-if="role=='admin'" :dialog="addUser" @close="addUser = false" @saveUser="saveUser" />
    <EnrollUserModal 
      v-if="role=='admin'" 
      :dialog="enrollStudent" 
      @close="enrollStudent = false" 
      @success="() => {
        alertMutation({
          show: true,
          text: 'The import has been queued and will be processed shortly. You can check the import logs for status updates.',
          type: 'success'
        })
      }" />

    <!-- All user -->
    <TermsAndConditions :dialog="tandc" :content="content" :loading="tandcLoading" @accept="acceptTerms"/>
    <ChangePassDialog :dialog="change" @close="change = false" />
    <UpdateInfoPromptDialog :dialog="updatePromptDialog" :loading="updatePromptLoading" @proceed="updateNeedsUpdateFlag"/>

    <!-- Instructor Components -->
    <AddCourseModal :dialog="addCourse" @close="addCourse = false" />
    <EnrollStudentDialog :dialog="addStudent" @close="addStudent = false" />
    <CourseStatusModal :dialog="statusCourse" @close="changeCourseStatus = false" />
    <!--  -->

    <!-- <toaster v-if="toast" :show="toast" :text="toastText" @close="toast = false" /> -->
    <!-- <toaster
      v-if="alert.show"
      :show="alert.show"
      :text="alert.text"
      @close="alert.show = false"
    /> -->

    <!-- <vue-tour name="explanatoryTour" :steps="steps"></vue-tour> -->

    <StartTourDialog :dialog="startTour" @close="startTour = false"/>
  </v-app>

  <v-app  color="app-body" v-else >
    <v-sheet class="d-flex align-center justify-center" height="100vh">
      <circular />
    </v-sheet>
  </v-app>
</template>

<script>
import { role } from "../services/axios";

import { mapActions, mapState, mapMutations } from 'vuex'

import Leftbar from "./Leftbar.vue";

/*user components */
import UserTopbar from "./user/Topbar.vue";
import UserCourseDrawer from './user/CoursesDrawer.vue'
import UserRightBarTodo from './user/RightBarTodo.vue'
/*teacher components */
import TeacherTopbar from "./teacher/Topbar.vue";
import AddCourseModal from "./teacher/AddCourseModal.vue";
import MyCoursesDrawer from "./teacher/MyCoursesDrawer.vue";
import CourseStatusModal from "./teacher/CourseStatusModal.vue";
import EnrollStudentDialog from "../components/teacher/enrollment/EnrollStudentDialog.vue";
/*admin components */
import AdminTopbar from "./admin/Topbar.vue";
import AddUserModal from "../components/admin/user/AddModal.vue";
import EnrollUserModal from "../components/admin/user/EnrollModal.vue";
/*super components */
import SuperTopbar from "./super/Topbar.vue";
import SuperAddTenant from "./super/ModalTenant.vue";

import MobileDrawer from "./MobileDrawer.vue";

import ChangePassDialog from "./ChangePassDialog.vue";
import TermsAndConditions from "./TermsAndConditions.vue";

import StartTourDialog from "../components/user/StartTourDialog.vue"
import SessionTimeOut from "@/components/reusable/SessionTimeOut.vue";
import UpdateInfoPromptDialog from "./UpdateInfoPromptDialog.vue";

// import echo from '@/plugins/socket';

export default {
  components: {
    Leftbar,
    UserTopbar,
    TeacherTopbar,
    AdminTopbar,
    SuperTopbar,
    SuperAddTenant,
    AddUserModal,
    EnrollUserModal,
    MobileDrawer,
    ChangePassDialog,
    TermsAndConditions,
    AddCourseModal,
    MyCoursesDrawer,
    CourseStatusModal,
    EnrollStudentDialog,
    UserCourseDrawer,
    UserRightBarTodo,
    StartTourDialog,
    SessionTimeOut,
    UpdateInfoPromptDialog
  },
  data: () => ({
    echo: null,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    tenantHorizontalLogo: null,
    tenantVerticalLogo: null,
    drawer: null,
    right: null,
    overlay: false,
    overlap: false,
    btnNotif: true,
    role: "",
    lists: [],
    addTenant: false,
    loading: true,
    toast: false,
    toastText: "",
    change: false,
    //instructor
    addUser: false,
    addCourse: false,
    addStudent: false,
    tandc: false, 
    tandcLoading: false, 
    updatePromptDialog: false,
    updatePromptLoading: false,
    content: '',
    enrollStudent: false,
    changeCourseStatus: false,
    mini: null,
    startTour: false,
    steps: [
      {
        target: '#v-step-0',
        content: `Discover <strong>Vue Tour</strong>!`
      }
    ]
  }),
  mounted() {
    this.cleanupNotification()

    this.$vuetify.breakpoint.width > 1440 ? this.setState(true) : this.setState(false);

    this.role = role();

    this.authAction().then(() => {
      if(this.user.role === 'USER') {
        if(this.user.needs_info_update === 1) {
          this.updatePromptDialog = true
        }
        if (this.user.has_accepted_terms === 0 && this.user.terms_and_conditions) {
          this.content = this.user.terms_and_conditions.content
          this.tandc = true
        } else if (this.user.is_first_login) {
          this.change = true;
        }
      }

      this.loading = false;
    }).catch(e => {
      this.loading = false;
      this.logoutAction()
    });
  },
  computed: {
    ...mapState({
      user: (state) => state.user,
      alert: (state) => state.alert,
      tenant: (state) => state.tenant,
      userCustomDrawer: (state) => state.drawer,
      customization: (state) => state.customization,
      myCoursesDrawer: (state) => state.drawer
    }),

    ...mapState("instructor", {
      course: (state) => state.course,
      statusCourse: (state) => state.statusCourse,
    }),

    getlinkColor(){
      return this.customization.hasOwnProperty('navigation_link_color') ? this.customization.navigation_link_color ? this.customization.navigation_link_color : '#b52210' : '#b52210'
    },

    getColorByBgColorMini() {
			if(this.customization.mini_navigation_background_color === '#b52210') return 'secondary-2--text'
			return this.customization.mini_navigation_background_color ? ((parseInt(this.customization.mini_navigation_background_color.replace('#', ''), 16) > 0xffffff / 2) ? 'secondary-2' : 'grey') : 'grey';
		}
    
  },
  methods: {
    ...mapActions(["authAction", "tenantsDomainAction", "otherAllCoursesAction", "logoutAction", "changeUserAcceptTermsAction", "changeUserNeedsInfoUpdateFlagAction"]),
    ...mapActions("superad", ["getTenantsAction"]),
    ...mapMutations(['alertMutation', 'drawerMutation', 'cleanupNotification', 'notificationMutation']),

    logout() {
      this.$store.dispatch('logoutAction')
    },

    setState(state) {
      this.right = state;
      this.overlay = state;
    },

    saveTenant() {
      (this.toast = true), (this.alertMutation({
        show: true,
        text: 'You have successfully added a new tenant.',
        type: "success"
      }));
      this.getTenantsAction();
    },

    saveUser() {
      this.alertMutation({
        show: true,
        text: 'The import has been queued and will be processed shortly. You can check the import logs for status updates.',
        type: "success"
      });
    },

    collapse(e) {
      this.mini = e && !this.$vuetify.breakpoint.mobile;
    },

    acceptTerms() {
      this.tandcLoading = true
      this.changeUserAcceptTermsAction().finally(() => {
        this.tandcLoading = false
        this.content = ''
        this.tandc = false
        
        if(this.user.is_first_login) this.change = true
      })      
    },

    updateNeedsUpdateFlag(){
      this.updatePromptLoading = true
      this.changeUserNeedsInfoUpdateFlagAction().finally(() => {
        this.updatePromptLoading = false
        this.updatePromptDialog = false
        this.$router.push({ name: 'User Account Profile' })
      })     

    }

    // statusCourse(e) {
    //   console.log(e);
    //   this.changeCourseStatus = true;
    // },
  },
  watch: {
    "$vuetify.breakpoint.width"(val) {
      val > 1440 ? this.setState(true) : this.setState(false);
      this.$route.name.includes("Super Admin") ? this.setState(false) : "";
    },
    '$route.meta.collapse'(val) {
      if(this.$route.name.includes("User")) {
        return
      } else this.mini = false && !$vuetify.breakpoint.mobile
    },
    '$route' (to, from){
      if(to.name === 'User Course Details') {
        this.$store.commit('drawerMutation', false)
        this.mini = true
      }
    },

    userCustomDrawer(val) {
      if(this.role == 'user' && val == true ) {
        this.mini = true && !this.$vuetify.breakpoint.mobile
      } else if(this.$route.name === 'User Course Details'){
        this.mini = true
        this.$store.commit('drawerMutation', false)
      } else this.mini = false && !$vuetify.breakpoint.mobile
      // this.mini=val
    }
  },
};
</script>
