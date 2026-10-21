<template>
  <v-sheet class="pa-5 custom-border border">
    
    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-7' : 'col-lg-7'" class="d-flex flex-wrap align-center">

      <label class="caption ma-1 mr-3">COURSE CODE:</label>
      <v-text-field 
        outlined
        dense
        class="general-custom-field roboto f14 fw500 secondary--text"
        :disabled="$isReadOnly.isReadOnly(course.status, permission)"
        v-model="course.course_code"
        v-on:change="(e) => {
          course.course_code = e
          if(course?.course_code.length <= 25) {
            updateCourse()
          }
        }"
        counter="25"
      />

    </div>
    
    <v-divider class="my-3" />
    <label class="caption mx-4">CERTIFICATE SETTINGS </label>
    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'" class="d-flex flex-row align-center">
      <label class="caption ma-1 mr-3">TYPE OF LEARNING:</label>
      <v-row align="center">
        <v-col cols="12" sm="9">
          <v-select
            outlined
            dense
            class="general-custom-field roboto f14 secondary-1--text fw500"
            :items="modes"
            v-model="course.type_of_learning"
            v-on:change="updateCourse()"
            :disabled="$isReadOnly.isReadOnly(course.status, permission)"
            hide-details
          >
          </v-select>
        </v-col>
      </v-row>
    </div>
    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'" class="d-flex flex-row align-center">
      <label class="caption ma-1 mr-3">TRAINING CREDITS:</label>
      <v-row align="center">
        <v-col cols="12" sm="9">
          <v-select
            outlined
            dense
            class="general-custom-field roboto f14 secondary-1--text fw500"
            item-value="id"
            item-text="name"
            :items="training_credits"
            v-model="course.training_credits"
            v-on:change="updateCourse()"
            :disabled="$isReadOnly.isReadOnly(course.status, permission)"
            hide-details
          >
          </v-select>
        </v-col>
      </v-row>
    </div>
    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'" class="d-flex flex-row align-center">
      <label class="caption ma-1 mr-3">CREDIT TRAINING HOURS:</label>
      <input
        type="number"
        class="input-sm"
        min="1"
        style="width: 130px"
        v-model="setCreditTrainingHours"
        v-on:change="updateTrainingHours($event)"
        :disabled="$isReadOnly.isReadOnly(course.status, permission)"
      />
      <div class="caption ma-1">hrs </div>
    </div>
    <v-divider class="my-3" />
    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'" class="d-flex flex-wrap align-center py-0"> 
      <section class="d-flex flex-row align-center">
        <label class="caption ma-1 mr-3">COST:</label>
        <label class="caption ma-1">PHP</label>
        <v-text-field
          dense
          hide-details
          min="1"
          type="number"
          v-model="course.price"
          v-on:change="updatePrice"
          class="secondary-1--text f12 fw600 poppins col-7"
          :disabled="$isReadOnly.isReadOnly(course.status, permission) || Boolean(course.is_free)">
        >
        </v-text-field>
      </section>
      <div class="d-flex align-center">
        <v-checkbox @change="updateIsFree" v-model="course.is_free" :disabled="$isReadOnly.isReadOnly(course.status, permission)"/>
        <label class="caption">Mark this course as <b class="primary--text">FREE</b></label>
      </div>
    </div>
    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'" class="d-flex flex-row align-center">
      <label class="caption ma-1 mr-3">TRAINING HOURS:</label>
      <input
        type="number"
        class="input-sm"
        style="width: 130px"
        min="1"
        v-model="setTrainingHours"
        v-on:change="updateTrainingHours($event)"
        :readonly="$isReadOnly.isReadOnly(course.status, permission)"
      />
      <div class="caption ma-1">hrs </div>
    </div>
    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'" class="ma-1">
      <label class="caption">FACILITATOR :</label>
      <v-radio-group :disabled="$isReadOnly.isReadOnly(course.status, permission)" hide-details class="pt-0 mt-2" v-model="setFacilitator">
        <v-radio :value="true" dense>
          <template v-slot:label>
            <label class="caption">Assign myself as instructor</label>
          </template>
        </v-radio>
        <v-radio :value="false" dense>
          <template v-slot:label>
              <v-text-field 
                outlined
                dense
                class="general-custom-field roboto f14 fw500 secondary--text"
                :disabled="$isReadOnly.isReadOnly(course.status, permission)"
                v-model="facilitator"
                v-on:change="(e) => {
                  course.facilitator = e
                  updateCourse()
                }"
                hide-details
              />
          </template>
        </v-radio>
      </v-radio-group>
    </div>
    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'">
      <label class="caption">COURSE DESCRIPTION *</label>
      <RichTextEditor v-if="course.status !== 'PUBLISH' || loading"
        :disabled="Boolean($isReadOnly.isReadOnly(course.status, permission))"
        :value="course.description"
        @setValue="(e) => {course.description = e, setDescription(e)}"
        :showSaveButton="true"
      />
      <div v-else v-html="course.description ? $dompurifier(course.description) : ''" class="my-3 f13 secondary-2--text"></div>
    </div>
    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'">
      <label class="caption">CLASS TAGGING *</label>
      <v-row align="center">
        <v-col cols="12" sm="9">
          <v-select
            outlined
            dense
            class="general-custom-field roboto f14 secondary-1--text fw500"
            :items="classes"
            item-value="id"
            item-text="class_name"
            v-model="classCategories"
            v-on:change="tagClass($event)"
            :disabled="$isReadOnly.isReadOnlyNotCourseOwner(course.status, permission)"
            hide-details
            :loading="loading"
          >
            <template slot="item" slot-scope="data">
              <v-list-item-content :name="data.item.class_name">
                <v-list-item-title class="roboto f14 fw500">
                  <span class="secondary--text">{{ data.item.class_code }}-</span>
                  <span class="secondary-3--text">{{ data.item.class_name }}</span>
                </v-list-item-title>
              </v-list-item-content>
            </template>
            <template slot="selection" slot-scope="data">
              <div class="roboto f14 fw500 my-1">
                <span class="secondary--text">{{ data.item.class_code }}-</span>fdfd
                <span class="secondary-3--text">{{ data.item.class_name }}&nbsp; </span>
              </div>
            </template>
          </v-select>
        </v-col>
        <v-col cols="12" sm="2">
          <v-btn dense outlined color="primary" class="f12 poppins text-capitalize" @click="dialog = true">
            <v-icon left class="">mdi-eye-outline</v-icon>
            See enrollees
          </v-btn>
        </v-col>
      </v-row>
    </div>

    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'">
      <label class="caption">COURSE AVAILABILITY</label>
      <div class="d-flex flex-wrap align-center mr-3 mb-3">
        <label class="caption mr-3">FROM:</label>
        <input
          type="date"
          class="input-sm"
          placeholder="mm/dd/yyyy"
          style="width: 130px"
          v-model="setStartDate"
          @blur="updateAvailabilityStart($event)"
          :readonly="$isReadOnly.isReadOnly(course.status, permission)"
        />
      </div>
      <div class="d-flex flex-wrap align-center">
        <label class="caption mr-3">UNTIL:</label>
        <input
          type="date"
          class="input-sm"
          placeholder="mm/dd/yyyy"
          style="width: 130px"
          v-model="setEndDate"
          @blur="updateAvailabilityEnd($event)"
          :readonly="$isReadOnly.isReadOnly(course.status, permission)"
        />
      </div>
    </div>

    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'">
      <label class="caption mb-2" v-if="course.status === 'PUBLISH'">COURSE THUMBNAIL: </label>
      <label class="caption" v-else>UPLOAD THUMBNAIL: 
        <v-btn dense color="primary" class="poppins f12 mr-2" @click="cropDialog = true" :disabled="$isReadOnly.isReadOnly(course.status, permission)">
          SELECT        
        </v-btn>
      </label>
      <PhotoUpload :modal="cropDialog" @close="cropDialog = false" :id="course.id" :type="'course'" :role="'instructor'"/>
      <div v-if="course.status === 'PUBLISH'">
        <div v-if="!course.image" class="secondary-2--text poppins fw500 ma-1 f12"> No course thumbnail assigned</div>
        <div v-else>
          <img
            width="250"
            height="132"
            :src="course.image ? course.image.url : `${asset_path}/default-course-bg.png`"
            class="d-flex align-center text-center"
          />
        </div>
      </div>

      <circular v-if="thumbnailLoading" class="mx-auto my-3"/>
      
      <section class="mt-3 d-flex flex-column align-center justify-center" v-else-if="course.image && course.status !== 'PUBLISH'">
        <!-- <v-btn icon class="ml-auto" @click="remove">
          <v-icon>
            mdi-close-circle
          </v-icon>
        </v-btn> -->
          <img
            width="250"
            height="132"
            :src="course.image ? course.image.url : `${asset_path}/default-course-bg.png`"
          />
        
        <a 
          @click="remove"
          class="caption error--text text-decoration-none"> Remove </a>
      </section>
      <!-- <div
        v-if="!$isReadOnly.isReadOnly(course.status, permission)" 
        class="bulk-border mt-2 py-3 d-flex flex-column align-center justify-center"
        @dragover="dragover"
        @dragleave="dragleave"
        @drop="drop"
      >
        <div class="d-flex align-center">
          <v-icon color="rgba(63, 93, 208, 0.12)" size="22"
            >mdi-cloud-upload-outline</v-icon
          >
          <div class="secondary--text f12 mx-2">
            <a
              href="#"
              @click="$refs.image.click()"
              class="primary--text text-decoration-none"
            >
              Browse
            </a>
            or drag file here
          </div>
          <div class="secondary--text f12">( .jpg, .jpeg, .png )</div>
        </div>
      </div> -->
      <alert 
          v-if="msg.show"
          :show = "msg.show"
          :text = "msg.text"
          :type = "msg.type"
      />
    </div>
    <div v-if="$isReadOnly.isReadOnlyNotCourseOwnerPublishedUnpublished(course.status, permission)" class="d-flex flex-row align-center" :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'">
      <v-btn v-if="course.status !== 'DRAFT'" dense color="primary" class="poppins f12 mr-2" @click="share()" :loading="shareLoading">
        <v-icon size="18">mdi-share-outline</v-icon>
        Share
      </v-btn>
    </div>
    <div :class="$vuetify.breakpoint.mobile ? 'col-lg-12' : activity_log ? 'col-lg-9' : 'col-lg-7'">
      <div class="roboto text-wrap f10 secondary-2--text">Note: Sharing courses will allow your co-instructors to edit the whole course.</div>
    </div>
    <input
      type="file"
      ref="image"
      class="d-none"
      accept=".jpeg,.png,.jpg"
      v-on:change="accept"
    />
    <EnrolleeList 
      :dialog="dialog" 
      @close="dialog = false" 
      :count="getEnrolleeCount" 
      :enrollees="getEnrollees"
      :class_categories="course.class_categories"/>
    <ShareCourseDialog :dialog="share_dialog" @close="share_dialog = false" :course_id="course.id" :shared_to_users="course.shared_to_users" @getShareUsers="share()"/>
  </v-sheet>
