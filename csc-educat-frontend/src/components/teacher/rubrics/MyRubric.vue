<template>
  <section id="grading-assessment">
    
    <section v-if="totalCount !== 0" class="text-right mx-5">
      <FormLabel :label="`${totalCount} result/s`"/>
    </section>
    <v-data-table 
      :headers="rubric_list_tbl"
      :items="rubrics"
      :loading="loading"
      class="poppins"
      :page.sync="page"
      hide-default-footer
      :items-per-page.sync="itemsPerPage"
    >           
      <template v-slot:item.created_at="{ item }" class="text-capitalize">
        <span v-if="item">{{$dateFormat.mmDDyy(item.created_at)}}</span>
      </template>
      <template v-slot:item.action="{ item }">
        <v-btn icon
          dense
          small
          color="primary"
          :to="{ name: 'Instructor Rubric View', params: { id: item.id } }"
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
            <v-list-item
              @click="() => {
                $router.push({ name: 'Instructor Rubric Edit', params: { id: item.id } })
              }"
            >
              <v-list-item-title class="roboto f14 d-flex align-center">
                <v-icon size="18" class="mr-2">mdi-pencil</v-icon>
                <div class="">Edit</div>
              </v-list-item-title>
            </v-list-item>
            <!-- <v-list-item link>
              <v-list-item-title class="roboto f14 d-flex align-center">
                <v-icon size="18" class="mr-2">mdi-content-duplicate</v-icon>
                <div class="">Duplicate</div>
              </v-list-item-title>
            </v-list-item> -->
            <v-list-item link @click="remove(item.id)">
              <v-list-item-title class="roboto f14 d-flex align-center">
                <v-icon size="18" class="mr-2">mdi-trash-can-outline</v-icon>
                <div class="">Delete</div>
              </v-list-item-title>
            </v-list-item>
            <v-list-item link @click="share(item)">
              <v-list-item-title class="roboto f14 d-flex align-center">
                <v-icon size="18" class="mr-2">mdi-share-outline</v-icon>
                <div class="">Share</div>
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>
    <RubricShareDialog :dialog="shareDialog" :data="toShare" @close="shareDialog = false"/>
    
    <DeleteDialog 
      :dialog="dialog"
      type="Rubric"
      :loading="deleteLoading"
      @close="dialog=false"
      @delete="deleteRubric"
    />
  </section>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
import RubricShareDialog from './RubricShareDialog.vue';
import { rubric_list_tbl } from '@/constants/tblheaders/rubric';

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
    },
  },
  components: {
    RubricShareDialog
  },
  data: () => ({
    rubric_list_tbl,
    dialog: false,
    id: null,
    shareDialog: false,
    toShare: null,
    deleteLoading: false
  }),
  mounted(){
  },
  methods: {
    ...mapActions("instructor", ["getInstructorsRubricToShareAction", "deleteRubricAction"]),
    
    ...mapMutations(["alertMutation"]),

    remove(id) {
      this.dialog = true;
      this.id = id;
    },

    deleteRubric() {
      this.deleteLoading = true
      this.deleteRubricAction(this.id).then(()=>{
        this.deleteLoading = false
        this.dialog = false
        this.alertMutation({
          show: true,
          text: `Rubric is Successfully Deleted`,
          type: "success"
        });
        this.$emit('getRubrics')
      }).catch(() => {
        this.deleteLoading = false
        this.dialog = false
        this.alertMutation({
          show: true,
          text: `Something went wrong`,
          type: "error"
        });
      })
    },

    share(item) {
      this.shareDialog = true
      this.toShare = item
    }
  },
  computed: {
    ...mapState("instructor", {
      courses: (state) => state.courses,
    }),
    itemsPerPage(){
      return Number(this.paginate)
    }
  }
};
</script>

<style></style>
