<template>
  <section id="class-category">
    <Table 
      :totalCount="totalCount"
      :pageCount="pageCount" 
      :classes="classes" 
      :page="page"
      :paginate="paginate"
      :search="search"
      @clear="(e) => searchClass('')"
      @search="(e) => searchClass(e)"
      @paginate="(e) => paginate = e" 
      @page="(e) => page = e" 
      @save="saveClass" 
      :loading="loading"/>
  </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex'
import Table from '../../components/admin/course/ClassTable.vue'

export default {
  components: { Table },
  data: () => ({
    toast: false,
    text: '',
    loading: false,
    classes: [],
    pageCount: 1,
    page: 1,
    paginate: '10',
    totalCount: 0,
    search: ''
  }),
  computed: {
    ...mapState({
      errors: (state) => state.errors,
    })
  },
  created() {
    if(Object.values(this.$route.query).length == 0){
      this.$router.replace({ query: { page: 1, paginate: Number(this.paginate), timestamp: Date.now() } });
    } else {
      let { page, paginate } = this.$route.query

      if (page) {
        this.page = Number(page)
      } 
      if (paginate) {
        this.paginate = paginate
      }
    }
    this.getClasses()
  },
  methods: {
    ...mapActions('admin', ['getClassesAction']),
    ...mapMutations(['alertMutation']),

    getClasses(){
      this.loading = true
      this.getClassesAction({page: this.page, paginate: Number(this.paginate), search: this.search}).then(res => {
        this.loading=false
        this.classes = res.data
        this.pageCount = res.last_page
        this.totalCount = res.total
      }).catch(() => {
        this.loading = false
        this.classes = []
        this.pageCount = 0
        this.totalCount = 0
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      })
    },

    saveClass() {
      this.alertMutation({
          show: true,
          text: 'Successfully added a new class',
          type: "success"
      })
      this.getClasses()
    },

    searchClass(val){
      this.search = val

      if(this.page !== 1) {
        this.page = 1
      } else {
        this.getClasses()
      }
    }
  },
  watch:{
    page(val){
      this.$router.replace({ query: { ...this.$route.query, page: val, timestamp: Date.now() } });
      this.getClasses()
    },
    paginate(val){
      this.page = 1
      this.$router.replace({ query: { ...this.$route.query, paginate: val, timestamp: Date.now() } });
      this.getClasses()
    }
  }
}
</script>
