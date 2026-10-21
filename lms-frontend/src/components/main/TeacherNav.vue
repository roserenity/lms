<template>
  <div class="mt-10">
    <v-list dense class="drawer p-0" v-for="(list, i) in lists" :key="i">
      <v-subheader v-if="list.subheader.length > 0" class="caption fw500 text-uppercase px-5 secondary-4--text">{{ list.subheader }}</v-subheader>
      <section v-for="(item, i) in list.lists" :key="i">
        <v-list-group
          v-if="item.title == 'My Courses'"
          no-action
          :color="customization.hasOwnProperty('navigation_text_icon_color') ? customization.navigation_text_icon_color : 'primary'"
        >
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title class="roboto f14 d-flex align-center" >
                <v-icon class="px-2" size="18" :color="$route.meta.parent === item.route ? 'navigation_text_icon_color' : 'navigation_link_inactive_color'"> {{item.icon}} </v-icon>
                <div class="px-2" :class="$route.meta.parent === item.route ? 'navigation_text_icon_color--text' : 'navigation_link_inactive_color--text'"> {{ item.title }} </div>
              </v-list-item-title>
            </v-list-item-content>
          </template>

          <v-list-item 
            v-for="(i, key) in status"
            :key="key"
            class="roboto f12"  
            :to="{name: i.route1, query: { page: 1, paginate: 10, search: '' } }" 
            :style="(i.route1 === $route.name || $route.path.includes(i.route2))  && { background: getlinkBackgroundColor, color: getlinkColor, borderRight: '5px solid'}"
            :class="!(i.route1 === $route.name || $route.path.includes(i.route2)) && 'navigation_link_inactive_color--text'">
            <v-list-item-title
              :style="(i.route1 === $route.name || $route.path.includes(i.route2)) && { color: getlinktextColor}">{{ i.name }}</v-list-item-title>
          </v-list-item>
        </v-list-group>
        <v-list-item 
          v-else-if="item.title == 'Assessment' || item.title == 'Grading'"
          :to="{ name: item.route, query: item.query }"
          class="px-5 li" 
          :style="$route.meta.parent === `Instructor ${item.title}` ? { background: getlinkBackgroundColor, color: getlinkColor, borderRight: '5px solid'} : ''">
          <v-list-item-content>
            <v-list-item-title class="roboto f14 d-flex align-center " :class="$route.meta.parent === `Instructor ${item.title}` ? 'navigation_text_icon_color--text'  : $route.meta.parent === `Instructor ${item.title}` ? 'navigation_text_icon_color--text' : 'navigation_link_inactive_color--text'">
              <v-icon v-if="mini" class="px-1" size="18" :color="$route.meta.parent === `Instructor ${item.title}` ? 'navigation_text_icon_color' : $route.meta.parent === `Instructor ${item.title}` ? 'navigation_text_icon_color' : 'navigation_link_inactive_color'">{{item.icon}}</v-icon>
              <v-icon v-else class="px-1" size="18" :color="$route.meta.parent === `Instructor ${item.title}` ? 'navigation_text_icon_color' : $route.meta.parent === `Instructor ${item.title}` ? 'navigation_text_icon_color' : 'navigation_link_inactive_color'">{{item.icon}}</v-icon>
              <div v-if="!mini" class="mx-3 fw500">{{item.title}}</div>
              
            </v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item 
          v-else
          :to="{ name: item.route, query: item.query }"
          class="px-5" 
          :style="$route.meta.parent === item.route ? { background: getlinkBackgroundColor, color: getlinkColor, borderRight: '5px solid'} : ''">
          <v-list-item-content>
            <v-list-item-title class="roboto f14 d-flex align-center " :class="$route.name === item.route ? 'navigation_text_icon_color--text'  : $route.meta.parent === item.route ? 'navigation_text_icon_color--text' : 'navigation_link_inactive_color--text'">
              <v-icon v-if="mini" size="18" class="px-1" :color="$route.meta.parent == item.route ? 'navigation_text_icon_color' : $route.name === item.route ? 'navigation_text_icon_color' : 'navigation_link_inactive_color'">{{item.icon}}</v-icon>
              <v-icon v-else size="18" class="px-1" :color="$route.meta.parent == item.route ? 'navigation_text_icon_color' : $route.name === item.route ? 'navigation_text_icon_color' : 'navigation_link_inactive_color'">{{item.icon}}</v-icon>
              <div v-if="!mini" class="mx-3 fw500">{{item.title}}</div>
              
            </v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </section>
    </v-list>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  props: ['lists', 'mini'],
  data: () => ({
    status: [
      { name: 'Published', route1: 'Instructor Published Courses', route2: '/instructor/my-courses/publish'},
      { name: 'Unpublished', route1: 'Instructor Unpublished Courses', route2: '/instructor/my-courses/unpublish'},
      { name: 'Drafts', route1: 'Instructor Drafted Courses', route2: '/instructor/my-courses/drafts'},
    ]
  }),
  computed: {
    ...mapState({
      customization: (state) => state.customization
    }),

    getlinktextColor(){
      if(!this.customization.hasOwnProperty('navigation_text_icon_color')) return this.$vuetify.theme.currentTheme.navigation_text_icon_color
      return this.customization.hasOwnProperty('navigation_text_icon_color') ? this.customization.navigation_text_icon_color ? this.customization.navigation_text_icon_color : '#b52210' : '#b52210'
    },

    getlinkColor(){
      if(!this.customization.hasOwnProperty('navigation_link_color')) return this.$vuetify.theme.currentTheme.navigation_link_color
      return this.customization.hasOwnProperty('navigation_link_color') ? this.customization.navigation_link_color ? this.customization.navigation_link_color : '#b52210' : '#b52210'
    },

    getlinkBackgroundColor(){
      if(!this.customization.hasOwnProperty('navigation_link_color')) return this.$vuetify.theme.currentTheme.navigation_link_color.concat('33')
      return this.customization.hasOwnProperty('navigation_link_color') ? this.customization.navigation_link_color ? this.customization.navigation_link_color.concat('33') : '#b5221033' : '#b5221033'
    }

  }
}
</script>