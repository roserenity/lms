<template>
  <section>
    <section class="d-flex flex-wrap align-center justify-space-between">
      <SearchBar 
        :placeholder="'Search user'" 
        :value.sync="search_" 
        @search="() => {
          $emit('updateSearch', search_)
          $emit('onSearch')
        }" 
        @clear="() => {
          $emit('updateSearch', '')
          $emit('onSearch')
        }"
        class="mb-5"/>
      <section class="d-flex flex-row align-center ml-auto mb-5">
        <FilterMenu
          :initialFilter="filter"
          @resetFilters="() => {
            minDate = ''
            maxDate = ''
            $emit('resetFilters')
          }"
          @applyFilters="() => { $emit('page', 1) }"
        >
          <LinearProgress v-if="filterLoading" class="mb-3" />
          <v-sheet max-height="300" class="overflow-y-auto scroller">

            <FormLabel :label="'ACCOUNT TYPE'"/>
              <v-chip-group column v-model="filter.role" active-class="primary" multiple>
                <v-chip filter outlined label color="secondary-3" value="USER" class="caption" >Learner</v-chip>
                <v-chip filter outlined label color="secondary-3" value="INSTRUCTOR" class="caption">Instructor</v-chip>
                <v-chip filter outlined label color="secondary-3" value="ADMIN" class="caption">Admin</v-chip>
              </v-chip-group>
              <FormLabel :label="'STATUS'"/>
              <v-chip-group v-model="filter.status">
                <v-chip filter outlined label color="secondary-3" value="active" class="caption">Active</v-chip>
                <v-chip filter outlined label color="secondary-3" value="inactive" class="caption">Inactive</v-chip>
              </v-chip-group>
              <div class="mt-3">
                  <FormLabel class="mb-1" :label="'REGISTRATION DATE (YYYY-MM-DD)'" />
              </div>
              <label class="caption">FROM:</label>
              <FormDatePicker 
                  :val="filter.created_from"
                  :max="maxDate"
                  class_="col-11 mb-3"
                  @apply="(e) => {
                      filter.created_from = e
                      minDate = e
                  }"/>

              <label class="caption">TO:</label>
              <FormDatePicker 
                  :val="filter.created_to"
                  :min="minDate"
                  class_="col-11 mb-3"
                  @apply="(e) => {
                      filter.created_to = e
                      maxDate = e
                  }"/>
              <div v-for="(item, i) in otherFilters" :key="i">
                <FormLabel :label="item.input_field_label.toUpperCase()" class="text-capitalize"/>
                <v-chip-group column v-model="filter[item.input_field_name]" multiple>
                  <v-chip v-for="option in item.data" :key="option" filter outlined label color="secondary-3" :value="option" class="caption">{{ option }}</v-chip>
                </v-chip-group>
              </div>
            </v-sheet>
        </FilterMenu>
        <ButtonExport 
          @click="exportTable"
          :loading="exportLoading"
          :disabled="loading || users.length === 0"
          class="ma-1 ml-auto"/>

        <ButtonPrimary
          :icon="'mdi-bell-outline'"
          @click="requestInfoUpdate"
          :label="'Request Info Update'"
          :loading="infoUpdateLoading"
        />
      </section>
    </section>
    <section v-if="totalCount !== 0" class="text-right">
      <FormLabel :label="`${totalCount} result/s`"/>
    </section>
    <v-data-table
      :headers="user_management_tbl"
      :items="users"
      :loading="loading"
      class="elevation-1 poppins f10"
      hide-default-footer
      :items-per-page.sync="itemsPerPage"
    >
      <template v-slot:header.name="{ header }" class="f12 poppins fw600 text-left text-uppercase secondary-2--text">
        {{ header.text }}
      </template>
      <template v-slot:item.role="{ item }" class="text-capitalize">
        {{ item.role === 'USER' ? 'LEARNER' : item.role }}
      </template>
      <template v-slot:item.email_verified_at="{ item }" class="text-capitalize">
        <v-chip v-if="item.email_verified_at" dense color="#7BC14533" class="success--text ma-auto fw5600">VERIFIED</v-chip>
        <v-chip v-else dense color="#BDBDBD33" class="secondary-2--text ma-auto fw600">UNVERIFIED</v-chip>
      </template>
      <template v-slot:item.status="{ item }" class="text-capitalize">
        <v-switch
          v-model="item.status"
          true-value="active"
          false-value="inactive"
          :label="item.status"
          class="text-capitalize"
          @change="updateStatus(item.id, $event)"
        ></v-switch>
      </template>
      <template v-slot:item.created_at="{ item }" class="text-capitalize">
        {{$dateFormat.mmDDyy(item.created_at)}}
      </template>
      <template v-slot:item.action="{ item }">
        <section class="d-flex flex-row">
          <v-btn @click="preview(item.id)" icon class="text-capitalize primary--text f12 fw600">
            <v-icon size="16" >mdi-eye-outline</v-icon>
          </v-btn>
          <!-- <v-btn @click="update(item.id)" icon class="text-capitalize primary--text f12 fw600">
            <v-icon size="16" >mdi-pencil-outline</v-icon>
          </v-btn> -->
          <v-btn @click="update(item.id)" icon class="text-capitalize primary--text f12 fw600">
            <v-icon size="16" >mdi-pencil-outline</v-icon>
          </v-btn>
          <v-btn @click="del(item.id)" icon class="text-capitalize primary--text f12 fw600">
            <v-icon size="16">mdi-trash-can-outline</v-icon>
          </v-btn>
        </section>
      </template>
    </v-data-table>
    <FormPagination 
      :pageCount="pageCount" 
      :page="page"
      :paginate="paginationPaginate"
      @page="(e) => $emit('page', e)" 
      @paginate="(e) => $emit('paginate', e)"/>
    <!-- <v-row align="center" class="my-2" v-if="users.length > 0">
      <v-col lg="1" class="hidden-md-and-down">
        <v-text-field
          :value="itemsPerPage"
          label="Items"
          type="number"
          min="-1"
          max="15"
          @input="itemsPerPage = parseInt($event, 10)"
        ></v-text-field>
      </v-col>

      <v-col lg="10" md="12">
        <v-pagination
          v-model="page"
          :length="pageCount"
          :total-visible="7"
        ></v-pagination>
      </v-col>
    </v-row> -->
    
    <DeleteDialog 
      :dialog="dialog"
      type="User"
      :loading="deleteLoading"
      @close="dialog=false"
      @delete="remove"
    />
    
    <UpdateModal v-if="updateDialog" :dialog="updateDialog" :item="users.filter(item => item.id == this.id)[0]" @close="()=>{ updateDialog = false, $emit('getUsers') }"/>
    <UserModal v-if="previewDialog" :dialog="previewDialog" :type="action" :item="users.filter(item => item.id == this.id)[0]" @close="previewDialog = false" @getUsers="$emit('getUsers')"/>
    <!-- <EnrollmentModal v-if="enrollmentDialog" :dialog="enrollmentDialog" :item="users.filter(item => item.id == this.id)[0]" @close="enrollmentDialog = false"/> -->
  </section>

