<template>
  <section id="class-section">
    <v-card outlined>
      <div class="d-flex">
        <Lists/>
        <div class="">
          <v-divider vertical/>
        </div>
        <div class="flex-grow-1 my-5">
          <div class="d-flex justify-end mb-5 mx-5">
            <div class="">
              <v-btn class="text-capitalize" color="success">Create New Class</v-btn>
            </div>
          </div>
          <Table/>
        </div>
      </div>
    </v-card>
  </section>
</template>

<script>
import Lists from '../../components/admin/section/Lists.vue'
import Table from '../../components/admin/section/Table.vue'

export default {
  components: {
    Lists,
    Table,
  },
 
}
</script>