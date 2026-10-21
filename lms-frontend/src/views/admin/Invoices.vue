<template>
  <section id="invoices">
    <v-tabs color="#999" class="tabs" v-model="tab" background-color="transparent">
      <v-tab v-for="item in items" :key="item" active-class="active" class="poppins text-uppercase black--text">
        {{item}}
      </v-tab>
    </v-tabs>
    <SearchHeader/>
    <v-tabs-items v-model="tab">
      <v-card-item v-for="item in items" :key="item">
        <v-sheet>
          <Table/>
        </v-sheet>
      </v-card-item>
    </v-tabs-items>
  </section>
</template>

<script>
import SearchHeader from '../../components/admin/invoice/SearchHeader.vue'
import Table from '../../components/admin/invoice/Table.vue'

export default {
  components: {
    SearchHeader,
    Table
  },
  data: () => ({
    tab: null,
    items: ['Paid', 'Unpaid']
  })
}
</script>