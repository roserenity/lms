<template>
  
    <router-view v-if="$route.name === 'Instructor Rubric Edit' || $route.name === 'Instructor Rubric View'" />
    <v-card class="d-flex flex-column custom-border border pb-2" v-else>
        <SearchBar 
          :placeholder="'Search rubric'" 
          :outlined="true" 
          :value.sync="search" 
          @clear="clearSearch"
          @search="onSearch"
          :disabled="tab === 0"
          class="pa-4"/>
        <Tabs :value.sync="tab" :tabs="items" :disabled="loading"/>
        <v-divider class="mb-1" />
        <v-tabs-items v-model="tab">
          <v-tab-item>
            <CreateRubric :tab="tab" @goToTab="goToTab"/>
          </v-tab-item>
  
          <v-tab-item>
            <MyRubrics
               @goToTab="goToTab"
               @getRubrics="getRubrics"
              :rubrics="rubrics" 
              :loading="loading"
              :page="page"
              :paginate="paginate"
              :pageCount="pageCount"
              :totalCount="totalCount"/>
          </v-tab-item>
  
          <v-tab-item>
            <RubricShareWithMe 
              :rubrics="rubrics"
              :loading="loading"
              :page="page"
              :paginate="paginate"
              :pageCount="pageCount"
              :totalCount="totalCount"/>
          </v-tab-item>
  
          <v-tab-item>
            <RubricShareToOtherTable 
              :rubrics="rubrics"
              :loading="loading"
              :page="page"
              :paginate="paginate"
              :pageCount="pageCount"
              :totalCount="totalCount"/>
          </v-tab-item>
        </v-tabs-items>
                        
        <FormPagination 
            v-if="tab !== 0"
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginationPaginate"
            @page="onPageChange" 
            @paginate="onPaginateChange"/>
    </v-card>
  </template>
  
  <script>
  import { mapActions, mapState, mapMutations } from "vuex";
  import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
  import items from '@/constants/tabs/rubric/assessmentrubric'
  import MyRubrics from "@/components/teacher/rubrics/MyRubric.vue";
  import CreateRubric from "@/components/teacher/rubrics/CreateRubric.vue";
  import RubricShareWithMe from "@/components/teacher/rubrics/RubricShareWithMe.vue";
  import RubricShareToOtherTable from "@/components/teacher/rubrics/RubricShareToOtherTable.vue";
  
  export default {
    mixins: [searchAndPaginateMixin],
    components: {
        MyRubrics,
        CreateRubric,
        RubricShareWithMe,
        RubricShareToOtherTable,
    },
    data: () => ({
      loading: false,
      items: items,
      tab: 0,
      totalCount: 0,
      pageCount: 1
    }),
    mounted() {
      this.getInstructorsRubricToShareAction()

      this.tab = Number(this.filter.tab)
    },
    computed: {
        ...mapState("instructor", {
            rubrics: (state) => state.rubrics,
        }),

        paginationPaginate(){
            return String(this.paginate)
        }
    },
    methods: {
        ...mapActions("instructor", 
        [ "getRubricsAction", 
            "showRubricSharedToOtherAction", 
            "showRubricSharedWithMeAction",
            "getInstructorsRubricToShareAction"
          ]),

        ...mapMutations(["alertMutation"]),

        ...mapMutations("instructor", ["rubricsMutation"]),
  
        goToTab(tab){
            this.tab = tab
        },

        getData(){
          this.tab == 3
            ? this.getSharedToOthersRubrics()
            : this.tab == 2
            ? this.getSharedWithMeRubrics()
            : this.tab == 1
            ? this.getRubrics()
            : "";
        },

        getRubrics(){
          if(!this.loading) {
            this.loading = true
            this.getRubricsAction({ paginate: this.paginate, page: this.page, search: this.search }).then(res => {
              this.paginate = String(res.per_page)
              this.page = res.current_page
              this.pageCount = res.last_page
              this.totalCount = res.total
              this.loading = false;
            }).catch(() => {
                this.loading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in fetching your rubrics',
                    type: "error"
                })
            });
          }
        },

        getSharedWithMeRubrics(){
          if(!this.loading) {
            this.loading = true
            this.showRubricSharedWithMeAction({ paginate: this.paginate, page: this.page, search: this.search }).then(res => {
              this.paginate = String(res.per_page)
              this.page = res.current_page
              this.pageCount = res.last_page
              this.totalCount = res.total
              this.loading = false;
            }).catch(() => {
                this.loading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in fetching received rubrics',
                    type: "error"
                })
            });
          }
        },

        getSharedToOthersRubrics(){
          if(!this.loading) {
            this.loading = true
            this.showRubricSharedToOtherAction({ paginate: this.paginate, page: this.page, search: this.search }).then(res => {
              this.paginate = String(res.per_page)
              this.page = res.current_page
              this.pageCount = res.last_page
              this.totalCount = res.total
              this.loading = false;
            }).catch(() => {
                this.loading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in fetching shared rubrics',
                    type: "error"
                })
            });
          }
        }
    },
  
    watch: {
      tab(val) {
        this.rubricsMutation([])
        this.page = 1
        this.filter.tab = val
        this.updateQuery()
        this.getData()
      },
    },
  };
  </script>
  