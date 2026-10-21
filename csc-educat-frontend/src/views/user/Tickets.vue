<template>
  <section id="ticket poppins">
    <v-sheet
        color="transparent"
        class="d-flex flex-column align-center justify-center pb-10 rounded-lg fade"
        height="80vh">
        <div class="">
        <v-img 
            max-width="300" 
            :src="require('@/assets/default/empty_published.png')" />
        </div>
        <h3 class="poppins secondary-1--text fw600 my-5">Reminder!</h3>
        <div class="text-center poppins secondary-1--text f12">
          For any issues or concerns, kindly submit your ticket via email to <b class="primary--text">hello@example.com</b>.
        </div>
        <div class="text-center poppins secondary-1--text f12">
          Ensure you include all relevant details, such as the nature of the issue, screenshots (if applicable), and your contact information, to facilitate prompt resolution.
        </div>
        <div class="text-center poppins secondary-1--text f12">
          Thank you for your cooperation!
        </div>
    </v-sheet>
    <!-- <v-tabs class="tabs" color="primary" v-model="tab" background-color="transparent">
      <v-tab v-for="item in items" 
      :key="item" 
      active-class="active"
      class="item poppins secondary-2--text text-capitalize">
        {{item}}
      </v-tab>
    </v-tabs>
    <v-tabs-items v-model="tab" style="background-color: transparent">
      <v-tab-item v-for="item in items" :key="item">
          <TicketForm v-if="item === 'Submit a ticket'" class="px-3" submit="submit"/>
          <TicketList v-if="item === 'My Tickets'" :tickets="tickets" :tab="tab"/>
      </v-tab-item>
    </v-tabs-items> -->
  </section>
</template>


<script>
import header from '@/constants/tabs/tickets/ticket'
import { mapActions, mapMutations, mapState } from 'vuex'
import TicketForm from '@/components/user/ticket/TicketForm.vue'
import TicketList from '@/components/user/ticket/TicketList.vue';

export default {
  components: {
    TicketForm,
    TicketList
  },
    computed: {
      ...mapState('usr', {
        tickets: (state) => state.tickets,
      }),
    },
  
  methods: {
      ...mapActions('usr', ['getTicketsAction', 'submitTicketAction']),
      ...mapMutations(['alertMutation']),
  },
  data: () => ({
      tab: 0,
      items: header
  }),

  mounted(){
    if(this.$route.query.ticket_uuid) {
      this.tab = 1
    }
  }
}
</script>