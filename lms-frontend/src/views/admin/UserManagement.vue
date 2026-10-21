<template>
  <section id="user-management">
    <Table 
      :loading="loading" 
      :filterLoading="filterLoading" 
      :page="page"
      :pageCount="pageCount"
      :filter="filter"
      :otherFilters="otherFilters"
      :totalCount="totalCount"
      :paginate="paginate"
      :search="search"
      @updateSearch="(e) => { search = e }"
      @onSearch="onSearch"
      @paginate="onPaginateChange" 
      @page="onPageChange" 
      @getUsers="getData"
      @updateQuery="updateQuery"
      @resetFilters="resetFilters"
      :users="users"/>
  </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex'
import Table from '../../components/admin/user/Table.vue'
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';

export default {
  mixins: [searchAndPaginateMixin],
  components: { Table },
  data: () => ({
    loading: false,
    filterLoading: false,
    pageCount: 1,
    totalCount: 0,
    users: [],
    prevRoute: null,
    otherFilters: []
  }),
  computed: {
    ...mapState('admin', {
      userState: (state) => state.users,
      filteredUsers: (state) => state.filtered_users,
    })
  },
  created() {
    //this.getData()
    this.getRegistrationFields()
    this.getClassesAction()
  },
  methods: {
    ...mapActions('admin', [
      'getUsersAction',
      'getRegistrationTablesAction',
      'getRegistrationFieldsWithOptionsAction',
      'getClassesAction'
    ]),
    ...mapMutations(['alertMutation']),

    getRegistrationFields(){
      this.filterLoading = true
      this.getRegistrationFieldsWithOptionsAction().then(res => {
        let result = res.reduce((acc, item) => {
            acc[item.input_field_name] = "";
            return acc;
        }, {});
        this.filter = { ...this.filter, ...result }
        this.otherFilters = res
        this.updateQuery()
        this.filterLoading = false
        this.getData()
      }).catch(() => {
        this.filterLoading = false
        this.getData()
      })
    },

    resetFilters() {
      this.filter = {
        role: '',
        class_category_id: [],
        course_id: [],
        status: '',
        created_at: ''
      }
      // this.$router.replace({ query: { page: this.$route.query.page, paginate: this.$route.query.paginate, timestamp: Date.now() } })
      this.updateQuery()
    },

    getData(){
      if(!this.loading) {
        this.loading = true
        this.getUsersAction({
          page: this.page, 
          paginate: Number(this.paginate),
          
          search: this.search,
          ...this.filter
        }).then(res => {
          this.users = res.data
          this.pageCount = res.last_page
          this.loading = false
          this.totalCount = res.total
        }).catch(() => {
          this.loading = false
          this.users = 0
          this.pageCount = 0
          this.totalCount = 0
          this.alertMutation({
            show: true,
            text: 'Something went wrong',
            type: "error"
          })
        })
      }
    }
  },
  
  beforeRouteEnter(to, from, next) {
      next(vm => {
          vm.prevRoute = from 
      })
  },
}
</script>