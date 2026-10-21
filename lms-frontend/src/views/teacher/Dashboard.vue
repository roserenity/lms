<template>
  <section id="dashboard">
    <v-card class="custom-border border pa-2">
      <v-card-title>
        <h4 class="poppins secondary-1--text fw600">Dashboard</h4>
        <v-spacer/>
        <div class="d-flex align-center">
          <v-btn 
            text 
            small
            @click="gallery =  true"
            class="hidden-sm-and-down"
          >
            <v-icon color="" :class="gallery ? 'primary--text' : 'secondary-2--text'">
              mdi-view-gallery-outline
            </v-icon>
          </v-btn>
          <v-btn 
            text 
            small
            @click="gallery = false"
            class="hidden-sm-and-down"
          >
            <v-icon color="" :class="!gallery ? 'primary--text' : 'secondary-2--text'">
              mdi-format-list-bulleted
            </v-icon>
          </v-btn>
        </div>
      </v-card-title>

      <section class=" px-3 d-flex flex-wrap align-center justify-space-between">
        <SearchBar 
          :placeholder="'Search course'" 
          :outlined="true" 
          :value.sync="search" 
          @clear="clearSearch"
          @search="onSearch"/>
        <FilterMenu
            :initialFilter="filter"
            @resetFilters="() => {
              minDate = ''
              maxDate = ''
              resetFilters()
            }"
            @applyFilters="() => { page = 1, updateQuery() }"
          >
          <v-sheet max-height="300" class="overflow-y-auto scroller">
            <div class="mt-3">
                <FormLabel class="mb-1" :label="'TYPE OF LEARNING'" />
                <FormSelectObject
                  :items="type_of_learning"
                  :value.sync="filter.type_of_learning"
                  :class="'ma-1'"
                />
            </div>
            <div class="mt-3">
                <FormLabel class="mb-1" :label="'COURSE CREATION DATE (YYYY-MM-DD)'" />
            </div>
            <label class="caption">FROM:</label>
            <FormDatePicker 
                :val="filter.created_from"
                :max="maxDate"
                class_="col-11 mb-3"
                @apply="(e) => {
                    filter.created_from = e
                    minDate = e
                }"/>

            <label class="caption">TO:</label>
            <FormDatePicker 
                :val="filter.created_to"
                :min="minDate"
                class_="col-11 mb-3"
                @apply="(e) => {
                    filter.created_to = e
                    maxDate = e
                }"/>
          </v-sheet>
        </FilterMenu>
      </section>
      <section class=" px-3 mt-3" v-if="loading">
        <LinearProgress/>
      </section>

      <v-card-text v-if="courses.length>0" class="d-flex" :class="[$vuetify.breakpoint.mobile && 'justify-center', gallery ? 'flex-wrap' : 'flex-column']">
        <CourseCardGallery 
          v-if="gallery"
          v-for="(item,i) in courses"
          :key="item.id"
          :i="i" 
          :item="item" 
          :gallery="gallery"
          class="ma-3"
          @fetchCourses="getInstructorDashboardCoursesAction"
        />
        <CourseCardList 
          v-if="!gallery"
          v-for="(item,i) in courses"
          :key="item.id"
          :i="i" 
          :item="item" 
          class="ma-3"
          @fetchCourses="getInstructorDashboardCoursesAction"
        />
      </v-card-text>

      <v-sheet class="d-flex flex-column align-center justify-center pb-10" height="500" v-if="courses.length === 0 && !loading">
        <div v-if="search === ''" class="d-flex flex-column align-center justify-center">
          <v-img 
            max-width="250" 
            :src="require('@/assets/default/empty_dashboard.png')"  />
            <h3 class="text-center poppins secondary-1--text fw600 my-5">You currently have no existing courses.</h3>
            <div class="text-decoration-underline f14 poppins primary--text text-center" @click="$emit('createCourse')">Create Course</div>
        </div>
        <h3 v-else class="text-center poppins secondary-1--text fw600 my-5">No results found.</h3>
      </v-sheet>
                        
      <FormPagination 
        :pageCount="pageCount" 
        :page="page"
        :paginate="paginationPaginate"
        @page="onPageChange" 
        @paginate="onPaginateChange"/>
    </v-card>
  </section>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import CourseCardGallery from '../../components/teacher/course/CourseCardGallery.vue'
import CourseCardList from '../../components/teacher/course/CourseCardList.vue'
import { type_of_learning } from '@/constants/typeoflearning';

export default {
  mixins: [searchAndPaginateMixin],
  props: ['right'],
  components: {
    CourseCardGallery,
    CourseCardList
  },
  data: () => ({
    loading: false,
    gallery: true,
    pageCount: 1,
    search: '',
    minDate: '',
    maxDate: '',
    type_of_learning
  }),
  computed: {
    ...mapState('instructor', {
      courses: (state) => state.courses
    }),

    paginationPaginate(){
      return String(this.paginate)
    }
  },
  mounted(){
    window.addEventListener('resize', this.onResize, {passive: true})
    this.onResize()
    
    this.minDate = this.filter.created_from
    this.maxDate = this.filter.created_to
  },
  methods: {
    ...mapActions('instructor', [
      'getInstructorDashboardCoursesAction'
    ]),

    onResize(){
      if(this.$vuetify.breakpoint.smAndDown || this.$vuetify.breakpoint.mobile){
        this.gallery = false
      }
    },

    resetFilters(){
      this.filter = {
        created_from: `${new Date().getFullYear()}-01-01`,
        created_to: `${new Date().toISOString().substr(0, 10)}`
      }

      this.filter.type_of_learning = ''
      this.page = 1
      this.updateQuery()
    },
    
    getData() {
      if(!this.loading) {
        this.loading = true
        this.getInstructorDashboardCoursesAction({ paginate: Number(this.paginate), page: this.page, search: this.search, ...this.filter}).then(res => {
          this.page = res.current_page
          this.pageCount = res.last_page
          this.paginate = String(res.per_page)
          this.loading = false;
        }).catch(() => {
          this.loading = false;
        });
      }
    }
  }
}
</script>