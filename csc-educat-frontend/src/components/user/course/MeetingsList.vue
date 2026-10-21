<template>
    <section>
      <v-expansion-panels class="" accordion flat v-model="expand">
        <v-expansion-panel>
          <v-expansion-panel-header color="secondary-5"
            class="ma-0 px-7 mat_expansion_panel ">
            <template v-slot:default="{  }">
              <v-container class="ma-0 pa-0 d-flex flex-row align-center roboto f14 fw500 secondary-1--text">
                <v-icon class="mr-3">mdi-video-account</v-icon>
                <section class="d-flex flex-column">
                  <b>Join Live Session</b> 
                  <span class="secondary-2--text f11 font-italic mt-1">Use Google Chrome as the web browser</span>
                </section>
              </v-container>
            </template>
          </v-expansion-panel-header>
          <LinearProgress v-if="loading" />
          <v-expansion-panel-content class="expansion-timeline ">
            <v-container class="roboto secondary-2--text f12 fw500 pl-7" v-if="zoom_meetings.length === 0">
              No scheduled meetings
            </v-container>
            <v-list v-else>
                <v-list-item dense class="my-2 roboto" v-for="_meeting in zoom_meetings" :key="_meeting.id">
                    <v-list-item-title class=" f13" >
                      {{_meeting.data.topic}}
                      <v-list-item-subtitle class="mt-1 f11">
                        SCHEDULE: {{ $dateFormat.mmDDyy(_meeting.data.start_date) }} {{ $dateFormat.timeAndPeriod(_meeting.data.start_date +'T' + _meeting.data.start_time + ':00') }}
                      </v-list-item-subtitle >
                    </v-list-item-title>
                    <div class="d-flex flext-row">
                      <v-btn v-if="_meeting.attendee && _meeting.attendee.zoom_meeting_activity" dense icon class="text-capitalize primary--text f12 fw600" @click="() => { selected_zoom_meeting = _meeting.attendee, zoom_activity_dialog = true}">
                          <v-icon>mdi-text-box-edit-outline</v-icon>
                      </v-btn>
                      <v-btn dense class="primary f12 ml-1" target="_blank" :href="_meeting.data.join_url">
                          JOIN
                      </v-btn>
                    </div>
                </v-list-item>
            </v-list>
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-expansion-panels>

      <ZoomActivityDialog
        :dialog="zoom_activity_dialog"
        :selected_zoom_meeting="selected_zoom_meeting"
        @close="() => { selected_zoom_meeting = null, zoom_activity_dialog = false}"
      />
    </section>
  </template>
  
  <style>
  .v-expansion-panel-header.mat_expansion_panel {
    align-items: center !important;
    white-space: normal !important;
    word-wrap: break-word !important;
  }
  .expansion-timeline .v-expansion-panel-content__wrap {
    padding-bottom: 0 !important;
    padding-left: 10px !important;
    padding-right: 10px !important;
  }
  </style>
  
  <style scoped>
  p {
    margin-bottom: 5px !important;
  }
  </style>
  
  <script>
  import { mapMutations, mapActions, mapState } from 'vuex';
  import ZoomActivityDialog from "./ZoomActivityDialog.vue"

  export default {
    props: ['course'],
    components:{
      ZoomActivityDialog
    },
    created() {
      this.loading = true

      this.getCourseZoomMeetingsAction(this.course.id).finally(() => {
        this.loading = false
      })
    },

    data: () => ({
      expand_material: 0,
      loading: false,
      selected_zoom_meeting: null,
      zoom_activity_dialog: false
    }),
  
    methods: {
      ...mapActions('usr', [
        'getCourseZoomMeetingsAction'
      ]),
    },

    computed: {
      
      ...mapState('usr', {
        zoom_meetings: (state) => state.zoom_meetings
      }),
      
      expand(){
        switch( this.$vuetify.breakpoint.name ) {
            case 'xs': return null
            case 'sm': return null
            case 'md': return null
            case 'lg': return 0
            case 'xl': return 0
        }
      },
    }
    
  }
</script>