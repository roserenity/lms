<template>
  <section id="section" class="my-10 nunito">
    <v-row no-gutters>
      <v-col cols="12" lg="3">
        <Sidebar :items="items"/>
      </v-col>
      <v-col cols="12" lg="9" class="px-5">
        <router-view/>
      </v-col>
    </v-row>
    
  </section>
</template>

<script>
import Sidebar from '../../components/teacher/settings/Sidebar.vue'

export default {
  components: {
    Sidebar
  },
  data: () => ({
    items: [
      {
        header: 'Account',
        lists: [
          {
            title: 'Manage Profile',
            icon: 'mdi-account',
            route: 'Teacher Account Setting'
          }
        ]
      },
      {
        header: 'Appearance',
        lists: [
          {
            title: 'Dark Mode and Text Size',
            icon: 'mdi-format-color-fill',
            route: 'Teacher Appearance'
          }
        ]
      },
    ]
  }),
  mounted() {
    this.$emit('hideNotif')
  },

  beforeDestroy() {
    this.$emit('showNotif')
  }
}
</script>
