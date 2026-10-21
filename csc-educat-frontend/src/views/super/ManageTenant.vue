<template>
  <section id="manage-tenant">
    <breadcrumbs :items="links"/>
    <v-sheet outlined class="pa-5 my-5">
      <div class="d-flex nunito">
        <div class="flex-grow-1">
          <div class="d-flex align-start">
            <v-icon size="120" color="primary">mdi-account-box-outline</v-icon>
            <div class="ml-5">
              <h2 class="">Juan Dela Cruz</h2>
              <div class="">juan@mail.com</div>
              <i class="">+63912345678</i>
              <div class="mt-5">
                <!-- <v-btn small outlined color="danger">
                  Terminate Tenant
                </v-btn> -->
              </div>
            </div>
          </div>
        </div>
        <v-divider vertical class="mx-5"/>
        <div class="flex-grow-1">
          <div class="font-weight-bold">
            ID: <span class=" ml-3 secondary-3--text">demo_tenant</span>
          </div>
          <div class="font-weight-bold">
            URL: <span class=" ml-3 secondary-3--text">https://lms.example.com</span>
          </div>
          <div class="font-weight-bold">
            API: <span class=" ml-3 secondary-3--text">https://api.lms.example.com</span>
          </div>
           <div class="font-weight-bold">
            STATUS: <span class=" ml-3 success--text">Running</span>
          </div>
        </div>
      </div>
    </v-sheet>
    <!-- <div class="">Summary</div> -->
    <v-row justify="start">
      <v-col cols="12" md="3" v-for="(item, i) in cards" :key="i">
        <Card :item="item"/>
      </v-col>
    </v-row>
    <v-sheet>
      <!-- <Info/>
      <v-tabs color="#999" class="tabs" v-model="tab" background-color="transparent">
        <v-tab v-for="item in items" :key="item" active-class="active" class="poppins text-uppercase black--text d-flex justify-start">
          {{item}}
        </v-tab>
      </v-tabs>
      <v-tabs-items v-model="tab">
        <div v-for="item in items" :key="item">

        </div>
      </v-tabs-items> -->
    </v-sheet>

  </section>
</template>

<script>
import Info from '../../components/super/tenant/Information.vue'
import Card from '../../components/reusable/TopCard.vue'

export default {
  components: {
    Info,
    Card
  },
  data: () => ({
    tab: null,
    items: [
      'Services',
      'Billing'
    ],
    cards: [
      {
        title: 'TOTAL COURSES',
        count: 7,
        icon: 'mdi-book-multiple',
        color: '#F2994A'
      },
      {
        title: 'TOTAL USERS',
        count: 995,
        icon: 'mdi-account-multiple',
        color: '#FEDA74'
      },
      {
        title: 'TOTAL TEACHERS',
        count: 25,
        icon: 'mdi-human-male-board-poll',
        color: '#2D9CDB'
      },
    ],
    links: [
      {
        text: 'Tenants',
        to: { name: 'Super Admin Tenants' },
        disabled: false
      },
      {
        text: 'tenant_1201',
        to: '',
        disabled: true
      },
    ]
  }),
}
</script>