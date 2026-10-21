<template>
    <section>
      <section v-if="totalCount !== 0" class="text-right mx-5">
        <FormLabel :label="`${totalCount} result/s`"/>
      </section>
      <v-data-table 
        :headers="share_to_tbl"
        :items="rubrics"
        :loading="loading"
        class="elevation-1 poppins"
        :page.sync="page"
        hide-default-footer
        :items-per-page.sync="itemsPerPage"
      >
        <template v-slot:item.date_shared="{ item }">
          <span v-if="item">{{ $dateFormat.mmDDyy(item.shared_rubrics[0].created_at) }}</span>
        </template>
        <template v-slot:item.last_modified="{ item }">
          <span v-if="item">{{ $dateFormat.mmDDyy(item.updated_at) }}</span>
        </template>
        <template v-slot:item.shared_to="{ item }">
          <div class="" v-if="item && item.shared_rubrics.length > 0">
            <span class="secondary-1--text">
              {{ item.shared_rubrics[0].shared_to_user.email }}
            </span>
            <span v-if="item.shared_rubrics.length > 1" class="secondary--text">and </span>
            <v-menu
              origin="center center"
              transition="slide-y-transition"
              :close-on-content-click="false"
              content-class="elevation-0"
              max-width="300"
              offset-x
            >
              <template v-slot:activator="{ on, attrs }">
                <a
                  v-bind="attrs"
                  v-on="on"
                  href="#"
                  class="primary--text text-decoration-none"
                  v-if="item.shared_rubrics.length > 1"
                  >{{ `${item.shared_rubrics.length - 1} others ` }}</a
                >
              </template>
              <v-card outlined class="custom-border">
                <v-card-text>
                  <div class="f14 fw500 sprimary--text poppins">Shared to :</div>
                  <v-divider class="my-2" />
                  <div
                    class="poppins f14 secondary-1--text my-1"
                    v-for="(item, index) in item.shared_rubrics"
                    :key="index"
                  >
                    {{ item.shared_to_user.email }}
                  </div>
                </v-card-text>
              </v-card>
            </v-menu>
          </div>
        </template>
      </v-data-table>
    </section>
  </template>
  
  <script>
  import { mapState } from "vuex";
  import { share_to_tbl } from '@/constants/tblheaders/shared'
  
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
    components: {},
    data: () => ({
      share_to_tbl,
      tab: null,
      dialog: false,
      deleteDialog: false,
      toShare: null,
      menu: false,
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
  