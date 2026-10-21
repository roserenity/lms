<template>
  <section>
    <v-data-table 
        :headers="share_to_tbl"
        :items="assessments"
        :loading="loading"
        class="poppins"
        :page.sync="page"
        hide-default-footer
        :items-per-page.sync="itemsPerPage"
      >
      <template v-slot:item.date_shared="{ item }">
        {{ $dateFormat.mmDDyy(item.shared_assessments[0].created_at) }}
      </template>
      <template v-slot:item.last_modified="{ item }">
        {{ $dateFormat.mmDDyy(item.updated_at) }}
      </template>
      <template v-slot:item.shared_to="{ item }">
        <div class="" v-if="item.shared_assessments.length > 1">
          <span class="secondary-1--text">
            {{ item.shared_assessments[0].shared_to_user.email }}
          </span>
          <span class="secondary--text">and </span>
          <v-menu
            origin="center center"
            transition="slide-y-transition"
            :close-on-content-click="false"
            content-class="my-menu elevation-0"
            max-width="300"
            offset-x
          >
            <template v-slot:activator="{ on, attrs }">
              <a
                v-bind="attrs"
                v-on="on"
                href="#"
                class="primary--text text-decoration-none"
                >{{ `${item.shared_assessments.length - 1} others ` }}</a
              >
            </template>
            <v-card outlined class="custom-border">
              <v-card-text>
                <h3 class="primary--text poppins">Shared to :</h3>
                <v-divider class="my-2" />
                <div
                  class="roboto f14 secondary-1--text my-1"
                  v-for="(item, index) in item.shared_assessments"
                  :key="index"
                >
                  {{ item.shared_to_user.email }}
                </div>
              </v-card-text>
            </v-card>
          </v-menu>
        </div>
        <div class="" v-else>
          <span class="secondary-1--text">
            {{ item.shared_assessments[0].shared_to_user.email }}
          </span>
        </div>
      </template>
    </v-data-table>
  </section>
</template>

<script>
import { share_to_tbl } from '@/constants/tblheaders/shared'

export default {
  props: {
    assessments: {
      type: Array,
      default: []
    }, 
    loading: {
      type: Boolean,
      default: false
    },
    pageCount: {
      type: Number,
      default: 1
    },
    page: {
      type: Number,
      default: 1
    },
    paginate: {
      type: String,
      default: '10'
    },
  },
  components: {},
  data: () => ({
    tab: null,
    dialog: false,
    deleteDialog: false,
    toShare: null,
    menu: false,
    share_to_tbl,
    idToRemove: null,
    itemToRemove: null,
  }),
  computed: {
    itemsPerPage(){
      return Number(this.paginate)
    }
  }
};
</script>

<style scoped>
.my-menu {
  /* margin-top: 40px; */
  contain: initial;
  overflow: visible;
}
.my-menu::before {
  position: absolute;
  content: "";
  top: 10px;
  /* right: 10px; */
  transform: translateX(-100%);
  /* box-shadow: #fff; */
  /* box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.04), 0px 0px 2px rgba(0, 0, 0, 0.06), 0px 0px 1px rgba(0, 0, 0, 0.04) !important; */
  /* box-shadow: 0 -5px 5px -5px #333; */
  width: 13px;
  height: 13px;
  border-top: 12px solid transparent;
  border-left: 12px solid transparent;
  border-right: 13px solid #e5e5e5;
  border-bottom: 12px solid transparent;
}
</style>
