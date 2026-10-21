<template>
    <section>
      <v-expansion-panels class="" accordion v-model="expand">
        <v-expansion-panel>
          <v-expansion-panel-header color="secondary-5"
            class="m-0 p-0 mat_expansion_panel ">
            <template v-slot:default="{  }">
            <v-container class="m-0 p-0 d-flex flex-row align-center roboto f14 fw500 secondary-1--text">
                <v-icon class="mr-3" color="primary">mdi-video-account</v-icon>
                <section class="d-flex flex-column">
                  <b>Synchronous Meetings</b> 
                </section>
            </v-container>
            </template>
          </v-expansion-panel-header>
          <v-expansion-panel-content class="expansion-timeline">
            <v-container class="roboto secondary-2--text f12 fw500 pl-7" v-if="!course.zoom_meetings || course.zoom_meetings.length === 0">
              No scheduled meetings
            </v-container>
            <v-list v-else>
                <v-list-item dense class="d-flex flex-column align-center" v-for="_meeting in course.zoom_meetings" :key="_meeting.id">
                    <section class="d-flex flex-column">
                        <div class="roboto f13 fw500 secondary-1--text">{{_meeting.data.topic}}</div>
                        <!-- <div class="roboto f13 fw500 secondary-1--text">SCHEDULE: {{ $dateFormat.dayDateTime(_meeting.data.start_time)}}</div> -->
                    </section>
                    <v-divider class="my-1"/>
                </v-list-item>
            </v-list>
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-expansion-panels>
    </section>
  </template>
  
<style>
</style>
  
<script>
  export default {
    name: 'course-meetings',
    props: ['course'],
    data: () => ({
      expand: null
    }),
    mounted(){
      if(!this.course.zoom_meetings || this.course.zoom_meetings.length <= 3) {
        this.expand = 0
      }
    }
  }
</script>