<template>
  <section id="enrollees">
    <SearchHeader/>    
    <Table/>    
  </section>
</template>

<script>
import SearchHeader from '../../components/admin/enrollee/SearchHeader.vue'
import Table from '../../components/admin/enrollee/Table.vue'

export default {
  components: {
    SearchHeader,
    Table
  },
  
}
</script>