<template>
  <section>
    <v-data-table
        :headers="assessments_tbl"
        :items="assessments"
        :loading="loading"
        class="poppins"
        :page.sync="page"
        hide-default-footer
        :items-per-page.sync="itemsPerPage"
      >
      <template v-slot:item.title="{ item }">
          <span class="poppins">{{ !item.title ? 'Untitled' : item.title }}</span>
      </template>
      <template v-slot:item.created_at="{ item }" class="text-capitalize">
        <span v-if="item">{{$dateFormat.mmDDyy(item.created_at)}}</span>
      </template>
      <template v-slot:item.action="{ item }">
        <v-btn icon
          dense
          small
          color="primary"
          @click="$emit('preview', item)"
          class="mr-2">
          <v-icon
              small
              outline
          >
              mdi-eye-outline
          </v-icon>
        </v-btn>

        <v-menu transition="slide-y-transition" bottom offset-y>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon
                  dense
                  small
                  color="primary"
                  v-bind="attrs"
                  v-on="on">
                  <v-icon
                      small
                      outline
                  >
                      mdi-dots-horizontal
                  </v-icon>
              </v-btn>
            </template>
            <v-list dense class="custom-border">
              <v-list-item link  @click="$emit('move', item)">
              <v-list-item-title class="roboto f14 d-flex align-center">
                  <v-icon size="18" class="mr-2">mdi-file-move-outline</v-icon>
                  <div class="">Move to</div>
              </v-list-item-title>
              </v-list-item>
              <v-list-item link @click="$emit('duplicate', { id: item.id, course_id: item.course_id })">
              <v-list-item-title class="roboto f14 d-flex align-center">
                  <v-icon size="18" class="mr-2">mdi-content-duplicate</v-icon>
                  <div class="">Duplicate</div>
              </v-list-item-title>
              </v-list-item>
              <v-list-item link @click="$emit('remove', item)">
              <v-list-item-title class="roboto f14 d-flex align-center">
                  <v-icon size="18" class="mr-2">mdi-trash-can-outline</v-icon>
                  <div class="">Delete</div>
              </v-list-item-title>
              </v-list-item>
              <v-list-item link @click="$emit('share', item)">
              <v-list-item-title class="roboto f14 d-flex align-center">
                  <v-icon size="18" class="mr-2">mdi-share-outline</v-icon>
                  <div class="">Share</div>
              </v-list-item-title>
              </v-list-item>
            </v-list>
        </v-menu>
      </template>
    </v-data-table>
  </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from "vuex";
import ShareDialog from "./AssessmentShareDialog.vue";
import MoveDialog from "./AssessmentMoveDialog.vue";
import PreviewDialog from "./AssessmentPreviewDialog.vue";
import { assessments_tbl } from '../../../constants/tblheaders/assessment'

export default {
  props: {
    assessments: {
      type: Array,
      default: []
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
    ShareDialog,
    MoveDialog,
    PreviewDialog,
  },
  data: () => ({
    dialog: false,
    moveDialog: false,
    deleteDialog: false,
    menu: false,
    itemToRemove: {
      id: 0,
      course_id: null
    },
    assessments_tbl
  }),
  computed: {
    ...mapState("instructor", {
      duplicate_id: (state) => state.duplicate_id,
      courses: (state) => state.courses,
      coursesLength: (state) => state.courses.length
    }),
    
    itemsPerPage(){
      return Number(this.paginate)
    }
  },
  created() {
    window.onbeforeunload = () => {
      alert("This page cannot be reload");
    };

  },
  methods: {
  },
};
</script>
