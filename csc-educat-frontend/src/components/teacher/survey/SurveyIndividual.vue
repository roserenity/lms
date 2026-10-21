<template>
  <section>
    <section class="d-flex flex-wrap align-center justify-space-between">
      <SearchBar 
        :placeholder="'Search student'" 
        :value.sync="search" 
        @search="$emit('search', search)" 
        @clear="() => {search = '', $emit('search', search)}" 
        class="mb-5"/>
      <section class="d-flex flex-row ml-auto">
        <v-btn
            outlined
            color="primary"
            class="poppins f12 fw500 text-capitalize mx-1 mb-5"
            @click="markAsCompleted"
            dense
            :loading="loading1"
        >
            Mark as Completed
        </v-btn>
        <v-btn
            outlined
            color="primary"
            class="poppins f12 fw500 text-capitalize mx-1 mb-5"
            @click="markAsPending"
            dense
            :loading="loading2"
        >
            Mark as Pending
        </v-btn>
      </section>
    </section>

    <v-data-table
      :loading="loading"
      :headers="grading_survey_tbl"
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
        <template v-slot:item.status="{ item }">
            <v-alert v-if="item.status !== -1 && item.status" dense :color="item.status === 1 ? '#7BC14533' : '#BDBDBD33'" class="ma-auto" :class="item.status === 1 ? 'success--text' : 'secondary-2--text'">
                {{ item.status ? 'CHECKED' : 'PENDING' }}
            </v-alert>
        </template>
        <template v-slot:item.date_submitted="{ item }">
          <span>
            {{ item.date_submitted !== null ? $dateFormat.mmDDyy(item.date_submitted) : ''}}
          </span>
        </template>
        <template v-slot:item.action="{ item }">
          <v-btn text class="text-capitalize primary--text f11 fw600" :to="{ name: 'Instructor Survey Student', params: { type: $route.params.type, id: $route.params.id, survey_id: $route.params.survey_id, student_id: item.id}}">
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
import { grading_survey_tbl } from '../../../constants/tblheaders/grading';
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

        markAsCompleted(){
          this.loading1 = true
          this.updateStatus({
            id: this.$route.params.survey_id,
            student_evaluation_id: this.selected,
            status: true
          })
        },

        markAsPending(){
          this.loading2 = true
          this.updateStatus({
            id: this.$route.params.survey_id,
            student_evaluation_id: this.selected,
            status: false
          })
        },

        updateStatus(e){
          if(this.$route.params.type === 'course_evaluation'){
            this.updateGradingCourseEvaluationStatusAction(e).then(() => {
              this.alertMutation({
                show: true,
                text: `Successfully updated evaluation status`,
                type: "success"
              })
              this.$emit('getData')
              this.loading1 = false
              this.loading2 = false
              this.selected = []
            }).catch(() => {
              this.alertMutation({
                show: true,
                text: `Something went wrong.`,
                type: "error"
              })
              this.loading1 = false
              this.loading2 = false
            })
          } else {
            this.updateGradingModuleEvaluationStatusAction(e).then(() => {
              this.alertMutation({
                show: true,
                text: `Successfully updated evaluation status`,
                type: "success"
              })
              this.$emit('getData')
              this.loading1 = false
              this.loading2 = false
              this.selected = []
            }).catch(() => {
              this.alertMutation({
                show: true,
                text: `Something went wrong.`,
                type: "error"
              })
              this.loading1 = false
              this.loading2 = false
            })
          }
        }
    },
    data: () => ({
      grading_survey_tbl,
      users: [],
      search: '',
      loading1: false,
      loading2: false,
      selected: [],
      selectAll: false,
    }),
    watch: {
      selectAll(val) {
        if(val) {
          let _selected = []

          this.learners.forEach(item => {
            if(!this.selected.includes(item)) {
              
              _selected.push(item[`student_${this.$route.params.type}_id`])
            }
          })

          this.selected = [..._selected]

        } else if (!val && this.learners.length === this.selected.length) {
          this.selected = [] 
        }
      },

      selected(val){
        if(val.length !== this.learners.length) {
          this.selectAll = false
        } else if (val.length === this.learners.length && this.learners.length !== 0) {
          this.selectAll = true
        }
      }

    },
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
