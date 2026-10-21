<template>
  <router-view v-if="$route.name == 'Instructor Rubric Edit'" />
  <router-view v-else-if="$route.name == 'Instructor Rubric View'" />
  <v-sheet  min-height="300" class="d-flex flex-column custom-border pb-2" v-else>
    <section v-if="!loading" class="">
      <Tabs class="px-5 pt-3" :value.sync="tab" :tabs="items"/>
      <v-divider class="mb-1" />
      <v-tabs-items v-model="tab">
        <v-tab-item>
          <CreateRubric :tab="tab" @goToTab="goToTab"/>
        </v-tab-item>

        <v-tab-item>
          <MyRubrics 
            :rubrics="rubrics" 
            :loading="loading"
            :page="page"
            :paginate="paginate"
            :pageCount="pageCount"
            :totalCount="totalCount"/>
        </v-tab-item>

        <v-tab-item>
          <RubricShareToOtherTable :rubrics="rubrics"/>
        </v-tab-item>

        <v-tab-item>
          <RubricShareWithMe :rubrics="rubrics" />
        </v-tab-item>
      </v-tabs-items>
    </section>
    <circular v-else class="ma-auto"/>
  </v-sheet>
</template>

<script>
import { mapActions, mapState } from "vuex";
import items from '../../constants/tabs/rubric/assessmentrubric'
import MyRubrics from "../../components/teacher/rubrics/MyRubric.vue";
import CreateRubric from "../../components/teacher/rubrics/CreateRubric.vue";
import RubricShareWithMe from "../../components/teacher/rubrics/RubricShareWithMe.vue";
import RubricShareToOtherTable from "../../components/teacher/rubrics/RubricShareToOtherTable.vue";

export default {
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
    pageCount: 1,
    page: 1,
    paginate: '10',
    totalCount: 0
  }),
  mounted() {
  },
  computed: {
    ...mapState("instructor", {
      rubrics: (state) => state.rubrics,
    }),
  },
  methods: {
    ...mapActions("instructor", 
    [ "getRubricsAction", 
      "showRubricSharedToOtherAction", 
      "showRubricSharedWithMeAction"]),

    goToTab(tab){
      this.tab = tab
    },

    getRubrics(){
      this.loading = true
      this.getRubricsAction().then(res => {
        this.paginate = String(res.per_page)
        this.page = res.page
        this.pageCount = res.last_page
        this.totalCount = res.total
        this.loading = false;
      }).catch(() => {
        this.loading = false
      })
    }
  },

  watch: {
    tab(val) {
      val == 3
        ? this.showRubricSharedToOtherAction()
        : val == 2
        ? this.showRubricSharedWithMeAction()
        : val == 1
        ? this.getRubrics()
        : "";
    },
  },
};
</script>
