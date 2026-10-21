<template>
    <section>
      <v-sheet max-height="85vh" min-height="85vh" class="custom-border d-flex">
        <v-navigation-drawer permanent stateless height="85vh" width="220">
          <v-list dense class="drawer poppins">
            <v-list-item v-for="(item, i) in items" :key="i" :to="item.to"
              :class="$route.name == item.to.name ? 'navigation_link_color--text' : ''"
              :style="$route.name == item.to.name ? { borderRight: `5px solid ${getlinkColor} !important`} : ''"
            >
              <v-list-item-title class="f12 fw500">
                {{item.name}}
              </v-list-item-title>
            </v-list-item>
          </v-list>
          <!-- <template  v-slot:append>
            <v-list dense>
              <v-list-item v-for="(item, i) in items2" :key="i">
                <v-list-item-title class="roboto f15 fw500">
                  {{item.name}}
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </template> -->
        </v-navigation-drawer>
        <div class="flex-grow-1">
          <router-view/>
        </div>
      </v-sheet>
    </section>
  </template>
  
  <script>
  import { mapState } from 'vuex'
  import { links1, links2 } from '../../constants/settinglinks/admin'
  
  export default {  
    data: () => ({
      items: links1,
      items2: links2
    }),
  
    computed: {
      ...mapState({
        customization: (state) => state.customization
      }),
  
      getlinkColor(){
        return this.customization.hasOwnProperty('navigation_link_color') ? this.customization.navigation_link_color ? this.customization.navigation_link_color : '#b52210' : '#b52210'
      },
    }
  }
  </script>