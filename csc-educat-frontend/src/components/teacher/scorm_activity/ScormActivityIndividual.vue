<template>
    <section>
      <section class="d-flex flex-wrap align-center justify-space-between">
        <SearchBar 
          :placeholder="'Search student'" 
          :value.sync="search" 
          @search="$emit('search', search)" 
          @clear="() => {search = '', $emit('search', search)}" 
          class="mb-5"/>
      </section>
  
      <v-data-table
        :loading="loading"
        :headers="grading_scorm_activities_tbl"
        hide-default-footer
        :items="learners"
        :items-per-page.sync="itemsPerPage"
        class="poppins custom-border">
          <template v-slot:header.select="{ item }" >
            <v-checkbox  class="mt-0" v-model="selectAll" hide-details/>
          </template>
          <template v-slot:item.select="{ item }" >
            <v-checkbox  class="mt-0" v-model="selected" :value="item[`student_${$route.params.type}_id`]" hide-details/>
          </template>
          <template v-slot:item.passed="{ item }">
            <v-alert dense v-if="item.passed" small :color="item.passed ? '#7BC14533' : '#E49E3233'" class="ma-auto" :class="item.passed ? 'success--text' : 'error--text'">
                {{ item.passed ? 'PASSED' : 'FAILED' }}
            </v-alert>
          </template>
          <template v-slot:item.created_at="{ item }">
            <span>
              {{ item.created_at !== null ? $dateFormat.mmDDyy(item.created_at) : ''}}
            </span>
          </template>
          <template v-slot:item.action="{ item }">
            <v-btn text class="text-capitalize primary--text f11 fw600" :to="{ name: 'Instructor Scorm Activity Student', params: { id: $route.params.id, scorm_activity_id: $route.params.scorm_activity_id, user_id: item.user_id}}">
              <v-icon left>mdi-eye</v-icon>
              View
            </v-btn>
          </template>
        </v-data-table>
        
        <FormPagination 
          :pageCount="pageCount" 
          :page="page"
          :paginate="paginationPaginate"
          @page="(e) => $emit('page', e)" 
          @paginate="(e) => $emit('paginate', e)"/>
    </section>
  </template>
  
  <script>
  import { mapMutations, mapState, mapActions } from 'vuex';
  import { grading_scorm_activities_tbl } from '../../../constants/tblheaders/grading';
  export default {
      props: {
        pageCount: {
          type: Number,
          default: 1
        },
        loading: {
          type: Boolean,
          default: false,
        },
        page: {
          type: Number,
          default: 1
        },
        paginate: {
          type: String,
          default: '10'
        },
        learners: {
          type: Array,
          default: []
        }
      },
      methods: {
          ...mapMutations(['alertMutation']),
          ...mapActions('instructor', ['updateGradingCourseEvaluationStatusAction', 'updateGradingModuleEvaluationStatusAction']),
      },
      data: () => ({
        grading_scorm_activities_tbl,
        users: [],
        search: '',
        loading1: false,
        loading2: false,
        selected: [],
        selectAll: false,
      }),
      computed: {
  
          itemsPerPage(){
            return Number(this.paginate)
          },
  
          paginationPaginate(){
              return String(this.paginate)
          }
      }
  }
  </script>
  