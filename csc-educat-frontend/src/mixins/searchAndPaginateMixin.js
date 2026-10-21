export default {
  data: () => ({
    search: '',
    page: 1,
    paginate: '10',
    filter: {},
    prevRoute: null
  }),
  watch: {
    '$route.query': {
      handler(newQuery) {
        this.search = newQuery.search || '';
        this.page = newQuery.page ? Number(newQuery.page) : 1;
        this.paginate = newQuery.paginate || '10';

        this.filter = {};
        Object.keys(newQuery).forEach(key => {
          if (!['page', 'paginate', 'search', 'timestamp'].includes(key)) {
              this.filter[key] = newQuery[key];
            }
        });
        this.getData();
      },
      immediate: true,
      deep: true
    }
  },
  methods: {
    updateQuery() {
      this.$router.replace({ query: { ...this.filter, search: this.search, page: this.page, paginate: this.paginate, timestamp: Date.now() } });
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

    this.filter = {};
    Object.keys(this.$route.query).forEach(key => {
        if(this.$route.name !== 'Instructor Announcements') {
          if (!['page', 'paginate', 'search', 'timestamp'].includes(key)) {
              this.filter[key] = this.$route.query[key];
          }
        }
    });

    if(this.$route.name !== 'Instructor Announcements') {
      this.updateQuery()
    }
  },

  beforeRouteEnter(to, from, next) {
      next(vm => {
          vm.prevRoute = from       
      })
  },
};
  