</template>

<script>
import UserModal from './UserModal.vue';
import EnrollmentModal from './EnrollmentModal.vue'
import UpdateModal from './UpdateModal.vue'
import { mapActions, mapState, mapMutations } from 'vuex'
import { user_management_tbl } from '@/constants/tblheaders/users';

export default {
  props: {
    users: {
      type: Array,
      default: []
    },
    loading: {
      type: Boolean,
      default: false
    },
    filterLoading: {
      type: Boolean,
      default: false
    },
    otherFilters: {
      type: Array,
      default: []
    },
    search: {
      type: String,
      default: ''
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
    totalCount: {
      type: Number,
      default: 0
    },
    totalCount: {
      type: Number,
      default: 0
    },
    filter: {
      type: Object,
      default: {
        role: null,
        class_category_id: [],
        course_id: [],
        status: null,
        created_from: '',
        created_to: ''
      }
    }
  },
  components:{
    UserModal,
    EnrollmentModal,
    UpdateModal,
  },
  data: () => ({
    user_management_tbl,
    dialog: false,
    id: null,
    previewDialog: false,
    updateDialog: false,
    enrollmentDialog: false,
    action: 'preview',
    exportLoading: false,
    deleteLoading: false,
    infoUpdateLoading: false,
    search_: '',
    minDate: '',
    maxDate: ''
  }),
  mounted() {
    this.getCoursesAction(),
    this.search_ = this.search
    
    this.minDate = this.filter.created_from
    this.maxDate = this.filter.created_to
  },
  methods: {
    ...mapActions('admin', [
      'getCoursesAction',
      'exportRegisteredUsersAction',
      'deleteUserAction',
      'updateUserStatusAction',
      'updateUserDetailPromptAction',
      'updateUsersDetailPromptAction'
    ]),

    ...mapMutations(['alertMutation']),

    del(id) {
      this.id = id
      this.dialog=true
    },

    remove() {
      this.deleteLoading = true
      this.deleteUserAction(this.id).then(() => {
        this.dialog=false
        this.alertMutation({
          show: true,
          text: `User Successfully Deleted`,
          type: "success"
        })
        this.deleteLoading = false
        this.$emit('getUsers')
      }).catch(() => {
        this.dialog=false
        this.alertMutation({
          show: true,
          text: 'Something went wrong in deleting this user from the system',
          type: "error"
        })
        this.deleteLoading = false
      })
    },

    updateStatus(id, e) {
      this.updateUserStatusAction({ user_id: id, status:  e}).then(() => {
        this.alertMutation({
          show: true,
          text: `User Status Successfully Updated`,
          type: "success"
        })
        this.$emit('getUsers')
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      })
    },

    preview(e) {
      this.action='preview'
      this.id = e
      this.previewDialog = true
    },

    update(e) {
      this.id = e

      this.updateDialog = true
    },

    updateEnrollment(e) {
      this.id = e
      this.enrollmentDialog = true
    },

    requestInfoUpdate(){
      this.infoUpdateLoading = true

      this.updateUsersDetailPromptAction().then(res => {
        this.infoUpdateLoading = false
        this.alertMutation({
          show: true,
          text: 'The system will now prompt learners to update their info.',
          type: "success"
        })
      }).catch(e => {
        console.log(e)
        this.infoUpdateLoading = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      })
    },
    
    exportTable() {
      this.exportLoading = true

      this.exportRegisteredUsersAction({search: this.search_, ...this.filter}).then(res => {
        this.exportLoading = false
        this.alertMutation({
            show: true,
            text: 'Export is being processed in the background.',
            type: "success"
        })
      }).catch(() => {
        this.exportLoading = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong in exporting',
          type: "error"
        })
      })
    }
  },
  computed: {
    ...mapState('admin', {
      courses: (state) => state.courses,
    }),

    itemsPerPage(){
      return Number(this.paginate)
    },

    paginationPaginate(){
        return String(this.paginate)
    }
  },
  watch: {
    search(val){
      this.search_ = val
    }
  }
}
</script>