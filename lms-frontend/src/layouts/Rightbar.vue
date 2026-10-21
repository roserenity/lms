<template>
  <v-navigation-drawer v-model="drawer" right :app="overlay" 
    :hide-overlay="fasle" fixed clipped 
    floating width="300" height="auto" class="nunito pa-5"
    stateless
    :style="!overlay ? 'margin-top: 100px':''"  
  >
    <div class="d-flex align-center justify-space-between">
      <div class="font-weight-bold" style="font-size: 18px">
        Notifications
      </div>
      <v-btn text small class="text-capitalize">
        Mark all as read
      </v-btn>
    </div>
    <v-divider class="my-2"/>
    <div class="text-center grey--text my-2" style="font-size: 12px">
      TODAY
    </div>
    <div class="" style="max-height:350px; overflow-x: hidden; overflow-y: auto">
      <v-sheet v-for="i in 5" :key="i" class="pa-3 my-2 d-flex align-center yellow lighten-5 rounded">
        <v-avatar size="40">
          <img src="https://avatars0.githubusercontent.com/u/9064066?v=4&s=460">
        </v-avatar>
        <div class="ml-2">
          <div class="font-weight-bold" style="font-size: 14px">
            Juan Dela Cruz
            <span class="grey--text" style="font-size: 12px">
              commented on your subject “MATH 3”.
            </span>
          </div>
        </div>
      </v-sheet>
    </div>
    <div class="d-flex align-center justify-space-between mt-7">
      <div class="font-weight-bold" style="font-size: 18px">
        Schedules
      </div>
    </div>
    <v-divider class="mt-2 mb-5"/>
    <v-sheet 
    style="border: 1px solid #5DB1FF; 
    background: rgba(93, 177, 255, 0.13); 
    border-raduis: 6px" 
    class="pa-3 my-2">
      <small class="">ON SCHEDULE</small>
      <div class="d-flex justify-space-between align-center">
        <div class="body-1 nunito font-weight-bold">
          Zoom Meeting
        </div>
        <v-btn icon>
          <v-icon>
            mdi-arrow-right
          </v-icon>
        </v-btn>
      </div>
    </v-sheet>
  </v-navigation-drawer>
</template>

<script>
export default {
  props: ['drawer', 'overlay'],
  data: () => ({
    stateless: false,
  }),
  created() {
    this.$route.name.includes('Super Admin') ? this.stateless=true : this.stateless=false 
  }
}
</script>