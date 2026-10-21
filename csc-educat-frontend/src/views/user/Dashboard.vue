<template>
  <section>
    <v-card class="custom-border border pa-2">
      <v-card-text>
        <span class="f18 poppins fw600 mr-auto">Published Courses</span>
        <div class=" d-flex justify-space-between align-center f18 secondary-1--text mb-5" :class="$vuetify.breakpoint.smAndDown ? 'flex-column' : 'flex-row'">
            <SearchBar 
              :placeholder="'Search course'" 
              :outlined="true" 
              :value.sync="search" 
              @clear="() => { search = '', page = 1, filterOtherCourses()}"
              @search="() => { page = 1, filterOtherCourses()}"
              class="my-1"/>
            <section class="d-flex justify-end align-center ml-auto flex-wrap my-1">
              <section style="width:150px">
                <FormSelectObject
                  :placeholder="'Type of Learning'"
                  :items="type_of_learning"
                  :value.sync="filter.type_of_learning"
                  :class="'ma-1'"
                />
              </section>
              <section style="width:150px">
                <FormSelectObject
                  :placeholder="'Category'"
                  :items="categories"
                  :value.sync="filter.classes"
                  :class="'ma-1'"
                  :loading="loading1"
                />
              </section>
              <section style="width:100px">
                <FormSelectArray
                  :placeholder="'Sort'"
                  :items="SORT_BY"
                  :value.sync="filter.course_name_sort"
                />
              </section>
          </section>
        </div>
      <v-progress-linear
          indeterminate
          color="primary"
          rounded
          height="3px"
          v-if="loading2"
          class="my-3"
        ></v-progress-linear>
        <div v-if="other_courses.length === 0 && !loading" class="poppins fw600 f13 secondary-4--text font-italic"> No courses available. </div>
        <section v-if="gallery" class="d-flex flex-wrap">
            <GalleryCards v-for="course in other_courses" :key="course.id" :course="course" class="ma-2"/>
        </section>
        <section v-if="!gallery" class="d-flex flex-column justify-center'">
            <ListCards v-for="course in other_courses" :key="course.id" :course="course" class="my-2"/>
        </section>
      </v-card-text>
      <FormPagination 
          :pageCount="pageCount" 
          :page="page"
          :paginate="paginate"
          @page="(e) => { page = e, filterOtherCourses() }" 
          @paginate="(e) => { paginate = e, filterOtherCourses() }"/>
    </v-card>
  </section>
</template>


<style>
.v-slide-group__prev, .v-slide-group__next {
  min-width: auto !important;
}
</style>

<script>
import { mapActions, mapState } from 'vuex';
import GalleryCards from '../../components/user/course/GalleryCard.vue'
import ListCards from '../../components/user/course/ListCard.vue'

export default {
  components: {
    GalleryCards,
    ListCards
  },
  data: () => ({
    loading: false,
    loading1: false,
    loading2: false,
    gallery: true,
    search: '',
    categories: [
      { text: 'All', value: []}
    ],
    SORT_BY: [
				{ text: 'A to Z', value: 'ASC' },
				{ text: 'Z to A', value: 'DESC' },
    ],
    filter: {
      classes: [],
      course_name_sort: '',
      price_min: '',
      price_max: '',
      type_of_learning: ''
    },
    type_of_learning: [
        { text: 'All', value: '' },
        { text: 'E-Learning', value: 'E-LEARNING' },
        { text: 'Blended', value: 'BLENDED' },
        { text: 'Microlearning', value: 'MICROLEARNING' },
    ],
    pageCount: 1,
    paginate: '10',
    page: 1,
  }),
  computed: {
    ...mapState({
      other_courses: (state) => state.other_courses,
      tenant: (state) => state.tenant,
    }),
    ...mapState('usr', {
      classes: (state) => state.classes,
      cart: (state) => state.cart
    }),
  },

  mounted() {
    /* window.addEventListener('resize', this.onResize, {passive: true})
    this.onResize() */
    this.filterOtherCourses()
    this.getClassCategory()
  },

  methods: {
    ...mapActions('usr', ['getClassCategoryAction']),
    ...mapActions(["otherAllCoursesAction"]),

    getClassCategory() {
      this.loading1 = true
      this.getClassCategoryAction().then(res => {
        res.forEach((item) => {
          this.categories.push({
            text: item.class_name,
            value: item.class_code,
          })
        })
      }).finally(() => {
        this.loading1 = false
      })
    },

    filterOtherCourses() {
      this.loading2 = true
      this.otherAllCoursesAction({...this.filter, search: this.search, page: this.page, paginate: Number(this.paginate)}).then((res) => {
        this.pageCount = res.last_page
        this.paginate = String(res.per_page)
        this.page = res.current_page
      }).finally(() => {
        this.loading2 = false
      })
    },

    /* onResize(){
      if(this.$vuetify.breakpoint.smAndDown || this.$vuetify.breakpoint.mobile){
        this.gallery = false
      }
    } */
  },

  watch: {
    filter: {
      handler(val) {
        this.filterOtherCourses()
      },
      deep: true
    },
  }
}
</script>