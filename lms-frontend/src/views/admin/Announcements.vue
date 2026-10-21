<template>
  <section>

    <section class="d-flex flex-row justify-space-between align-center mb-2">
      <section v-if="!selected_announcement_id">
        <h4 class="poppins secondary-1--text">
            Announcements
        </h4>   
        <FormLabel :label="`Posted from ${$dateFormat.MMYY(filter.posted_from)} to ${$dateFormat.MMYY(filter.posted_to)}`" class="mb-3"/>
      </section>
      <section class="ml-auto">
        <FilterMenu
          :initialFilter="filter"
          @resetFilters="resetFilters"
          @applyFilters="() => { page = 1, updateQuery() }"
        >
          <section>
            <FormLabel :label="'COURSE'"/>
            <v-select   
              :items="courses"
              item-value="id"
              item-text="title"    
              outlined
              dense
              class="general-custom-field roboto f14 secondary-1--text fw500 mb-3"
              v-model="filter.course_id"
              multiple
              required
              hide-details="auto"
            >
            </v-select>
            <div>
              <FormLabel class="mb-1" :label="'POSTING DATE (YYYY-MM)'" />
            </div>
            <label class="caption">FROM:</label>
            <FormMonthPicker 
              :val="filter.posted_from"
              :class_="'col-12'"
              :max="maxDate"
              @setMonth="(e) => { selectedPostFromMonth = e }"
              @setYear="(e) => { selectedPostFromYear = e }"
              @apply="applyDate()"/>
            <FormLabel :label="'TO:'" class="mt-3"/>
            <FormMonthPicker 
              :val="filter.posted_to"
              :class_="'col-12'"
              :min="minDate"
              @setMonth="(e) => { selectedPostToMonth = e }"
              @setYear="(e) => { selectedPostToYear = e}"
              @apply="applyDate()"/>
          </section>
        </FilterMenu>

        <ButtonPrimary
          @click="dialog = true"
          :label="'Post'"
          :icon="'mdi-plus'"
          class="ml-1"
        />
      </section>
    </section>
      
    <LinearProgress v-if="filterLoading || loading"/>

    <v-card v-if="announcements.length==0 && !loading" class="border d-flex flex-column align-center justify-center pb-10 text-center" height="60vh">
      <div class="">
        <v-img 
          max-width="300" 
          contain
          :src="require('@/assets/default/empty_drafts.png')" />
      </div>
      <h3 class="poppins secondary-1--text fw600 my-5 text-center">You haven’t posted any announcements yet.</h3>
    </v-card>

    <v-row dense v-else>
      <v-col v-if="$vuetify.breakpoint.smAndDown ? !selected_announcement_id : true"
        :cols="(!$vuetify.breakpoint.smAndDown && selected_announcement_id) ? '4' : '12'">
          <section :style="(!$vuetify.breakpoint.smAndDown && selected_announcement_id) ? 'overflow-y: auto;' : ''">
            <div
              v-for="item in announcements"
              :key="item.id"
              @click="setAnnouncement(item.id)"
              class="mx-1">
              <PostCard 
                :id="`comment-${item.id}`"
                :item="item"
                :selected_announcement_id="selected_announcement_id"
                @remove="remove"
                @updateAnnouncement="updateAnnouncement"
                @getData="getData"
              />
            </div>
          </section>
          <FormPagination
            v-if="totalCount > 0" 
            :wrap="selected_announcement_id"
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginationPaginate"
            @page="onPageChange" 
            @paginate="onPaginateChange"/>

      </v-col>
      <v-col v-if="$vuetify.breakpoint.smAndDown ? selected_announcement_id : selected_announcement_id" 
        :cols="!$vuetify.breakpoint.smAndDown ? '8' : '12'">          
        <Post 
          :item="selected_announcement"
          :user="user"
          @remove="remove"
          :selected_announcement_id="selected_announcement_id"
          :selected_announcement_comment_id="selected_announcement_comment_id"
          @setAnnouncement="setAnnouncement"
          @updateAnnouncement="updateAnnouncement"
          @getData="() => {
            if($route.query.announcement_comment_id) {
              selected_announcement_comment_id = null
              $router.replace({ query: { ...this.$route.query, announcement_comment_id: null } });
            } else getData()
          }"
        />
      </v-col>
    </v-row>

    <PostDialog 
      :dialog="dialog" 
      :user="user"
      :courses="courses"
      @close="dialog=false"
      @posted="() => { 
          if($route.query.announcement_comment_id) {
            dialog = false
            selected_announcement_comment_id = null
            $router.replace({ query: { ...this.$route.query, announcement_comment_id: null } });
          } else {
            dialog = false
            getData()
          }
        }"
    />

    <PostUpdateDialog
      :dialog="editDialog" 
      :user="user"
      :courses="courses"
      :announcement="selected_announcement"
      @posted="() => { 
              getData()
            }"
      @close="editDialog=false"
    />

    <!-- <toaster :show="saved" @close="saved=false" :text="text"/> -->
  
    <delete-toast 
      :dialog="deleteDialog"
      type="Announcement"
      @delete="deleteAnnouncement"
      @cancel="deleteDialog=false"
    />
  
  </section>

