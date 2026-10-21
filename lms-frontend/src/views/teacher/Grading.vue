<template>
  <section id="grading">
    <section class="d-flex flex-wrap align-center justify-space-between">
      <SearchBar 
        :placeholder="'Search course'" 
        :value.sync="search"  
        @clear="clearSearch"
        @search="onSearch"
        class="mb-3"/>

      <FilterMenu
          class="mb-3"
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
                :class="'mb-1'"
              />
          </div>
          <FormSelectObject
            :label="'STATUS'"
            :items="status"
            :value.sync="filter.status"
          />
          <div class="mt-3">
              <FormLabel class="mb-1" :label="'COURSE CONDUCTED DATE (YYYY-MM-DD)'" />
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

    <section class="mb-1 d-flex flex-row justify-space-between">                
        <FormLabel v-if="filter.created_from || filter.created_to" :label="`Courses conducted ${filter.created_from ? `from ${$dateFormat.mmDDyy(filter.created_from)}` : ''} ${filter.created_to ? `to ${$dateFormat.mmDDyy(filter.created_to)}` : ''}`" class="mb-3"/>
        <FormLabel class="ml-auto" :label="`${totalCount} result/s`" v-if="totalCount !== 0"/>
    </section>

    <v-data-table
      :headers="grading_tbl"
      :items="grading"
      class="text--center custom-border poppins f14 mb-3"
      :loading="loading"
      hide-default-footer
      :items-per-page.sync="itemsPerPage">
      
      <template v-slot:item.status="{ item }">
          <span v-if="item" class="text-capitalize"> {{ item.status }}ED </span>
      </template>

      <template v-slot:item.created_at="{ item }">
          <span v-if="item"> {{ $dateFormat.mmDDyy(item.created_at) }} </span>
      </template>
      
      <template v-slot:item.start="{ item }">
          <span v-if="item">{{ item.available_until?.start_date ? $dateFormat.mmDDyy(item.available_until.start_date) : '' }}</span>
      </template>

      <template v-slot:item.end="{ item }">
          <span v-if="item">{{ item.available_until?.end_date ? $dateFormat.mmDDyy(item.available_until.end_date) : '' }}</span>
      </template>

      <template v-slot:item.scorm_count="{ item }">
        <v-btn
          text
          small
          class="primary--text text-capitalize"
          :to="{ name: 'Instructor Grading Course', params: { id: item.id, course_name: item.name, type: 'scorm' }, query: { search: '', page: 1, paginate: 10 } }"
        >
          {{ item.scorm_count }}
        </v-btn>
      </template>
      
      <template v-slot:item.group_activities_count="{ item }">
        <v-btn
          text
          small
          class="primary--text text-capitalize"
          :to="{ name: 'Instructor Grading Course', params: { id: item.id, course_name: item.name, type: 'group_activity' }, query: { search: '', page: 1, paginate: 10 } }"
        >
          {{ item.group_activities_count }}
        </v-btn>
      </template>

      <template v-slot:item.evaluations_count="{ item }">
        <v-btn
          text
          small
          class="primary--text text-capitalize"
          :to="{ name: 'Instructor Grading Course', params: { id: item.id, course_name: item.name, type: 'evaluation' }, query: { search: '', page: 1, paginate: 10 } }"
        >
          {{ item.evaluations_count }}
        </v-btn>
      </template>

      <template v-slot:item.assessments_count="{ item }">
        <v-btn
          text
          small
          class="primary--text text-capitalize"
          :to="{ name: 'Instructor Grading Course', params: { id: item.id, course_name: item.name, type: 'assessment' }, query: { search: '', page: 1, paginate: 10 } }"
        >
          {{ item.assessments_count }}
        </v-btn>
      </template>

      <!-- <template v-slot:item.action="{ item }">
        <v-btn
          text
          small
          class="primary--text text-capitalize"
          :to="{ name: 'Instructor Grading Course', params: { id: item.id, course_name: item.name }, query: { search: '', page: 1, paginate: 10 } }"
        >
          <v-icon left small>mdi-eye-outline</v-icon>
          View
        </v-btn>
      </template> -->
    </v-data-table>
    
                        
    <FormPagination 
      :pageCount="pageCount" 
      :page="page"
      :paginate="paginationPaginate"
      @page="onPageChange" 
      @paginate="onPaginateChange"/>

  </section>
</template>

<script>
  import { mapActions, mapState, mapMutations } from 'vuex'
  import { grading_tbl } from '@/constants/tblheaders/grading'
  import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
  import { type_of_learning } from '@/constants/typeoflearning';

  export default {
    mixins: [searchAndPaginateMixin],
    methods: {
      ...mapMutations(['alertMutation']),
      ...mapActions('instructor', ['getCoursesGrading']),
      ...mapMutations('instructor', ['gradingMutation']),

      resetFilters(){
        this.filter = {
          status: '',
          created_from: ``,
          created_to: ``,
          type_of_learning: ''
        }

        this.page = 1
        this.updateQuery()
      },

      getData(){
        if(!this.loading) {
          this.loading = true
          this.getCoursesGrading({ paginate: Number(this.paginate), page: this.page, search: this.search, ...this.filter }).then((res) => {
            this.pageCount = res.last_page
            this.paginate = String(res.per_page)
            this.page = res.current_page
            this.totalCount = res.total
            this.loading = false
          }).catch(() => {
            this.loading = false
            this.alertMutation({
                show: true,
                text: 'Something went wrong',
                type: "error"
            })
          })
        }
      }
    },
    computed: {
      ...mapState('instructor', {
        grading: (state) => state.grading,
      }),

      paginationPaginate(){
        return String(this.paginate)
      },

      itemsPerPage(){
        return Number(this.paginate)
      }
    },
    created(){
      this.gradingMutation([])
    },
    mounted(){
      this.gradingMutation([])
      
      this.minDate = this.filter.created_from
      this.maxDate = this.filter.created_to
    },
    data: () => ({
      grading_tbl,
      loading: false,
      totalCount: 0,
      pageCount: 1,
      minDate: '',
      maxDate: '',
      status: [
        { text: 'ALL', value: '' },
        { text: 'PUBLISHED', value: 'PUBLISH' },
        { text: 'UNPUBLISHED', value: 'UNPUBLISH' },
      ],
      type_of_learning
    }),

  };
</script>