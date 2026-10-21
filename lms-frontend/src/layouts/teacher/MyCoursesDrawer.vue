<template>
  <v-navigation-drawer class="drawer" fixed permanent app style="margin-left: 80px" width="200" stateless>
    <h3 class="poppins fw600 mx-7 mb-5" style="margin-top: 120px"> 
      My Courses
    </h3>
    <v-list>
      <v-list-item dense class="px-7 li" 
        v-for="(item, i) in lists" 
        :to="{ name: item.route }"
        :key="i"
        exact
        :class="$route.name === item.route ? 'li-active' : ''"
      >
        <v-list-item-content>
          <v-list-item-title class="poppins fw600 f14 d-flex align-center" 
           :class="$route.name === item.route ? 'secondary-1--text' : 'secondary--text'">
            {{item.name}}
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item dense class="px-7 li li-active" v-if="$route.name === 'Instructor Manage Course'" exact>
        <v-list-item-content>
          <v-list-item-title class="poppins fw600 f14 d-flex align-center secondary-1--text" >
            Course Manager
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
export default {
  props: ['mini'],
  data: () => ({
    lists: [
      {
        name: 'Uploaded',
        route: 'Instructor Uploaded Course'
      },
      {
        name: 'Drafts',
        route: 'Instructor Drafts Course'
      },
      // {
      //   name: 'Course Manager',
      //   route: 'Instructor Course Manager'
      // },
    ]
  })
}
</script>