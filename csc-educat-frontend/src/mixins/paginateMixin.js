export default {
    data: () => ({
      page: 1,
      paginate: '10',
      prevRoute: null
    }),
    watch: {
      '$route.query': {
        handler(newQuery) {
          this.page = newQuery.page ? Number(newQuery.page) : 1;
          this.paginate = newQuery.paginate || '10';
          this.getData();
        },
        immediate: true,
        deep: true
      }
    },
    methods: {
      updateQuery() {
        this.$router.replace({ query: { search: this.search, page: this.page, paginate: this.paginate, timestamp: Date.now() } });
      },
      onSearch() {
        this.page = 1
        this.updateQuery()
      },
      clearSearch() {
        this.search = '';
        this.page = 1
        this.updateQuery()
      },
      onPageChange(newPage) {
        this.page = newPage;
        this.updateQuery();
      },
      onPaginateChange(newPaginate) {
        if (this.paginate !== newPaginate) {
          this.paginate = newPaginate;
          this.page = 1;
          this.updateQuery();
        }
      }
    },
    mounted() {
      this.search = this.$route.query.search || '';
      this.page = this.$route.query.page ? Number(this.$route.query.page) : 1;
      this.paginate = this.$route.query.paginate || '10';

      this.updateQuery()
    },
  
    beforeRouteEnter(to, from, next) {
        next(vm => {
            vm.prevRoute = from       
        })
    },
  };
  