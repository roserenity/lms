<template>
  <v-card :class="`fill-height border pa-5 pb-9 ${ !loading && 'pt-9' }`">
    <LinearProgress v-if="loading" class="mb-3"/>
    <section class="d-flex justify-space-between align-center mb-3">
      <h3 class="f24 fw600 poppins secondary-1--text mr-auto">
        {{ title }}
      </h3>
      <FilterMenu
          :initialFilter="filter"
          @resetFilters="resetFilters"
          @applyFilters="apply"
        >
        <FormCheckbox val="course" label="Courses" :modelValue.sync="filter.type"/>
        <FormCheckbox val="assessment" label="Assessments" :modelValue.sync="filter.type"/>
        <FormCheckbox val="meeting" label="Meetings" :modelValue.sync="filter.type"/>
      </FilterMenu>
    </section>
    <section class="d-flex flex-row align-center poppins">
      <v-spacer />
      <v-btn small dense text @click="today" color="secondary-1">
        TODAY
      </v-btn>
      <v-btn small dense text @click="prev" color="secondary-1" class=" mx-1">
        <v-icon>mdi-chevron-left</v-icon>
      </v-btn>
      <FormMonthPicker 
        :val="`${selectedYear}-${selectedMonth < 10 ? `0${selectedMonth}` : selectedMonth }`"
        @setMonth="(e) => { selectedMonth = e }"
        @setYear="(e) => { selectedYear = e }"
        @apply="apply"/>
      <v-btn small dense text @click="next" color="secondary-1" class="ml-1">
        <v-icon>mdi-chevron-right</v-icon>
      </v-btn>
    </section>
    <FullCalendar
      ref="fullCalendar"
      :options="eventss">
      <template #eventContent="arg">
        <div style="height:20px;">
          <v-menu
            open-on-hover
            bottom
            max-width="250"
            z-index="10"
          >
            <template v-slot:activator="{ on, attrs }">
              <div style="height:20px;" v-bind="attrs" v-on="on" class="text-truncate fw500">
                {{arg.event.title}}
              </div>
            </template>
            <v-card class="pa-2" style="margin-left: 0px" width="250">
              <v-row dense>
                <v-col cols="1" class="poppins mt-1">
                  <v-sheet :style="`background-color: ${arg.event.textColor}; width: 100%`" height="15" class="rounded-sm" />
                </v-col>
                <v-col class="fw500 text-wrap f14">
                  <div class="f11 secondary-3--text text-capitalize poppins">{{ arg.event.extendedProps.type }}</div>
                  {{arg.event.title}}
                </v-col>
              </v-row>
              <v-row dense>
                <v-col cols="1">
                  <v-icon size="15">mdi-calendar</v-icon>
                </v-col>
                <v-col class="d-flex flex-row f12 text-wrap poppins mt-1">
                  <span v-if="arg.event.start"> {{ $dateFormat.mmDDyy(arg.event.start) }} </span>
                  <span v-if="arg.event.end"> -{{ $dateFormat.mmDDyy(arg.event.end) }} </span>
                </v-col>
              </v-row>
              <v-row dense v-if="arg.event.extendedProps.type === 'meeting'">
                <v-col cols="1">
                  <v-icon size="15">mdi-clock-outline</v-icon>
                </v-col>
                <v-col class="d-flex flex-row f12 text-wrap poppins mt-1">
                  <span>   {{ $dateFormat.timeAndPeriod(arg.event.extendedProps.start_date +'T' +arg.event.extendedProps.time + ':00') }} </span>
                </v-col>
              </v-row>
              <v-btn 
                v-if="arg.event.extendedProps.type === 'meeting'"
                class="text-capitalize poppins f12 mt-2" 
                color="primary"
                block
                target="_blank" 
                :href="arg.event.extendedProps.join_url"
                >Join</v-btn>
              <v-btn 
                v-else
                class="text-capitalize poppins f12 mt-2" 
                color="primary"
                block
                @click="$router.push({ name: 'User Course Details', params: { id: arg.event.extendedProps.uuid}})"
                >{{ arg.event.extendedProps.type === 'assessment'  ? 'View Course' : 'View' }}</v-btn>
            </v-card>
          </v-menu>
        </div>
      </template>
    </FullCalendar>
  </v-card>
