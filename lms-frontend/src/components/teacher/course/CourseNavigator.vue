<template>
  <v-sheet class="custom-border border hidden-sm-and-down">
    <section class="px-5 py-3">
      <FormLabel :label="'Course Navigator'" />
    </section>
    
    <v-divider/>
    <div class=" px-5 py-2">
      <v-sheet outlined v-for="(item, i) in modules" :key="i" class="px-3 py-2 my-2" color="secondary-5" rounded>
        <div class="poppins f12 fw600 secondary-3--text">
          {{item.title ? item.title : 'Untitled'}}
        </div>
        <v-list-item-subtitle class="f10 poppins">
          {{ item.description }}
        </v-list-item-subtitle>
        <div class="f10 secondary-2--text mt-1 " v-if="item.module_is_locked">
          <div class="font-italic" v-if="dateFormattedAvailableFrom(item.available_from) || dateFormattedAvailableTo(item.available_to)">
            This module will be accessible
            <template v-if="dateFormattedAvailableFrom(item.available_from)"> from {{ dateFormattedAvailableFrom(item.available_from) }}</template>
            <template v-if="dateFormattedAvailableTo(item.available_to)"> until {{ dateFormattedAvailableTo(item.available_to) }}</template>
          </div>
        </div>
        <div class="f10 secondary-2--text mt-1 " v-if="item.valid_until === 'lock' && i > 0">
            This module will be accessible on after {{ modules[i -1].title }} is completed
        </div>
        <div class="f10 error--text mt-1 " v-if="item.valid_until === 'lock' && i === 0">
            Please update the module validity / lock
        </div>
      </v-sheet>
    </div>
  </v-sheet>
</template>

<script>
import moment from 'moment'

export default {
  props: ['modules'],

  methods: {
    dateFormattedAvailableFrom(date) {
      return date ? moment(date).format('LL') : ''
    },

    dateFormattedAvailableTo(date) {
      return date ? moment(date).format('LL') : ''
    },
  }
}
</script>