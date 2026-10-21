<template>
  <section id="dashboard" class="pb-10">
      <v-row dense>
        <v-col
            cols="12"
            md="3"
            sm="6"
        >
            <v-menu
                ref="menu"
                v-model="menu"
                :close-on-content-click="false"
                transition="scale-transition"
                offset-y
                left
                bottom
                min-width="auto"
            >
                <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                        v-model="dateRange"
                        prepend-inner-icon="mdi-calendar"
                        dense
                        hide-details
                        class="f14 poppins fw500 ml-auto general-custom-field custom-border border"
                        elevation="0"
                        readonly
                        v-bind="attrs"
                        v-on="on"
                        solo
                        flat
                        :loading="cardLoading"
                    ></v-text-field>
                </template>
                <v-date-picker
                    v-model="dates"
                    no-title
                    scrollable
                    color="primary"
                    :max="`${$dateFormat.ymd(Date.now())}`"
                    elevation='2'
                    right
                    range
                ></v-date-picker>
            </v-menu>
        </v-col>
        
    </v-row>
    <v-row class="mb-5">
      <v-col cols="4" md="4" sm="6" v-for="(item, i) in items" :key="i">
        <TopCard :item="item" :loading="cardLoading"/>
      </v-col>
    </v-row>
    <v-row class="mb-5" >
      <v-col v-for="(item, i) in graphItems" :key="i" cols="12" md="4" sm="12">
        <v-card align="center" class="custom-border border pa-5">
          <ApexDonutGraph :series="item.series" :labels="item.labels" :title="item.title" :loading="pieGraphLoading" :filename="item.title"/>
        </v-card>
      </v-col>
    </v-row>

    <v-card class="custom-border border pa-10 mb-5">
    <section class="d-flex flex-wrap align-center justify-space-between mb-5">
      <div>
        <h4 class="poppins mb-1">
            Enrolled Learners
        </h4>
        <FormLabel v-if="dates[0] || dates[1]" :label="`(${dates[0] ? `from ${$dateFormat.mmDDyy(dates[0])}` : ''} ${dates[1] ? `to ${$dateFormat.mmDDyy(dates[1])})` : ''}`" class="mb-3"/>
      </div>
      <div class="d-flex flex-row align-center ml-auto">

        <FilterMenu
            :initialFilter="course_filter"
            @resetFilters="clearFilters"
            @applyFilters="getDashboardGraphData"
            class="mb-2"
        >
          <v-sheet max-height="350" class="overflow-y-auto scroller">
              <FormSelectObject
                  :label="'STATUS'"
                  :items="status"
                  :value.sync="course_filter.status"
              />
              
              <FormSelectObject
                  class="mt-3"
                  :label="'TYPE OF LEARNING'"
                  :items="type_of_learnings"
                  :value.sync="course_filter.type_of_learning"
              />

              <div class="mt-3">
                  <FormLabel class="mb-1" :label="'COURSE CONDUCTED DATE (YYYY-MM-DD)'" />
              </div>
              <label class="caption">FROM:</label>
              <FormDatePicker 
                  :val="course_filter.date_from"
                  :max="maxDate"
                  class_="col-11"
                  @apply="(e) => {
                      course_filter.date_from = e
                      minDate = e
                  }"/>

              <label class="caption">TO:</label>
              <FormDatePicker 
                  :val="course_filter.date_to"
                  :min="minDate"
                  class_="col-11"
                  @apply="(e) => {
                      course_filter.date_to = e
                      maxDate = e
                  }"/>`
          </v-sheet>
        </FilterMenu>
      </div>
    </section>
    
      <FormSelectArray
        :items="filters"
        :value.sync="selected"
        :loading="loading1"
        class="mb-5 col-5 ml-auto pa-0"
      />
      <LinearProgress v-if="graphLoading" />
      <ApexHorizontalBarGraph :series="chartData" :filename="'Enrollments'"/>
    </v-card>
    <section v-if="!showUsers" >
      <CourseTable 
        v-if="!loading" 
        @viewTotalEnrolleeList="viewTotalEnrolleeList"
        @viewEnrolleeList="viewEnrolleeList" 
        @viewCompletersList="viewCompletersList" 
        :selected="selected" 
        :dates="dates" 
        :menu="menu"/>
    </section>
    <CourseUserTable 
        v-if="showUsers" 
        :loading="loading" 
        :items="users" 
        @back="back" 
        :listName="listName"
        :show="show"
        :page="page"
        :paginate="paginate"
        :pageCount="pageCount"
        :totalCount="totalCount"
        :filter="filter"
        :value="value"
        :dates="dates"
        @clear="() => { search = '', page = 1, getUsers() }"
        @search="(e) => { search = e, page = 1, getUsers() }"
        @page="(e) => { page = e, getUsers() }"
        @paginate="(e) => { paginate = e, page = 1, getUsers() }"
      />
  </section>
