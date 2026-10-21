 <template>
   <section id="ticket" class="my-10">
     <v-row justify="center" align="center">
      <v-col cols="12" xl="8">
        <v-tabs class="tabs" color="success" v-model="tab" background-color="transparent">
          <v-tab v-for="item in items" 
          :key="item" 
          active-class="active"
          class="item poppins text-capitalize">
            {{item}}
          </v-tab>
        </v-tabs>
        <v-divider/>
        <v-tabs-items v-model="tab">
          <v-tab-item v-for="item in items" :key="item" >
            <v-card flat>
              <TicketForm v-if="item === 'Submit a ticket'"/>
            </v-card>
          </v-tab-item>
        </v-tabs-items>
       </v-col>
    </v-row>
   </section>
 </template>
 
 <script>
  import tabs from '../../constants/tabs/tickets/ticket'
  import TicketForm from '../../components/teacher/ticket/TicketForm.vue'

 export default {
  components: {
    TicketForm
  },
  data: () => ({
    tab: null,
    items: tabs,
  })
 }
 </script>