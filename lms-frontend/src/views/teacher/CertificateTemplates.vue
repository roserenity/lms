<template>
    <section id="grading">
      <section class="d-flex flex-wrap align-center justify-space-between">
        <SearchBar 
          :placeholder="'Search template'" 
          :value.sync="search"  
          @clear="clearSearch"
          @search="onSearch"
          class="mb-3"/>
          
          <ButtonPrimary 
            @click="() => { $router.push({ name: 'Instructor Certificate Template Create'}) }"
            :label="'Create'"
            class="mb-3"
          />
      </section>
  
      <v-data-table
        :headers="certificate_template_tbl"
        :items="certificate_templates"
        class="text--center custom-border poppins f14 mb-3"
        :loading="loading"
        hide-default-footer
        :items-per-page.sync="itemsPerPage">
        
        <template v-slot:item.name="{ item }">
            <div v-if="item" class="my-2">
              <div>
                <v-chip
                  color="secondary"
                  v-if="item.is_default === 1"
                  x-small
                  class="mb-1"
                  dense
                >
                  DEFAULT
                </v-chip>
              </div>
              {{ item.name }}
            </div>
        </template>

        <template v-slot:item.created_at="{ item }">
            <span v-if="item"> {{ $dateFormat.mmDDyy(item.created_at) }} </span>
        </template>
        
        <template v-slot:item.certificate_category="{ item }">
            <span v-if="item"> {{ categories.find((category) => item.certificate_category_id === category.id)?.name }} </span>
        </template>

        <template v-slot:item.action="{ item }">
          <v-btn
            v-if="item.uploader_id === 1"
            text
            small
            class="primary--text text-capitalize"
            :to="{ name: 'Instructor Certificate Template View', params: { id: item.id }}"
          >
            <v-icon left small>mdi-eye-outline</v-icon> View
          </v-btn>
          <v-btn
            v-if="item.uploader_id !== 1"
            text
            small
            class="primary--text text-capitalize"
            :to="{ name: 'Instructor Certificate Template Edit', params: { id: item.id }}"
          >
            <v-icon left small>mdi-pencil-outline</v-icon> Edit
          </v-btn>
          <v-btn
            text
            small
            class="primary--text text-capitalize"
            @click="() => {
              selectedCertificateTemplate = item.id,
              duplicateCertificateTemplate()
            }"
          >
            <v-icon left small>mdi-content-duplicate</v-icon> Make a copy
          </v-btn>
          <v-btn
            v-if="item.uploader_id !== 1"
            text
            small
            class="primary--text text-capitalize"
            :loading="duplicateLoading && item.id === selectedCertificateTemplate"
            @click="() => {
              selectedCertificateTemplate = item.id,
              dialog = true
            }"
          >
            <v-icon left small>mdi-trash-can-outline</v-icon> Delete
          </v-btn>
        </template>
      </v-data-table>
      
                          
      <FormPagination 
        :pageCount="pageCount" 
        :page="page"
        :paginate="paginationPaginate"
        @page="onPageChange" 
        @paginate="onPaginateChange"/>
  
      <DeleteDialog 
        :dialog="dialog"
        type="Certificate Template"
        :loading="deleteLoading"
        @close="dialog=false"
        @delete="deleteCertificateTemplate"
      />

    </section>
  </template>
  
  <script>
  import { mapActions, mapState, mapMutations } from 'vuex'
  import { certificate_template_tbl } from '@/constants/tblheaders/certificatetemplate'
  import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
  
  export default {
    mixins: [searchAndPaginateMixin],
    methods: {
        ...mapMutations(['alertMutation']),
        ...mapActions('instructor', ['getCertificateTemplatesAction', 'getCertificateTemplateAction', 'deleteCertificateTemplateAction', 'duplicateCertificateTemplateAction', 'getCertificateTemplateCategory']),
    
        /* resetFilters(){
            this.filter = {
            status: '',
            created_from: ``,
            created_to: ``
            }
    
            this.page = 1
            this.updateQuery()
        }, */
    
        getData(){
          if(!this.loading) {
            this.loading = true
            this.getCertificateTemplatesAction({ paginate: Number(this.paginate), page: this.page, search: this.search }).then((res) => {
                this.pageCount = res.last_page
                this.paginate = String(res.per_page)
                this.page = res.current_page
                this.totalCount = res.total
                this.loading = false
            }).catch(() => {
                this.loading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
          }
        },

        duplicateCertificateTemplate(){
          this.duplicateLoading = true

          this.duplicateCertificateTemplateAction(this.selectedCertificateTemplate).then(() => {
            this.duplicateLoading = true
            this.alertMutation({
                show: true,
                text: 'Certificate Template Successfully Duplicated',
                type: "success"
            })
            this.selectedCertificateTemplate = null
            this.getData()
          }).catch(() => {
            this.duplicateLoading = true
            this.alertMutation({
                show: true,
                text: 'Something went wrong',
                type: "error"
            })
          })
        },

        deleteCertificateTemplate(){
          this.deleteLoading = true

          this.deleteCertificateTemplateAction(this.selectedCertificateTemplate).then(() => {
            this.deleteLoading = true
            this.alertMutation({
                show: true,
                text: 'Certificate Template Successfully Deleted',
                type: "success"
            })
            this.selectedCertificateTemplate = null
            this.dialog = false
            this.getData()
          }).catch(() => {
            this.deleteLoading = true
            this.alertMutation({
                show: true,
                text: 'Something went wrong',
                type: "error"
            })
            this.dialog = false
          })
        }
    },
    computed: {
      ...mapState('instructor', {
        certificate_templates: (state) => state.certificate_templates,
      }),
  
      paginationPaginate(){
        return String(this.paginate)
      },
  
      itemsPerPage(){
        return Number(this.paginate)
      }
    },
    mounted(){
      this.getCertificateTemplateCategory().then((res) => {
        this.categories = res
      })
    },
    data: () => ({
      certificate_template_tbl,
      loading: false,
      totalCount: 0,
      pageCount: 1,
      selected: null,
      dialog: false,
      deleteLoading: false,
      duplicateLoading: false,
      selectedCertificateTemplate: null,
      categories: []
    }),

    watch: {
      dialog(val) {
        if(val === false) {
          this.selectedCertificateTemplate = null
        }
      }
    }
  
  };
  </script>
  
  <style></style>
  
  