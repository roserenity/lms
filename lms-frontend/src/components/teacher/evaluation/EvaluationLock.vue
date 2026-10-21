<template>
  <v-dialog  persistent v-model="dialog" max-width="400">
    <v-card v-if="evaluation">
      <v-card-title class="d-flex align-center justify-space-between">
        <h4 class="primary--text poppins fw600">Lock Evaluation</h4>
        <v-btn icon @click="$emit('close')">
          <v-icon size="18">mdi-close</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text>
        <v-checkbox v-model="validity" value="anytime" label="Accessible anytime" class="roboto fw500" hide-details/>
        <v-checkbox v-model="validity" value="date" label="Availablity dates" class="roboto fw500" />

        <FormDatePicker 
            :placeholder="'Available from'"
            :val="available_from_date"
            :max="available_to_date"
            class_="col-11 mb-3"
            @apply="(e) => {
                available_from_date = e
            }"/>

        <FormDatePicker 
            :placeholder="'Available to'"
            :val="available_to_date"
            :min="available_from_date"
            class_="col-11 mb-3"
            @apply="(e) => {
                available_to_date = e
            }"/>
        
        <div class="font-italic f14 roboto secondary--text" v-if="dateFormattedAvailableFrom || dateFormattedAvailableTo">
          This evaluation will be accessible
          <template v-if="dateFormattedAvailableFrom"> from {{ dateFormattedAvailableFrom }}</template>
          <template v-if="dateFormattedAvailableTo"> until {{ dateFormattedAvailableTo }}</template>
        </div>

      </v-card-text>
      <v-divider/>
      <v-card-actions class="d-flex align-center justify-end">
        <v-btn class="text-capitalize poppins" text @click="$emit('close')">Cancel</v-btn>
        <v-btn class="text-capitalize poppins" color="primary" v-if="!saving" @click="save">Save Changes</v-btn>
        <v-btn class="text-capitalize poppins" color="primary" v-else >Saving...</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import moment from 'moment'
import { mapActions, mapMutations, mapState } from 'vuex'

export default {
  props: ['dialog', 'evaluation'],

  data: () => ({
    available_from_date: '',
    available_to_date: '',
    menu1: false,
    menu2: false,
    available_from: null,
    available_to: null,
    saving: false,
    validity: 'anytime',
    availability_date: null,
  }),

  computed: {

    dateFormattedAvailableFrom() {
      return this.available_from_date ? moment(this.available_from_date).format('LL') : ''
    },

    dateFormattedAvailableTo() {
      return this.available_to_date ? moment(this.available_to_date).format('LL') : ''
    },

  },
  methods: {
      ...mapActions('instructor', ['courseEvaluationValidityAction', 'moduleEvaluationValidityAction', 'getCourseEvaluationsAction', 'getModuleEvaluationsAction']),
    ...mapMutations(['alertMutation']),

    save() {
      this.saving=true
      

      if(this.evaluation.module_id) {
        this.moduleEvaluationValidityAction({
          id: this.evaluation.id,
          module_id: this.evaluation.module_id,
          date: this.date === null ? 'anytime' : this.date,
          validity: this.validity,
          available_from: this.validity !== 'date' ? null : (this.available_from_date != '' ? this.available_from_date : null),
          available_to: this.validity !== 'date' ? null : (this.available_to_date != '' ? this.available_to_date : null)
        }).then(() => {
          this.getModuleEvaluationsAction(this.evaluation.module_id).then(() => {
            this.$emit('close')
            this.alertMutation({
              show: true,
              text: 'Module lock settings updated successfully',
              type: "success"
            })
            this.saving=false
          }).catch(() => {
            this.saving=false
            this.alertMutation({
              show: true,
              text: 'Something went wrong',
              type: "error"
            })
          })
        })
        .catch(() => {
          this.saving=false
          this.alertMutation({
            show: true,
            text: 'Something went wrong',
            type: "error"
          })
        })
      }
      if(this.evaluation.course_id) {
        this.courseEvaluationValidityAction({
          id: this.evaluation.id,
          course_id: this.evaluation.course_id,
          date: this.date === null ? 'anytime' : this.date,
          validity: this.validity,
          available_from: this.validity !== 'date' ? null : (this.available_from_date != '' ? this.available_from_date : null),
          available_to: this.validity !== 'date' ? null : (this.available_to_date != '' ? this.available_to_date : null)
        }).then(() => {
          this.getCourseEvaluationsAction(this.evaluation.course_id).then(() => {
            this.$emit('close')
            this.alertMutation({
              show: true,
              text: 'Module lock settings updated successfully',
              type: "success"
            })
            this.saving=false
          }).catch(() => {
            this.saving=false
            this.alertMutation({
              show: true,
              text: 'Something went wrong',
              type: "error"
            })
          })
          
        })
        .catch(() => {
          this.saving=false
        })
      }
    }
  },
  watch: {

    validity(val) {
      if(!val) {
        this.validity = 'anytime'
        this.available_from_date='',
        this.available_to_date=''
      } else if(val == 'anytime') {
        this.available_from_date='',
        this.available_to_date=''
      }
    },

    dialog(val) {
      if(val == true) {
        this.validity=this.evaluation.validity, 
        this.available_from_date=this.evaluation.available_from ? this.evaluation.available_from : '',
        this.available_to_date=this.evaluation.available_to ? this.evaluation.available_to : ''
      }
    }
  }
}
</script>