</template>

<script>
import { mapActions, mapMutations } from 'vuex';
import ApexDonutGraph from "@/components/reusable/graphs/ApexDonutGraph.vue"
import ApexHorizontalBarGraph from "@/components/reusable/graphs/ApexHorizontalBarGraph.vue"
import TopCard from '@/components/reusable/TopCard.vue'
import CourseTable from '@/components/admin/dashboard/CourseTable.vue';
import CourseUserTable from '@/components/admin/dashboard/CourseUserTable.vue';

export default {
  components: {
    TopCard,
    CourseTable,
    CourseUserTable,
    ApexHorizontalBarGraph,
    ApexDonutGraph
  },
  created() {
    
    let _now = new Date(Date.now());
    this.dates = [`${this.$dateFormat.ymd(`2023-06-06`)} 00:00:00`, `${this.$dateFormat.ymd(_now)} 23:59:59`]
    this.prevDates = this.dates
    this.getDashboardCards()
    this.getDashboardPieGraphs()
  },
  data: () => ({
    items: [
      {
        title: 'Total Registered Users',
        count: '-',
        color: '#2A338F33', // Light lavender
        outline: '#2A338F' // Deep blue
      },
      {
        title: 'Total Enrollments',
        count: '-',
        color: '#EC1C2D33', // Light red
        outline: '#EC1C2D' // Bright red
      },
      {
        title: 'Total Unique Enrolled Learners',
        count: '-',
        color: '#D9121A33', // Softer red
        outline: '#D9121A' // Darker red
      },
      {
        title: 'Total Completions',
        count: '-',
        color: '#C26C0033', // Light beige
        outline: '#C26C00' // Earthy orange
      },
      {
        title: 'Total Unique Completers',
        count: '-',
        color: '#B9540033', // Peach
        outline: '#B95400' // Warm orange
      },
      {
        title: 'Total Instructors',
        count: '-',
        color: '#EF891733', // Pastel orange
        outline: '#EF8917' // Vibrant orange
      },
      {
        title: 'Total No. of Transactions',
        count: '-',
        color: '#525BB733', // Light periwinkle
        outline: '#525BB7' // Soft blue
      },
      {
        title: 'Total Created Courses',
        count: '-',
        color: '#FF304133', // Pale pink
        outline: '#FF3041' // Bold pink
      },
      {
        title: 'Total Received Tickets',
        count: '-',
        color: '#FFB13F33', // Light amber
        outline: '#FFB13F' // Rich gold
      }
    ],
    graphItems: [
      {
        title: 'Users'
      },
      {
        title: 'Tickets'
      },
      {
        title: 'Transactions'
      }
    ],
    dates: [],
    prevDates: [],
    menu: false,
    users: [],
    filters: [ 'Total Users per Course' ],
    filters_obj: {},
    selected: 'Total Users per Course',
    showUsers: false,
    listName: '',
    status_filter: [
      {
        text: 'Completed', value: 'completed',
      },
      {
        text: 'Enrolled', value: 'enrolled'
      },
    ],
    status: {
      text: 'Completed', value: 'completed',
    },
    menu_from: false,
    menu_to: false,
    date_from: '',
    date_to: '',
    cardLoading: false,
    pieGraphLoading: false,
    graphLoading: false,
    loading: false,
    loading1: false,
    pageCount: 1,
    page: 1,
    paginate: '10',
    totalCount: 0,
    search: '',
    filter: '',
    value: '',
    chartData: [{
        name: 'Enrolled Learners Count',
        data: [{
            x: '',
            y: 0
        }]
    }],
    show: '',
    minDate: '',
    maxDate: '',
    course_filter: {
        status: '',
        type_of_learning: '',
        date_from: ``,
        date_to: ``,
        enrolled_from: '',
        enrolled_to: ''
    },
    type_of_learnings: [
        { text: 'ALL', value: '' },
        { text: 'E-LEARNING', value: 'E-LEARNING' },
        { text: 'BLENDED', value: 'BLENDED' },
        { text: 'MICROLEARNING', value: 'MICROLEARNING' },
    ],
    status: [
        { text: 'ALL', value: '' },
        { text: 'PUBLISHED', value: 'PUBLISH' },
        { text: 'UNPUBLISHED', value: 'UNPUBLISH' },
    ],
  }),
  methods: {
    ...mapMutations(['alertMutation']),
    ...mapActions('admin', ['getDashboardCardsAction', 'getDashboardPieGraphsAction', 'getDashboardRegistrationFieldListAction', 'getDashboardTotalUsersBarGraphAction', 'getDashboardEnrolledLearnersAction', 'getDashboardCompletedLearnersAction']),

    clearFilters(){
      this.course_filter = {
          status: '',
          type_of_learning: '',
          date_from: ``,
          date_to: ``,
          enrolled_from: '',
          enrolled_to: ''
      }

      this.minDate = ''
      this.maxDate = ''

      this.getDashboardGraphData()
    },

    getDashboardCards() {
      this.cardLoading = true
      this.getDashboardCardsAction({ date_from: this.dates[0], date_to: this.dates[1] }).then(res => {
        this.cardLoading = false
        if(res) {
          let key = ['total_registered_users', 'total_enrolled_users', 'total_unique_enrolled_users', 'total_completers_count', 'total_unique_completers_count', 'total_onboarded_instructors', 'total_transactions', 'total_courses', 'total_tickets']

          this.items.forEach((item, i) => {
            item.count = res[key[i]]
          })
        }

        this.getDashboardGraphData()
      }).catch(() => {
        this.cardLoading = false
      })
    },

    getDashboardPieGraphs(){
      this.pieGraphLoading = true

      this.getDashboardPieGraphsAction({ date_from: this.dates[0], date_to: this.dates[1] }).then(res => {
        this.graphItems = Object.values(res)

        let titles = Object.keys(res)
        this.graphItems.forEach((item, i) => {
          item.title = titles[i]
        })

        this.pieGraphLoading = false
      }).catch(() => {
        this.pieGraphLoading = false
      })
    },

    viewTotalEnrolleeList(e){
      this.show = 'TOTAL ENROLLEES'
      this.showUsers = true
      this.listName = e.listName
      this.loading = true

      this.filter = e.filter
      this.value = e.value

      this.getDashboardEnrolledLearnersAction({
        ...e,
        paginate: Number(this.paginate),
        page: this.page,
        search: this.search,
        date_from: '',
        date_to: ''
      }).then( res => {
        this.users = res.data
        this.totalCount = res.total
        this.pageCount = res.last_page
        this.page = res.current_page
        this.loading = false
      }).catch(() => {
        this.loading = false
      })

    },

    viewEnrolleeList(e) {
      this.show = 'ENROLLEES'
      this.showUsers = true
      this.listName = e.listName
      this.loading = true

      this.filter = e.filter
      this.value = e.value

      this.getDashboardEnrolledLearnersAction({
        ...e,
        paginate: Number(this.paginate),
        page: this.page,
        search: this.search,
        date_from: this.dates[0],
        date_to: this.dates[1]
      }).then( res => {
        this.users = res.data
        this.totalCount = res.total
        this.pageCount = res.last_page
        this.page = res.current_page
        this.loading = false
      }).catch(() => {
        this.loading = false
      })
    },

    viewCompletersList(e) {
      this.show = 'COMPLETERS'
      this.showUsers = true
      this.listName = e.listName
      this.loading = true

      this.filter = e.filter
      this.value = e.value

      this.getDashboardCompletedLearnersAction({
        ...e,
        paginate: Number(this.paginate),
        page: this.page,
        search: this.search,
        date_from: this.dates[0],
        date_to: this.dates[1]
      }).then( res => {
        if(this.filter === 'Total Users per Course') {
          let _data = []
          res.data.forEach(item => _data.push(item.user))
          this.users = _data
        } else {
          this.users = res.data
        }
        this.totalCount = res.total
        this.pageCount = res.last_page
        this.page = res.current_page
        this.loading = false
      }).catch(() => {
        this.loading = false
      })
    },

    back() {
      this.search = ''
      this.showUsers = false
      this.users = []
      this.selected = this.selected
    },

    getDashboardGraphData() {
      this.graphLoading = true
      this.getDashboardTotalUsersBarGraphAction({ filter: this.selected, ...this.course_filter, enrolled_from: this.dates[0] ? this.$dateFormat.mmDDyy(this.dates[0]) : '', enrolled_to: this.dates[1] ? this.$dateFormat.mmDDyy(this.dates[1]) : '' }).then(res => {
        if(this.selected === 'Total Users per Course'){
          let courses = {}
          let _data = []
          res.forEach(item => {
              courses[item.title ? item.title : 'Untitled'] = item.enrollments_count

              let title_split = []
              item.title.split(' ').forEach((word, index) => {
                  if (index === 0) {
                      title_split.push(word);
                  } else {
                      let prev = title_split[title_split.length - 1].length;
                      let curr = word.length;
                      
                      if ((prev + curr + 1) <= 12) {
                          title_split[title_split.length - 1] = `${title_split[title_split.length - 1]} ${word}`;
                      } else {
                          title_split.push(word);
                      }
                  }
              });

              _data.push({
                  x: title_split,
                  y: item.enrollments_count,
                  enrollees: item.enrollments_count,
                  status: item.status + 'ED',
                  type_of_learning: item.type_of_learning,
                  availability_dates: `${(item.available_until?.start_date) ? `from ${this.$dateFormat.mmDDyy(item.available_until.start_date)} ` : ''}${item.available_until?.end_date ? `until ${this.$dateFormat.mmDDyy(item.available_until.end_date)}` : ''}`
              })

              if(_data.availability_dates === '') {
                  _data.availability_dates = '-'
              }

              this.chartData = [{
                  name: 'Enrollee Count',
                  data: [..._data]
              }]
          }) 
          this.chartData = [{
              name: 'Enrolled Learners Count',
              data: _data
          }]
        } else {
          let _data = Object.entries(res).map(([title, count]) => ({ x: title, y: count }));
          this.chartData = [{
              name: 'Enrolled Learners Count',
              data: _data
          }]
        }


        this.graphLoading = false
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
        this.chartData = []
        this.graphLoading = false
      })
    },

    getUsers(){
      let params = { value: this.value, listName: this.listName, filter: this.selected }

      switch(this.show){
        case 'ENROLLEES':
          this.viewEnrolleeList(params)
          break;
        case 'COMPLETERS':
          this.viewCompletersList(params)
          break;
        case 'TOTAL ENROLLEES':
          this.viewTotalEnrolleeList(params)
          break;
      }
    }
  },
  mounted() {

    this.loading1 = true
    this.getDashboardRegistrationFieldListAction().then( res => {
        this.filters.push(...res)
        this.loading1 = false
    }).catch(() => {
      this.loading1 = false
    })
  },
  watch: {
    selected(val) {
      this.showUsers = false

      this.getDashboardGraphData()
    },
    filter() {
      this.page = 1
    },
    search(){
      this.page = 1

      if(this.showUsers) {
        this.getUsers()
      }
    },
    menu(val) {
        if(!val) {
          if(this.prevDates !== this.dates) {
            if(this.dates.length === 1) {
              this.dates = [`${this.dates[0]} 00:00:00`, `${this.dates[0]} 23:59:59`]
            } else {
              this.dates = [`${this.dates[0]} 00:00:00`, `${this.dates[1]} 23:59:59`]
            }
            this.prevDates = this.dates
            this.getDashboardCards()
            this.getDashboardPieGraphs()
          }
        }
    },
    show() {
      this.page = 1
    }
  },

  computed: {
    dateRange() {
        return this.dates.map(item => this.$dateFormat.mmDDyy(item)).join(' - ');
    }
  },
}
</script>