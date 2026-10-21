<template>
  <v-dialog  persistent v-model="dialog" max-width="400">
    <v-card v-if="module">
      <v-card-title class="d-flex align-center justify-space-between">
        <h4 class="primary--text poppins fw600">Lock Module</h4>
        <v-btn icon @click="$emit('close')">
          <v-icon size="18">mdi-close</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text>
        <v-checkbox v-model="validity" value="anytime" label="Accessible anytime" class="roboto fw500" hide-details/>
        <v-checkbox v-if="index > 0 || validity === 'lock'" v-model="validity" value="lock" label="Lock module if preceding module is incomplete." class="roboto fw500" hide-details/>
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
          This module will be accessible
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
  props: ['dialog', 'module'],

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
    ...mapState('instructor', {
      'modules': (state) => state.modules
    }),

    dateFormattedAvailableFrom() {
      return this.available_from_date ? moment(this.available_from_date).format('LL') : ''
    },

    dateFormattedAvailableTo() {
      return this.available_to_date ? moment(this.available_to_date).format('LL') : ''
    },

    index() {
      if(module) {
        return this.modules.findIndex(item => item.id === this.module.id)
      }

      return -1
    }
  },
  methods: {
    ...mapActions('instructor', ['moduleValidityAction']),
    ...mapMutations(['alertMutation']),

    save() {
      this.saving=true
      this.moduleValidityAction({
        id: this.module.id,
        course_id: this.module.course_id,
        validity: this.validity,
        available_from: this.validity !== 'date' ? null : (this.available_from_date != '' ? this.available_from_date : null),
        available_to: this.validity !== 'date' ? null : (this.available_to_date != '' ? this.available_to_date : null),
      }).then(() => {
        this.$emit('close')
        this.$emit('success')
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
        this.validity=this.module.validity, 
        this.available_from_date=this.module.available_from ? this.module.available_from : '',
        this.available_to_date=this.module.available_to ? this.module.available_to : ''
      }
    }
  }
}
</script>