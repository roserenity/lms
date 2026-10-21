<template>
  <section id="class-category">
    <section class="d-flex flex-wrap justify-space-between">
      <SearchBar :placeholder="'Search certificate type'" :value.sync="search" 
        @clear="() => { search = '', page = 1, getData() }" @search="() => {
          page = 1
          getData()
        }" class="mb-5" />

      <ButtonPrimary 
        :outlined="true"
        @click="addCertificateTemplateDialog = true"
        :label="'Add Certificate Template Category'"
        :icon="'mdi-plus'"
        class="mb-3 ml-auto"
      />
    </section>
    <section v-if="totalCount !== 0" class="text-right">
      <FormLabel :label="`${totalCount} result/s`"/>
    </section>
    <v-data-table
        :headers="headers"
        :items="classes"
        class="elevation-1 poppins"
        :loading="loading"
        hide-default-footer
        :page.sync="page"
        :items-per-page.sync="itemsPerPage"
    >
      <template v-slot:item.action="{ item }">
        <v-btn icon color="primary" @click="selectedItem = item, deletedialog=true" v-if="item.id !== 1">
          <v-icon small>mdi-trash-can-outline</v-icon>
        </v-btn>
      </template>
    </v-data-table>
                  
    <FormPagination 
      :pageCount="pageCount" 
      :page="page"
      :paginate="paginationPaginate"
      @page="onPageChange" 
      @paginate="onPaginateChange"/>

	  <v-dialog persistent v-model="addCertificateTemplateDialog" max-width="450">
        <v-card class="rounded-lg">
          <v-card-title class="d-flex align-center justify-center px-4">
            <h5 class="primary--text poppins fw600 text-center">Add Certificate Template Category</h5>
            <v-spacer/>
            <v-btn icon @click="addCertificateTemplateDialog = false" dense>
              <v-icon size="22">mdi-close</v-icon>
            </v-btn>
          </v-card-title>
          <v-form @submit.prevent="add" ref="form">
          <v-card-text>
            <FormTextFieldRequired
              :label="'CERTIFICATE TEMPLATE NAME *'"
              :value.sync="category_name"
              :readonly="savingLoading"
            />
          </v-card-text>
          <v-divider/>
          <v-card-actions class="d-flex align-center justify-end mx-2">
            <v-btn text @click="() => {
              category_name = '',
              addCertificateTemplateDialog = false
            }">Cancel</v-btn>
            <ButtonPrimary
                @click="saveCertificateCategory"
                :loading="savingLoading"
                :label="'Save'"
            />
          </v-card-actions>
          </v-form>
        </v-card>
      </v-dialog>
      <DeleteDialog 
        :dialog="deletedialog"
        type="Category"
        :loading="deleteLoading"
        @close="deletedialog=false"
        @delete="deleteCertificateTemplateCategory"
      />
  </section>
</template>
  
<script>
  import { mapActions, mapState, mapMutations } from 'vuex'
  import Table from '../../components/admin/course/ClassTable.vue'
  import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
  import ButtonPrimary from '@/components/reusable/ButtonPrimary.vue';
  
  export default {
    mixins: [searchAndPaginateMixin],
    components: { Table },
    data: () => ({
      addCertificateTemplateDialog: false,
      toast: false,
      text: '',
      loading: false,
      savingLoading: false,
      deleteLoading: false,
      classes: [],
      totalCount: 0,
      pageCount: 1,
      search: '',
      headers: [
        {
            text: 'Certificate Template Type',
            value: 'name',
            align: 'start',
            sortable: false
        },
        {
            text: 'Action',
            value: 'action',
            align: 'center',
            sortable: false
        },
      ],
      category_name: '',
      selectedItem: null,
      deletedialog: false
    }),
    computed: {
      ...mapState({
        errors: (state) => state.errors,
      }), 

      itemsPerPage(){
        return Number(this.paginate)
      },

      paginationPaginate(){
          return String(this.paginate)
      }

    },
    methods: {
      ...mapActions('admin', ['getCertificateTemplateCategoryAction', "addCertificateCertificateTemplateAction", "deleteCertificateTemplateCategoryAction"]),
      ...mapMutations(['alertMutation']),
  
      getData(){
        this.loading = true
        this.getCertificateTemplateCategoryAction({page: this.page, paginate: Number(this.paginate), search: this.search}).then(res => {
          this.loading=false
          this.classes = res.data
          this.pageCount = res.last_page
          this.totalCount = res.total
        }).catch(() => {
          this.loading = false
          this.classes = []
          this.pageCount = 0
          this.totalCount = 0
          this.alertMutation({
            show: true,
            text: 'Something went wrong',
            type: "error"
          })
        })
      },

      saveCertificateCategory() {
        this.savingLoading = true
        this.addCertificateCertificateTemplateAction({ name: this.category_name }).then(() => {
          this.alertMutation({
              show: true,
              text: 'Successfully added a new category',
              type: "success"
          })
          this.savingLoading = false
          this.addCertificateTemplateDialog = false
          this.getData();
        }).catch(() => {
          this.savingLoading = false
          this.alertMutation({
              show: true,
              text: 'Something went wrong.',
              type: "error"
          })
        })
      },

      deleteCertificateTemplateCategory(){
        this.deleteLoading = true
        this.deleteCertificateTemplateCategoryAction({ id: this.selectedItem.id }).then(() => {
          this.alertMutation({
              show: true,
              text: 'Successfully deleted a category',
              type: "success"
          })
          this.deleteLoading = false
          this.deletedialog = false
          this.getData();
        }).catch(() => {
          this.deleteLoading = false
          this.deletedialog = false
          this.alertMutation({
              show: true,
              text: 'Something went wrong.',
              type: "error"
          })
        })
      }
    },

    watch: {
      addCertificateTemplateDialog() {
        this.category_name = ''
      }
    }

  }
  </script>
  