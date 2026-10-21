<template>
  <v-dialog v-model="dialog" persistent max-width="550">
    <v-card >
      <v-card-text class="poppins f18 fw600 d-flex align-center pt-3">
        <div class="primary--text">
          Enroll Learners
        </div>
        <v-spacer/>
        <v-btn icon @click="cancel">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-text>
      <v-card-text>
        <label class="caption">COURSE</label>
        <v-select 
          outlined 
          dense 
          :items="courses"
          v-model="course_id"
          item-text="title"
          item-value="id"
          hide-details="auto"
          :disabled="courses.length === 0"
          :loading="loading"
          class="mb-3"
        >
          <template slot="item" slot-scope="data">
            <v-list-item-content>
              <v-list-item-title class="roboto f14 fw500" >
                <div class="roboto f10 secondary-2--text text-uppercase">
                  {{  data.item.type_of_learning }} &nbsp; | &nbsp; {{ data.item.is_free ? 'FREE' : `PHP ${data.item.price ? data.item.price : 0}` }}
                  <span v-if="data.item.available_until">
                      &nbsp; | &nbsp; 
                      <span v-if="data.item.available_until.start_date"> {{ $dateFormat.mmDDyy(data.item.available_until.start_date) }} </span>
                      <span v-if="data.item.available_until.end_date"> until {{ $dateFormat.mmDDyy(data.item.available_until.end_date) }} </span>
                  </span>
                </div>
                {{  data.item.title }}
              </v-list-item-title>
            </v-list-item-content>
          </template>
          <template slot="selection" slot-scope="data" >
            <div class="roboto f14 fw500 my-1">
              <div class="roboto f10">
                {{  data.item.type_of_learning }} &nbsp; | &nbsp; {{ data.item.is_free ? 'FREE' : `PHP ${data.item.price ? data.item.price : 0}` }}
              </div>
              {{  data.item.title }}
            </div>
          </template>
        </v-select>
        <Bulk @onbulk="onbulk" :dialog="dialog"/>
        <div class="mx-5">
          <div class="" v-for="(err, i) in errorList" :key="i">
            <div class="d-flex align-start">
              <v-icon color="danger-1" size="18" class="mr-2">mdi-alert-circle</v-icon>
              <div class="f12">{{err[0]}}</div>
            </div>
          </div>
          <div class="d-flex flex-wrap justify-space-around align-center">
            <div class="">
              <v-btn dense small text color="primary" target="_blank" :href="`${base_path.replace('/v1', '')}/enroll_users_template.csv`" class="poppins text-capitalize fw600 cursor-pointer" download>
                <v-icon left size="18">mdi-download</v-icon>
                Bulk-enroll template
              </v-btn>
            </div>
          </div>
        </div>
      </v-card-text>
      <v-divider/>
      <v-card-actions class="d-flex justify-end align-center py-3 px-5">
        <ButtonPrimary 
          @click="submit"
          :label="'Enroll'"
          :loading="saving"
          :disabled="!file || !course_id"
        />
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style>
.cursor-pointer {
  cursor: pointer;
}
</style>

<script>
import { mapActions, mapState, mapMutations } from 'vuex'
import Bulk from './Bulk.vue'

export default {
  props: ['dialog'],
  components: { 
    Bulk,
  },
  computed: {
    ...mapState({
      errors: (state) => state.errors,
      courses: (state) => state.courses
    }),

  },
  data: () => ({
    base_path: `${process.env.VUE_APP_API}`,
    loading: false,
    saving: false,
    form: new FormData(),
    course_id: null,
    file: null,
    errorList: [],
    userLength: 0,
    disable: false
  }),
  mounted() {
  },
  methods: {
    ...mapMutations(['alertMutation', 'errorsMutation']),

    ...mapActions([
      'getTenantCoursesAction'
    ]),

    ...mapActions('admin', [
      'importEnrolleessAction'
    ]),

    submit() {
      this.errorList = []
      this.form.set('course_id', this.course_id)
      this.saving = true
      this.importEnrolleessAction(this.form).then(() => {
        this.errorList = []
        this.reset()
        this.$emit('close')
        this.$emit('success')
        this.saving = false
      }).catch(e => {
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
        this.saving = false
      })
    },

    onbulk(e) {
      this.reset()
      this.form.set('file', e)
      this.file = e

      if(e) {
        this.disable = false
      } else {
        this.disable = true
      }
    },

    cancel() {
      this.reset()
      this.$emit('close')
    },

    reset() {
      this.errorList = []
    }
  },

  watch: {
    
    dialog(val) {
      if(val){
        this.errorsMutation([])
        this.course_id = null
        this.file = null
        this.form.set('file', null)
        this.form.set('course_id', null)
        this.loading = true
        this.getTenantCoursesAction().then(() => {
          this.loading = false
        }).catch(() => {
          this.loading = false
        })
      }
    }
  }
}
</script>