</template>

<script>
import { mapState, mapActions, mapMutations } from 'vuex'
import Toaster from '@/components/reusable/Toaster.vue'
import Post from '@/components/admin/announcement/Post.vue'
import PostCard from '@/components/admin/announcement/PostCard.vue'
import PostDialog from '@/components/admin/announcement/PostDialog.vue'
import PostUpdateDialog from '@/components/admin/announcement/PostUpdateDialog.vue'
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';

export default {
  mixins: [searchAndPaginateMixin],
  components: {
    Post,
    PostCard,
    PostDialog,
    Toaster,
    PostUpdateDialog
  },
  computed: {
    
    paginationPaginate(){
        return String(this.paginate)
    },

   ...mapState({
     user: (state) => state.user
   }),
   ...mapState('admin', {
    courses: (state) => state.courses,
    announcements: (state) => state.announcements,
   }),
  },
  data: () => ({
    pageCount: 1,
    totalCount: 0,
    dialog: false,
    deleteDialog: false,
    editDialog: false,
    saved: false,
    text: '',
    loading: false,
    filterLoading: false,
    course_loading: false,
    idToDelete: null,
    selected_announcement: null,
    selected_announcement_id: null,
    selected_announcement_comment_id: null,
    filter: {
      course_id: [],
      posted_from: `${new Date().getFullYear()}-1`,
      posted_to: `${new Date().getFullYear()}-12`,
    },
    selectedPostFromMonth: '',
    selectedPostFromYear: '',
    selectedPostToMonth: '',
    selectedPostToYear: '',
    minDate: '',
    maxDate: ''
  }),

  mounted() {
    this.course_loading = true

    this.getTenantCoursesAction().then(res => {
      this.coursesMutation(res)
    }).finally(() => {
      this.course_loading = false
    })

    if(this.$route.query.course_id) {
      this.$route.query.course_id.forEach((id) => {
        if(!this.filter['course_id']) {
          this.filter['course_id'] = [parseInt(id)];
        } else {
          this.filter['course_id'].push(parseInt(id));
        }
      })
    } 

    if(this.$route.query.announcement_id) {
      this.selected_announcement_id = this.$route.query.announcement_id
    }

    if(this.$route.query.announcement_comment_id) {
      this.selected_announcement_comment_id = this.$route.query.announcement_comment_id
    }

    if(this.$route.query.posted_from) {
      this.selectedPostFromMonth = this.$route.query.posted_from.split('-')[1]
      this.selectedPostFromYear = this.$route.query.posted_from.split('-')[0]
    }

    if(this.$route.query.posted_to) {
      this.selectedPostToMonth = this.$route.query.posted_to.split('-')[1]
      this.selectedPostToYear = this.$route.query.posted_to.split('-')[0]
    }

    this.filter.posted_from = this.$route.query.posted_from || `${new Date().getFullYear()}-1`
    this.filter.posted_to = this.$route.query.posted_to || `${new Date().getFullYear()}-12`
    
    this.minDate = this.filter.posted_from
    this.maxDate = this.filter.posted_to

    this.getData()

  },

  methods: {
    ...mapActions('admin', ['getInstructorAllAnnouncementsAction', 'getInstructorAnnouncementAction', 'deleteAnnoucementAction']),
    ...mapActions(['getTenantCoursesAction']),

    ...mapMutations('admin', ['coursesMutation']),

    ...mapMutations(['alertMutation']),

    applyDate(){
      this.filter = {
        ...this.filter,
        posted_to: `${this.selectedPostToYear}-${this.selectedPostToMonth}`,
        posted_from: `${this.selectedPostFromYear}-${this.selectedPostFromMonth}`
      }

      this.minDate = this.filter.posted_from
      this.maxDate = this.filter.posted_to
    },

    resetFilters(){
      this.filter = {
        course_id: [],
        posted_from: `${new Date().getFullYear()}-1`,
        posted_on: `${new Date().getFullYear()}-12`,
      }

      this.minDate = this.filter.posted_from
      this.maxDate = this.filter.posted_to

      this.updateQuery()
    },

    getData() {
      if(!this.loading) {
        this.loading = true
        this.getInstructorAllAnnouncementsAction({...this.filter, page: this.page, paginate: Number(this.paginate)}).then((res) => {
          this.pageCount = res.last_page
          this.paginate = String(res.per_page)
          this.page = res.current_page
          this.totalCount = res.total
          this.loading=false

          if(this.selected_announcement_id) {
            this.getAnnouncement()
          }

          this.filter.course_id = []
          if(this.$route.query.course_id) {
            this.$route.query.course_id.forEach((id) => {
              if(!this.filter['course_id']) {
                this.filter['course_id'] = [parseInt(id)];
              } else {
                this.filter['course_id'].push(parseInt(id));
              }
            })
          } 
        }).catch(() => {
          this.loading=false
        })
      }
    },

    updateAnnouncement(e) {
      this.selected_announcement = e
      this.editDialog = true
    },

    remove(event) {
      this.deleteDialog=true
      this.idToDelete=event
    },

    deleteAnnouncement(){
      this.deleteAnnoucementAction(this.idToDelete).then(res => {
        this.deleteDialog=false

        this.alertMutation({
          show: true,
          text: res.data.message,
          type: "success"
        })
        if(this.selected_announcement_id === this.idToDelete) {
          this.setAnnouncement(null)
        } else {
          if(this.$route.query.announcement_comment_id) {
            this.selected_announcement_comment_id = null
            this.$router.replace({ query: { ...this.$route.query, announcement_comment_id: null } });
          } else this.getData()
        }
      }).catch(() => {
        this.deleteDialog=false

        this.alertMutation({
          show: true,
          text: "Something went wrong",
          type: "error"
        })
      })
    },

    setAnnouncement(id) {
      this.$router.replace({ query: { ...this.$route.query, course: this.$route.query.course, interval: this.$route.query.interval, announcement_id: id, timestamp: Date.now(), announcement_comment_id: null } });
      this.selected_announcement_id = id
      if(id) {
        this.getAnnouncement()
      } else {
        this.selected_announcement = null
      }
    },

    getAnnouncement(){
      this.filterLoading = true
      this.getInstructorAnnouncementAction(this.$route.query.announcement_id).then(res => {
        this.selected_announcement = res
        this.filterLoading = false
        if(this.$route.query.announcement_id && this.$route.query.announcement_comment_id) {
          this.selected_announcement_comment_id = null
          setTimeout(() => {
            this.$vuetify.goTo(`#comment-${this.$route.query.announcement_comment_id}`, { offset: 50})
          this.selected_announcement_comment_id = this.$route.query.announcement_comment_id
          }, 2000)
        }
      }).catch(() => {
        this.filterLoading = false
      })
    }
  },
}
</script>