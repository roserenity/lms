<template>
  <section id="top-bar">
    <v-app-bar app color="app-body" flat height="100">
      <v-row justify="center" align="center">
        <v-col cols="12" xl="11">
          <v-toolbar v-toolbar class="custom-border border">
            <v-btn class="hidden-lg-and-up" icon @click="$emit('showDrawer')" dense>
              <v-icon size="22">mdi-menu</v-icon>
            </v-btn>
            <div class="poppins fw600 secondary-1--text hidden-lg-and-up">
              Tenant Management
            </div>
            <v-spacer/>
            <div class="d-flex align-center">
              <!-- <div class="">
                <v-text-field 
                  class="poppins f14" 
                  dense 
                  flat 
                  solo 
                  prepend-inner-icon="mdi-magnify" 
                  hide-details 
                  placeholder="Search"
                />
              </div> -->
              <v-btn v-if="!$vuetify.breakpoint.smAndDown"
              @click="$emit('add')" color="primary" outlined class="text-capitalize poppins fw600 f12 small mr-2">
                <v-icon small left>mdi-plus</v-icon>
                Add Tenant
              </v-btn>
              <v-btn
                @click="$emit('add')"
                icon
                color="primary"
                v-if="$vuetify.breakpoint.smAndDown"
                class="text-capitalize poppins fw600 f12 small mr-1"
                dense
              >
                <v-icon>mdi-account-multiple-plus-outline</v-icon>
              </v-btn>

              <v-avatar size="30" v-if="$route.name !== 'Instructor Manage Course'">
                <img
                  alt="Avatar"
                  :src="user.image?user.image.url:require('../../assets/default-photo.png')"
                  :lazy-src="require('../../assets/default-photo.png')"
                  v-on:error="user.image = null"
                >
              </v-avatar>
              <v-menu
                offset-y
                transition="slide-y-transition"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon
                      v-bind="attrs"
                      v-on="on"
                      v-if="$route.name !== 'Instructor Manage Course'"
                      small>
                      <v-icon>
                        mdi-chevron-down
                      </v-icon>
                  </v-btn>
                </template>
                <CommonToolsVue :role="'super-admin'"/>
              </v-menu>
              <!-- <v-btn icon>
                <v-icon>mdi-weather-night</v-icon>
              </v-btn> -->
            </div>
          </v-toolbar>
        </v-col>
      </v-row>
      <toaster
        v-if="alert.show"
        :show="alert.show"
        :text="alert.text"
        :type="alert.type"
        @close="alert.show = false"
      />
    </v-app-bar>
    
  </section>  
</template>

<script>
import { mapState } from "vuex";
import CommonToolsVue from '@/components/reusable/CommonTools.vue';

export default {
  props: ["user"],
  components: {
    CommonToolsVue
  },
  data: () => ({
    items: ['User','Teacher', 'Admin'],
    dialog: false,
    role: ''
  }),
  computed: {
    ...mapState({
      alert: (state) => state.alert,
    }),
  }
}
</script>
