<template>
    <section>
      <section v-if="totalCount !== 0 && !onPreview" class="text-right mx-5">
        <FormLabel :label="`${totalCount} result/s`"/>
      </section>
      <v-data-table
        :headers="shared_with_me_tbl"
        :items="rubrics"
        :loading="loading"
        class="elevation-1 poppins"
        :page.sync="page"
        hide-default-footer
        :items-per-page.sync="itemsPerPage"
        v-if="!onPreview"
      >
        <template v-slot:item.title="{ item }">
          <span v-if="item">{{ item.rubric.title }}</span>
        </template>
        <template v-slot:item.shared_by="{ item }">
          <span v-if="item">{{ item.shared_by_user.email }}</span>
        </template>
        <template v-slot:item.date_shared="{ item }">
          <span v-if="item">{{$dateFormat.mmDDyy(item.created_at)}}</span>
        </template>
        <template v-slot:item.action="{ item }">
          <v-btn
              icon
              dense
              small
              color="primary"
              class="mr-2"
              @click="$router.push({ name: 'Instructor Rubric View', params: { id: item.rubric_id } })">
              <v-icon
                  small
                  outline
              >
                mdi-eye-outline
              </v-icon>
          </v-btn>
          <v-btn
              icon
              dense
              small
              color="primary"
              @click="$router.push({ name: 'Instructor Rubric Edit', params: { id: item.rubric_id } })">
              <v-icon
                  small
                  outline
              >
                mdi-pencil-outline
              </v-icon>
          </v-btn>
        </template>
      </v-data-table>
  
      <!-- <MoveDialog
        action="shared"
        :data="toShare"
        :courses="courses"
        :dialog="moveDialog"
        @close="moveDialog = false"
      /> -->
      <PreviewRubric v-if="onPreview" :rubric="toPreview.rubric" :rubric_id="toPreview.rubric_id" :mode="1"/>
    </section>
  </template>
  
  <script>
  import { mapActions, mapState } from "vuex";
  import PreviewRubric from "./PreviewRubric.vue";
  import { shared_with_me_tbl } from '@/constants/tblheaders/shared'
  
  export default {
    props: {
      rubrics: {
        default: [],
        type: Array
      }, 
      loading: {
        default: false,
        type: Boolean
      },
      totalCount: {
        default: 0,
        type: Number
      },
      pageCount: {
        default: 1,
        type: Number
      },
      page: {
        default: 1,
        type: Number
      },
      paginate: {
        default: '10',
        type: String
      }
    },
    components: {
      PreviewRubric
    },
    data: () => ({
      moveDialog: false,
      toShare: null,
      shared_with_me_tbl,
      toPreview: {
        rubric: null
      },
      onPreview: false,
    }),
    computed: {
      ...mapState("instructor", {
        courses: (state) => state.courses,
      }),
      itemsPerPage(){
        return Number(this.paginate)
      }
    },
    created() {},
    methods: {
      ...mapActions("instructor", ["getCoursesAction"]),
  
      move(data) {
        this.getCoursesAction().then(() => {
          this.moveDialog = true;
        });
        this.toShare = data;
      },

      preview(data) {
        this.toPreview = data
        this.onPreview = true
      }
    },
    watch: {},
  };
  </script>