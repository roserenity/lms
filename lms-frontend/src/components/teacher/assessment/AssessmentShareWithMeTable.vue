<template>
  <section>
    <v-data-table
      :headers="shared_with_me_tbl"
      :items="assessments"
      :loading="loading"
      class="poppins"
      :page.sync="page"
      hide-default-footer
      :items-per-page.sync="itemsPerPage"
    >
      <template v-slot:item.title="{ item }">
        <v-badge
          dot
          left
          inline
          v-if="new_assessment_uuid && item.assessment.uuid === new_assessment_uuid"
          class="pl-1"
        > {{ item.assessment.title ? item.assessment.title : "Untitled"}}
        </v-badge>
        <span v-else> {{ item.assessment.title ? item.assessment.title : "Untitled"}} </span>
      </template>
      <template v-slot:item.shared_by="{ item }">
        {{ item.shared_by_user ? (item.shared_by_user.email ? item.shared_by_user.email : '') : '' }}
      </template>
      <template v-slot:item.date_shared="{ item }">
        {{ $dateFormat.mmDDyy(item.created_at) }}
      </template>
      <template v-slot:item.action="{ item }">
        <v-btn icon color="primary" @click="move(item.assessment)">
          <v-icon>mdi-folder-move-outline</v-icon>
        </v-btn>
      </template>
    </v-data-table>
    <AssessmentMoveDialog
      action="shared"
      :data="toShare"
      :courses="courses"
      :dialog="moveDialog"
      :courseLoading="courseLoading"
      @close="moveDialog = false"
    />
  </section>
</template>

<script>
import { mapActions, mapState } from "vuex";
import AssessmentMoveDialog from "./AssessmentMoveDialog.vue";
import { shared_with_me_tbl } from '@/constants/tblheaders/shared'

export default {
  props: {
    assessments: {
      type: Array,
      default: []
    },
    new_assessment_uuid: {
      type: String,
      default: ''
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
    },
  },
  components: {
    AssessmentMoveDialog,
  },
  data: () => ({
    moveDialog: false,
    toShare: {
      title: 'Untitled'
    },
    shared_with_me_tbl,
    courseLoading: false
  }),
  computed: {
    ...mapState("instructor", {
      courses: (state) => state.courses,
    }),

    itemsPerPage(){
      return Number(this.paginate)
    }
  },
  created() {
    if(!this.courseLoading) {
      this.getCoursesAction().then(() => {
        this.courseLoading = false
      }).catch(() => {
        this.courseLoading = false
      });
    }
  },
  methods: {
    ...mapActions("instructor", ["getCoursesAction"]),

    move(data) {
      this.moveDialog = true
      this.toShare = data;
    },
  },
  watch: {},
};
</script>