</template>

<script>
import '@fullcalendar/core/vdom'
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import { mapActions, mapState, mapMutations } from 'vuex'

export default {
  components: {
    FullCalendar,
  },
  data: () => ({
    focus: '',
    type: 'month',
    colors: ['blue', 'indigo', 'deep-purple', 'cyan', 'green', 'orange', 'gre-darken-1'],
    selectedMonth: null,
    selectedYear: null,
    loading: false,
    menu: false,
    title: '',
    filter: {
      type: []
    }
  }),

  mounted() {
    this.getCalendarEvents()
  },

  computed: {
    ...mapState('usr', {
      eventss(state){
        var vm = this;
        let _events = state.calendar
        let options = {
          plugins: [ dayGridPlugin, listPlugin, timeGridPlugin ],
          initialView: 'dayGridMonth',
          events: _events,
          headerToolbar: {
            left: '',
            center: '',
            right: ''
          },
          dayMaxEvents: true, // for all non-TimeGrid views
          views: {
            dayGridMonth: {
              dayMaxEvents: 3 // adjust to 6 only for timeGridWeek/timeGridDay
            }
          },
          contentHeight: 'auto',
          datesSet: function(info){
            let {start, end} = info
            const dateObjectStart = new Date(start);
            const dateObjectEnd = new Date(end);

            if (dateObjectEnd.getMonth() === 0) {
              vm._data.selectedMonth = 12
              vm._data.selectedYear = dateObjectStart.getFullYear();
            } else {
              vm._data.selectedMonth = dateObjectEnd.getMonth();
              vm._data.selectedYear = dateObjectEnd.getFullYear();
            }
          },
          // eventClick: function(info) {
          //   var eventObj = info.event;
          //   vm.$router.push({ name: 'User Course Details', params: { id: eventObj.extendedProps.uuid}})
          // },
          // eventMouseEnter: function(info) {
          //   console.log(info)
          // }
        }
        return options
      }
    }),

  },

  methods: {
    ...mapActions('usr', ['getCalendarEventsAction']),
    ...mapMutations(['alertMutation']),

    getCalendarEvents(){
      if(!this.loading) {
        this.loading = true
        this.getCalendarEventsAction({ month: this.selectedMonth, year: this.selectedYear, ...this.filter}).catch(() => {
          this.alertMutation({
              show: true,
              text: 'Something went wrong',
              type: "error"
          })
        }).finally(() => {
          this.loading = false
          const calendarApi = this.$refs.fullCalendar.getApi();
          this.title = calendarApi.view.title;
        })
      }
    },

    prev() {
      this.getCalendarEvents()
      this.$refs.fullCalendar.getApi().prev();
    },

    next() {
      this.getCalendarEvents()
      this.$refs.fullCalendar.getApi().next();
    },

    today() {
      this.getCalendarEvents()
      this.$refs.fullCalendar.getApi().today();
    },

    apply(){
      this.$refs.fullCalendar.getApi().gotoDate(`${this.selectedYear}-${this.selectedMonth}-01`)

      this.getCalendarEvents()
    },

    resetFilters(){
      this.filter =  {
        type: []
      }
    }
    
  },
}
</script>

<style>
  .fc-toolbar-title {
    font-family: 'Poppins';
  }
  span.fc-icon.fc-icon-chevron-left::before {
    vertical-align: initial !important;
  }
  span.fc-icon.fc-icon-chevron-right::before {
    vertical-align: initial !important;
  }
  .fc .fc-popover {
    z-index: 1 !important;
  }
</style>
