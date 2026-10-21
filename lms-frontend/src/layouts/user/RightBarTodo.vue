<template>
  <v-navigation-drawer class="hidden-md-and-down"  right app width="230" v-if="role === 'user'">
    <div class=" mt-7">
      <div class="d-flex align-center mx-5">
        <h3 class="success--text poppins">
          To Do
        </h3>
        <div class="mx-3">
          <v-badge content="5"></v-badge>
        </div>
      </div>
      <v-divider class="mt-5"/>
      <v-sheet class="" >
        <div class="poppins f14 text-center my-10 secondary-2--text">Under Construction</div>
        <!-- <v-sheet class="my-5 mx-5" v-for="i in 5" :key="i">
          <div class="d-flex justify-end">
            <v-btn x-small color="danger-1" class="" icon>
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
          <div class="roboto primary--text f13 fw500">Introduction to Earth Science Quiz</div>
          <div class="d-flex justify-space-between align-center secondary-2--text f12 fw500 roboto">
            <div class="">
              SCIENCE
            </div>
            <v-divider vertical/>
            <div class="">
              50 PTS
            </div>
            <v-divider vertical/>
            <div class="">
              JUNE 3, 2022
            </div>
          </div>
        <v-divider class="mt-3"/>
        </v-sheet> -->
        
      </v-sheet>
    </div>
  </v-navigation-drawer>
</template>

<script>
export default {
  props: ['role']
} 
</script>