</template>

<script>
import Editor from "@tinymce/tinymce-vue";
import { mapActions, mapMutations, mapState } from "vuex";
import EnrolleeList from "./EnrolleeList.vue";
import ShareCourseDialog from "./ShareCourseDialog.vue";
import PhotoUpload from "../../main/PhotoUpload.vue";

export default {
  props: ["course", "classes", "activity_log", "permission", "loading"],
  data: () => ({
    show: false,
    dialog: false,
    cropDialog: false,
    form: new FormData(),
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    classLoading: false,
    thumbnailLoading: false,
    shareLoading: false,
    share_dialog: false,
    certificateLoading: false,
    trainingCreditsLoading: false,
    is_free: false,
    mce_key: `${process.env.VUE_APP_TINYMCE_KEY}`,
    facilitator: null,
    msg: {
      show: false,
      type: '',
      text: ''
    },
    modes: ['E-LEARNING', 'BLENDED', 'MICROLEARNING'],
    programs: ['Leadership and Management Training', 'Foundational/Leadership and Management Training', 'Technical Training', 'Foundational Training'],
    certificates: [],
    certificate_template: null
  }),
  components: {
    editor: Editor,
    EnrolleeList,
    ShareCourseDialog,
    PhotoUpload
  },
  mounted() {
    this.trainingCreditsLoading = true
    this.getTrainingCreditsAction().finally(() => {
      this.trainingCreditsLoading = false
    })
  },
  computed: {
    ...mapState({
      tenant: (state) => state.tenant
    }),

    ...mapState('instructor', {
      certificate_templates: (state) => state.certificate_templates,
      training_credits: (state) => state.training_credits,
    }),

    classCategories:{
      get(){
        if(this.course) {
          return this.course.class_categories.length > 0 ? this.course.class_categories[0].id : this.course.class_categories
        }
      },
      set(val){
        this.course.class_categories = val 
      }
    },

    setTrainingHours: {
      get() {
        if(this.course.training_hours === '' || this.course.training_hours > 0) {
          return this.course.training_hours
        } else {
          this.course.training_hours = 1
          return 1
        }
      },
      set(val) {
        this.course.training_hours = val
      }
    },

    setCreditTrainingHours: {
      get() {
        if(this.course.credit_training_hours === '' || this.course.credit_training_hours > 0) {
          return this.course.credit_training_hours
        } else {
          this.course.credit_training_hours = 1
          return 1
        }
      },
      set(val) {
        this.course.credit_training_hours = val
      }
    },

    setStartDate() {
      return this.course.available_until ? this.course.available_until.start_date ? this.course.available_until.start_date : null : null
    },

    setEndDate() {
      return this.course.available_until ? this.course.available_until.end_date ? this.course.available_until.end_date : null : null
    },

    setFacilitator: {
      get() {
        if(this.course) {
          if(Object.keys(this.course).length > 0) return false
          if( this.course.facilitator === null ) {
            this.course.facilitator = null
            this.facilitator = null
            return true
          } else {
            this.facilitator = this.course.facilitator
            return false
          }
        }
      },

      set(val){
        if(val) {
          this.course.facilitator = null
          this.facilitator = null
          this.updateCourse()
        } else {
          this.course.facilitator = this.facilitator
        }
      }
    },

    getEnrolleeCount() {
      if(this.loading || !this.course) return 0
      return Object.keys(this.course).length > 0 ? this.course.enrollments.length : 0

    },
    getEnrollees(){
      let _enrollees = []
      this.course.enrollments.forEach(item => {
        if(item.user) {
          _enrollees.push(
            {
              name: `${item.user.first_name ? this.$userFormat.name(item.user.first_name) : 'User'} ${item.user.last_name ? this.$userFormat.name(item.user.last_name) : 'Account'} ${item.user.suffix ? item.user.suffix : ''}`,
              date_enrolled: `${this.$dateFormat.mmDDyy(item.created_at)}`,
              course_id: item.course_id
            }
          )
        }
      })
      return _enrollees
    }
  },
  methods: {
    ...mapActions("instructor", ["showCourseAction", "getAssessmentsAction", "getClassesAction", "uploadThumbnailCourseAction", "tagClassCourseAction", "updateCourseDescriptionAction", "updateCourseUpdatedAt", "getInstructors", "removeCourseImage", "getCourseSharedInstructors", "getCertificateTemplatesAction", "updateCourseCertificateTemplateAction", "getTrainingCreditsAction"]),

    ...mapMutations(['alertMutation']),

    dragover(e) {
      e.preventDefault();
      e.currentTarget.classList.add("bulk-border-drag");
    },
    dragleave(e) {
      e.preventDefault();
      e.currentTarget.classList.remove("bulk-border-drag");
    },

    drop(e) {
      e.preventDefault();
      this.$refs.image.files = e.dataTransfer.files;
      this.accept();
      e.currentTarget.classList.remove("bulk-border-drag");
    },
    accept() {
      this.msg = {
          show: false,
          type: '',
          text: ''
      }
      if(!['image/png', 'image/jpeg', 'image/jpg'].includes(this.$refs.image.files[0].type)) {
        this.msg = {
            show: true,
            type: 'error',
            text: `File not supported`
        }
        this.$refs.image.value = ''
      } else {
        const image = [...this.$refs.image.files];
        this.form.append("image_file", ...image);
        this.form.append("_method", 'PUT');

        this.thumbnailLoading = true
        this.uploadThumbnailCourseAction({
          id: this.course.id,
          form: this.form,
        }).then(()=>{
          this.thumbnailLoading = false
          this.alertMutation({
            show: true,
            text: 'Course successfully updated',
            type: "success"
          })
        }).catch(e => {
          this.thumbnailLoading = false
        })
      }

    },

    tagClass(e) {
      this.classLoading = true
      this.tagClassCourseAction({
        id: this.course.id,
        class_category_id: e,
      }).then(()=>{
        this.showCourseAction(this.$route.params.id).then(() => {
          this.getAssessmentsAction({
            course_id: this.course.id,
          }).then(()=>{
            this.getClassesAction().then(()=>{
              this.classLoading = false
              this.alertMutation({
                show: true,
                text: 'Course successfully updated',
                type: "success"
              })
            })
          })
        });
      })
    },


    setDescription(e){
      this.updateCourseInfo({
        ...this.course,
        start_date: this.course.available_until ? this.course.available_until.start_date ? this.course.available_until.start_date : null : null,
        end_date: this.course.available_until ? this.course.available_until.end_date ? this.course.available_until.end_date : null : null,
        description: e,
      })
    },

    updatePrice(e) {
      this.updateCourseInfo({
        ...this.course,
        price: parseInt(e) > 0 ? parseFloat(e) : null,
        is_free: parseInt(e) > 0 ? false : true,
        start_date: this.course.available_until ? this.course.available_until.start_date ? this.course.available_until.start_date : null: null,
        end_date: this.course.available_until ? this.course.available_until.end_date ? this.course.available_until.end_date : null : null,
      })
    },

    updateIsFree() {
      if(this.course.is_free) {
        this.course.price = null
      }

      this.updateCourseInfo({
        ...this.course, 
        start_date: this.course.available_until ? this.course.available_until.start_date ? this.course.available_until.start_date : null : null,
        end_date: this.course.available_until ? this.course.available_until.end_date ? this.course.available_until.end_date : null : null,
      })
    },

    updateAvailabilityStart(e){
      if(this.course.available_until) {
        if(!this.course.available_until.end_date) {
          this.course.available_until = {
            start_date: e.target.value,
            end_date: e.target.value
          }
        } else {
          if(e.target.value > this.course.available_until.end_date) {
            this.course.available_until = {
              start_date: e.target.value,
              end_date: e.target.value
            }
          } else {
            this.course.available_until = {
              start_date: e.target.value,
              end_date: this.course.available_until.end_date
            }
          }
        }
      } else {
        this.course.available_until = {
            start_date: e.target.value,
            end_date: e.target.value
          }
      }

      this.updateCourseInfo({
        ...this.course,
        start_date: e.target.value,
        end_date: this.course.available_until.end_date,
      })
    },

    updateAvailabilityEnd(e){
      if(this.course.available_until) {
        if(!this.course.available_until.start_date) {
          this.course.available_until = {
            start_date: e.target.value,
            end_date: e.target.value
          }
        } else {
          if(e.target.value < this.course.available_until.start_date) {
            this.course.available_until = {
              start_date: e.target.value,
              end_date: e.target.value
            }
          } else {
            this.course.available_until = {
              start_date: this.course.available_until.start_date,
              end_date: e.target.value
            }
          }
        }
      } else {
          this.course.available_until = {
            start_date: e.target.value,
            end_date: e.target.value
          }
      }

      this.updateCourseInfo({
        ...this.course,
        start_date: this.course.available_until.start_date,
        end_date: e.target.value,
      })
    },
    
    updateTrainingHours(e){
      if(e.target.value !== '') {
        this.updateCourseInfo({
          ...this.course,
          start_date: this.course.available_until ? this.course.available_until.start_date ? this.course.available_until.start_date : null: null,
          end_date: this.course.available_until ? this.course.available_until.end_date ? this.course.available_until.end_date : null : null,
          training_hours: e.target.value > 0 ? Math.ceil(e.target.value) : 1,
        })
      }
    },

    updateCourse(){
      this.updateCourseInfo({
          ...this.course,
          start_date: this.course.available_until ? this.course.available_until.start_date ? this.course.available_until.start_date : null: null,
          end_date: this.course.available_until ? this.course.available_until.end_date ? this.course.available_until.end_date : null : null,
      })
    },

    updateCourseInfo(info){
      this.updateCourseDescriptionAction(info).then(()=>{
        this.alertMutation({
          show: true,
          text: 'Course successfully updated',
          type: "success"
        })
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      })
    },

    share() {
      this.shareLoading = true
      this.getCourseSharedInstructors(this.course.id).then(()=>{
        this.share_dialog =  true
      }).finally(() => {
        this.shareLoading = false
      })
    },

    remove(){
      this.thumbnailLoading = true
      this.removeCourseImage({ id: this.course.id }).then(()=>{
        this.course.image = null
        this.$refs.file.value = ''
        this.alertMutation({
          show: true,
          text: 'Course successfully updated',
          type: "success"
        })
        this.thumbnailLoading = false
      }).catch(()=>{
        this.thumbnailLoading = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      })
    },

    getCertificates() {
      this.certificateLoading = true
      this.getCertificateTemplatesAction({ type_of_certificate: this.course.type_of_learning === 'MICROLEARNING' ? 'E-LEARNING' : this.course.type_of_learning }).then((res) => {
        this.certificateLoading = false
      }).catch(() => {
        this.certificateLoading = false
        this.alertMutation({
            show: true,
            text: 'Something went wrong in fetching certificate templates',
            type: "error"
        })
      })
    },
    
    setCertificateTemplate() {
      this.certificateLoading = true
      this.updateCourseCertificateTemplateAction({ id: this.course.id, certificate_template_id: this.certificate_template }).then((res) => {
        this.certificateLoading = false
        this.alertMutation({
          show: true,
          text: 'Course successfully updated',
          type: "success"
        })
      }).catch(() => {
        this.certificateLoading = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong in setting certificate template',
          type: "error"
        })
      })
    }
  },

  watch: {
    facilitator(val) {
      if(val) {
        if(val.length === 0) {
          this.facilitator = null
        }
      }
    },
    course(val) {
      this.getCertificates()
      if(this.course.certificate_template) {
        this.certificate_template = this.course.certificate_template.certificate_template_id
      } else {
        this.certificate_template = null
      }
    },
  }
};
</script>
