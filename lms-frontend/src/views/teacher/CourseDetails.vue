<template>
  <v-card flat outlined class="mt-5">
    <v-row no-gutters>
      <v-col cols="12" md="8" class="flex-grow-0 flex-shrink-0" style="border-right: 1px solid #d8d6d6">
        <v-card flat>
          <breadcrumbs :items="links"/>
          <v-card-text>
            <v-img
              :aspect-ratio="16/9"
              max-height="500"
              class="rounded-lg"
              src="https://cdn.vuetifyjs.com/images/parallax/material.jpg"
            ></v-img>

            <v-tabs color="success" v-model="tab">
              <v-tab v-for="item in items" 
              :key="item" 
              class="nunito black--text text-capitalize font-weight-bold " 
              style="font-size: 16px; letter-spacing: .5px">{{item}}</v-tab>
            </v-tabs>
            <v-divider></v-divider>
            <v-tabs-items v-model="tab">
              <v-tab-item
                v-for="item in items"
                :key="item"
              >
                <AboutCourse v-if="item==='Course Description'"/>
              </v-tab-item>
            </v-tabs-items>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="4" class="flex-grow-1 flex-shrink-0 pa-3">
        <div class="text-center my-5 mx-3">
          <v-btn outlined class="body-1 nunito text-capitalize" block color="danger">
            Edit Course
          </v-btn>
        </div>
        <div class="ma-3">
          <div class="font-weight-bold" style="font-size: 18px">
            Course Navigation
          </div>
        </div>
        <div class="mx-2">
          <v-expansion-panels hover flat class="accordion">
            <v-expansion-panel
              v-for="(item,i) in 5"
              :key="i"
              class="my-1"
              active-class="active"
            >
              <v-expansion-panel-header class="header">
                Filipino
              </v-expansion-panel-header>
              <v-divider></v-divider>
              <v-expansion-panel-content class="pt-5">
                <v-expansion-panels hover flat class="accordion">
                  <v-expansion-panel
                    v-for="(item,i) in 5"
                    :key="i"
                    class="my-1"
                    active-class="active"
                  >
                    <v-expansion-panel-header class="header">
                      Filipino
                    </v-expansion-panel-header>
                    <v-divider></v-divider>
                    <v-expansion-panel-content>
                      
                    </v-expansion-panel-content>
                  </v-expansion-panel>
                </v-expansion-panels>
              </v-expansion-panel-content>
            </v-expansion-panel>
          </v-expansion-panels>
        </div>
      </v-col>
    </v-row>
    
  </v-card>
</template>

<script>
import AboutCourse from '../../components/teacher/course/AboutCourse.vue'

export default {
  components: {
    AboutCourse,
  },
  data: () => ({
    tab: null,
    items: [
      'Course Description',
      'Announcement',
      'Grades'
    ],
    links: [
      {
        text: 'My Courses',
        to: { name: 'Teacher My Courses' },
        disabled: false
      },
      {
        text: 'Filipino',
        to: '',
        disabled: true
      },
    ]
  }),
  created() {
    
  }
}
</script>
