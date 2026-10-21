<template>
  <v-navigation-drawer class="drawer hidden-md-and-down"
    app
    v-model="drawer"
    disable-route-watcher 
    style="margin-left: 80px" 
    stateless
    disable-resize-watcher
    permanent
    fixed
    :width="setWidth"
  >
    <h3 class="poppins fw600 mx-7 mb-5" style="margin-top: 120px"> 
      My Courses
    </h3>
    <v-list v-if="$store.getters['usr/coursesGetter'].length>0">
      <v-list-item dense class="px-7" 
        v-for="item in $store.getters['usr/coursesGetter']" 
        :key="item.id"
        exact
        exact-path
        @click="() => {
          $store.commit('drawerMutation', false)
          $router.push({name: 'User Course Details', params: {id: item.uuid}})
        }"
        :style="($route.path === `/course/${item.uuid}` || $route.path === `/course/${item.uuid}/content`) && { background: getlinkBackgroundColor, color: getlinkColor, borderRight: `5px solid ${getlinkColor}`}"
      >
        <v-list-item-content>
          <v-list-item-title class="roboto fw500 f14 d-flex align-center truncate" 
          :class="($route.path === `/course/${item.uuid}` || $route.path === `/course/${item.uuid}/content`) ? 'navigation_link_color--text' : 'default_color_1--text'">
            {{item.title}}
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>
    <div v-else class="px-7 roboto fw500 f14 d-flex align-center">No courses found.</div>
  </v-navigation-drawer>
</template>

<script>
import { mapState } from 'vuex'

export default {
  data: () => ({
    drawer: true,
  }),
  // computed: {  },
  mounted() {
    this.$store.dispatch('usr/getCoursesAction')
  },
  computed: {
    ...mapState({
      customization: (state) => state.customization
    }),

    setWidth(){
      switch (this.$vuetify.breakpoint.name) {
          case 'xs': 
          case 'sm': 
          case 'md': return 0
          case 'lg': 
          case 'xl': return 200
        }
    },
    
    getlinkColor(){
			return this.customization.hasOwnProperty('mini_navigation_link_color') ? this.customization.mini_navigation_link_color ? this.customization.mini_navigation_link_color : '#b52210' : '#b52210'
		},

		getlinkBackgroundColor(){
			return this.customization.hasOwnProperty('mini_navigation_link_color') ? this.customization.mini_navigation_link_color ? this.customization.mini_navigation_link_color.concat('33') : '#b5221033' : '#b5221033'
		},
  }
}
</script>