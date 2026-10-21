<template>
  <section id="trash">

    <v-card
      class="d-flex flex-column align-center justify-center pb-10 pa-5 custom-border border"
      height="500"
      v-if="trash.length === 0 && !loading"
    >
      <div class="">
        <v-img max-width="300" :src="require('../../assets/default/empty_trash.png')" />
      </div>
      <h3 class="poppins fw600 my-5">
          Trash is Empty
      </h3>
      <h4 class="poppins secondary-1--text fw400 text-center">
          Move files you don’t need anymore in Trash. All of the data on this trash will be permanently deleted after 30 days. 
      </h4>
    </v-card>
    <v-card v-else class="custom-border border pa-5">
      <section class="mx-3 d-flex flex-wrap justify-space-between">
        <SearchBar 
          :placeholder="'Search course'" 
          :outlined="true" 
          :value.sync="search" 
          @clear="clearSearch"
          @search="onSearch"
          class="mb-2"/>
        <div class="d-flex justify-end align-center mb-2">
          <v-btn 
            text 
            small
            @click="gallery =  true"
            class="mx-1 hidden-sm-and-down"
          >
            <v-icon size="22" :class="gallery ? 'primary--text' : 'secondary-2--text'">
              mdi-view-gallery-outline
            </v-icon>
          </v-btn>
          <v-btn 
            text 
            small
            @click="gallery = false"
            class="mx-1 hidden-sm-and-down"
          >
            <v-icon size="22" :class="!gallery ? 'primary--text' : 'secondary-2--text'">
              mdi-format-list-bulleted
            </v-icon>
          </v-btn>
        </div>
      </section>

      <LinearProgress  v-if="loading" class="my-3"/>
      <v-card-text v-if="trash.length>0" class="d-flex" :class="[$vuetify.breakpoint.mobile && 'justify-center', gallery ? 'flex-wrap' : 'flex-column']">
        <CourseCardGallery 
          v-if="gallery"
          v-for="(item,i) in trash"
          :key="item.id"
          :i="i" 
          :item="item" 
          :gallery="gallery"
          class="ma-3"
          @fetchCourses="getData"
        />
        <CourseCardList 
          v-if="!gallery"
          v-for="(item,i) in trash"
          :key="item.id"
          :i="i" 
          :item="item" 
          class="ma-3"
          @fetchCourses="getData"
        />
      </v-card-text>
      <FormPagination 
        :pageCount="pageCount" 
        :page="page"
        :paginate="paginationPaginate"
        @page="onPageChange" 
        @paginate="onPaginateChange"/>
    </v-card>
    
    <DeleteAllDialogVue :dialog="deleteDialog" @close="deleteDialog=false"/>
  </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from "vuex";
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import CourseCardGallery from "../../components/teacher/course/CourseCardGallery.vue";
import CourseCardList from "../../components/teacher/course/CourseCardList.vue";
import DeleteAllDialogVue from "../../components/teacher/trash/DeleteAllDialog.vue";

export default {
  mixins: [searchAndPaginateMixin],
  components: {
    CourseCardGallery,
    DeleteAllDialogVue,
    CourseCardList
  },
  data: () => ({
    loading: true,
    gallery: true,
    deleteDialog: null,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    pageCount: 1,
  }),
  computed: {
    ...mapState("instructor", {
      trash: (state) => state.trash,
    }),
    paginationPaginate(){
      return String(this.paginate)
    }
  },
  created() {
    this.onResize()
  },
  methods: {
    ...mapActions("instructor", ["getTrashAction", "getCoursesAction"]),
    ...mapMutations(["alertMutation"]),

    getData(){
      this.loading = true
      this.getTrashAction({ paginate: Number(this.paginate), page: this.page, search: this.search}).then(res => {
        this.page = res.current_page
        this.pageCount = res.last_page
        this.paginate = String(res.per_page)
        this.loading = false;
      }).catch(() => {
        this.loading = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong.',
          type: "error"
        })
      })
    },

    onResize(){
      if(this.$vuetify.breakpoint.smAndDown || this.$vuetify.breakpoint.mobile){
        this.gallery = false
      }
    }
  },
  mounted() {
    window.addEventListener('resize', this.onResize, {passive: true})
  }
};
</script>  