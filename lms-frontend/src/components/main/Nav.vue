<template>
  <div class="">
    <v-list dense class="drawer">
      <section v-for="(item, i) in lists" :key="i">
        <div v-if="!mini" :class="i==0?'mt-3':'mt-7'" class="subtitle secondary-4--text ml-7 mb-1 text-uppercase">{{item.subheader}}</div>
          <v-list-item 
            v-for="(list, j) in item.lists" :key="j"
            :to="{ name: list.route, query: list.query }"
            class="px-5" 
            :style="$route.name == list.route ? { background: getlinkBackgroundColor, color: getlinkColor, borderRight: '5px solid'} 
            : $route.meta.parent == list.route ? { background: getlinkBackgroundColor, color: getlinkColor, borderRight: '5px solid'} : ''">
              <v-list-item-content v-if="j !== 6 || (j === 6 && user.ticket_count === 0)">
                <v-list-item-title class="roboto f14 d-flex align-center " :class="$route.name === list.route ? 'navigation_text_icon_color--text'  : $route.meta.parent === list.route ? 'navigation_text_icon_color--text' : 'navigation_link_inactive_color--text'">
                  <v-icon v-if="mini" class="px-1" size="20" :color="$route.meta.parent == list.route ? 'navigation_text_icon_color' : $route.name === list.route ? 'navigation_text_icon_color' : 'navigation_link_inactive_color'">{{list.icon}}</v-icon>
                  <v-icon v-else class="px-1" size="18" :color="$route.meta.parent == list.route ? 'navigation_text_icon_color' : $route.name === list.route ? 'navigation_text_icon_color' : 'navigation_link_inactive_color'">{{list.icon}}</v-icon>
                  <div v-if="!mini" class="mx-3 fw500">{{list.title}} </div>
                </v-list-item-title>
              </v-list-item-content>
              <v-list-item-content v-if="j === 6 && list.title === 'Terms'">
                  <v-list-item-title class="roboto f14 d-flex align-center"
                    :class="[mini && 'justify-center', $route.name === list.route ? 'navigation_text_icon_color--text' : $route.meta.parent === list.route ? 'navigation_text_icon_color--text' : 'navigation_link_inactive_color--text']">
                    <v-icon v-if="mini" class="px-1" size="20" :color="($route.name === list.route || $route.meta.parent === list.route) ? 'mini_navigation_icon_color' : getColorByBgColorMini">{{list.icon}}</v-icon>
                    <v-icon v-else class="px-1" size="18" :class="[$route.name === list.route ? 'navigation_text_icon_color--text' : $route.meta.parent === list.route ? 'navigation_text_icon_color--text' : 'navigation_link_inactive_color--text']">{{list.icon}}</v-icon>
                    <div v-if="!mini" class="mx-3 fw500 ">{{list.title}}</div>
                  </v-list-item-title>
              </v-list-item-content>
              <v-list-item-content v-if="j === 6 && user.ticket_count > 0 && list.title === 'Tickets'">
                <v-badge
                  v-if="list.title === 'Tickets'"
                  overlap
                  :inline="!mini"
                  bordered
                  :color="user.ticket_count === 0 ? 'transparent' : 'primary'"
                  :content="user.ticket_count"
                  :offset-x="!mini ? 25 : 15"
                  :style="text"
                  :dot="mini">
                  <v-list-item-title class="roboto f14 d-flex align-center"
                    :class="[mini && 'justify-center', $route.name === list.route ? 'navigation_text_icon_color--text' : $route.meta.parent === list.route ? 'navigation_text_icon_color--text' : 'navigation_link_inactive_color--text']">
                    <v-icon v-if="mini" class="px-1" size="20" :color="($route.name === list.route || $route.meta.parent === list.route) ? 'mini_navigation_icon_color' : getColorByBgColorMini">{{list.icon}}</v-icon>
                    <v-icon v-else class="px-1" size="18" :class="[$route.name === list.route ? 'navigation_text_icon_color--text' : $route.meta.parent === list.route ? 'navigation_text_icon_color--text' : 'navigation_link_inactive_color--text']">{{list.icon}}</v-icon>
                    <div v-if="!mini" class="mx-3 fw500 ">{{list.title}}</div>
                  </v-list-item-title>
                </v-badge>
                
                <v-list-item-title class="roboto f14 d-flex align-center" v-else
                  :class="[mini && 'justify-center', $route.name === list.route ? 'navigation_text_icon_color--text' : $route.meta.parent === list.route ? 'navigation_text_icon_color--text' : 'navigation_link_inactive_color--text']">
                  <v-icon v-if="mini" class="px-1" size="20" :color="($route.name === list.route || $route.meta.parent === list.route) ? 'mini_navigation_icon_color' : getColorByBgColorMini">{{list.icon}}</v-icon>
                  <v-icon v-else class="px-1" size="18" :class="[$route.name === list.route ? 'navigation_text_icon_color--text' : $route.meta.parent === list.route ? 'navigation_text_icon_color--text' : 'navigation_link_inactive_color--text']">{{list.icon}}</v-icon>
                  <div v-if="!mini" class="mx-3 fw500 ">{{list.title}}</div>
                </v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        <!-- </v-list-item-group> -->
      </section>
    </v-list>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  props: ['lists', 'mini'],
  computed: {
    ...mapState({
      customization: (state) => state.customization,
      user: (state) => state.user
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
    },

    getColorByBgColorMini() {
      if(this.customization.mini_navigation_background_color === '#b52210') return 'secondary-2--text'
			return this.customization.mini_navigation_background_color ? ((parseInt(this.customization.mini_navigation_background_color.replace('#', ''), 16) > 0xffffff / 2) ? 'secondary-2' : 'grey') : 'grey';
    }
  }
}
</script>