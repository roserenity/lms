<template>
  <section id="courses">
    <SearchHeader/>
    <v-sheet>
      <div class="d-flex justify-space-between align-center pa-5">
        <div class="nunito grey--text">
          120 published courses
        </div>
        <div class="d-flex align-center">
          <v-btn :color="grid? 'primary':''" @click="(grid=true,list=false)" icon>
            <v-icon>mdi-view-grid</v-icon>
          </v-btn>
          <v-btn :color="list? 'primary':''" @click="(grid=false,list=true)" icon class="ml-2">
            <v-icon>mdi-format-list-numbered</v-icon>
          </v-btn>
        </div>
      </div>
      <v-sheet max-height="600" class="pb-10 overflow-y-auto scroller overflow-hidden" :class="list?'':'px-5 mx-1'">
        <v-row v-if="grid">
          <v-col cols="12" lg="3" v-for="n in 10" :key="n">
            <Card :i="n"/>
          </v-col>
        </v-row>
        <Table v-if="list"/>
      </v-sheet>
    </v-sheet>
  </section>
</template>

<script>
import SearchHeader from '../../components/admin/course/SearchHeader.vue'
import Card from '../../components/admin/course/Card.vue'
import Table from '../../components/admin/course/Table.vue'

export default {
  components: {
    SearchHeader,
    Card,
    Table
  },
  data: () => ({
    grid: true,
    list: false
  })
}
</script>