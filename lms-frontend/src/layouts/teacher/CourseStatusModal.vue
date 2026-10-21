<template>
  <section>
    <v-dialog v-if="!review" max-width="480" v-model="dialog.status" persistent>
      <v-card class="" v-if="!review">
        <div class="" v-if="!publishing">
          <div class="d-flex justify-end align-center px-5 pt-3">
            <v-btn icon @click="close">
              <v-icon size="18">mdi-close</v-icon>
            </v-btn>
          </div>
          <div class="px-5 pt-3 pb-7">
            <h3 class="primary--text poppins fw600 text-center">
              Are you sure you want to {{ dialog.action }} this course?
            </h3>
            <!-- <div class="secondary-3--text poppins text-center my-1 f14">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            </div> -->
          </div>
          <v-divider />
          <div class="d-flex justify-end align-center px-5 py-2">
            <div class="">
              <!-- <v-btn text class="text-capitalize poppins f14" @click="preview()"
                >Preview</v-btn
              > -->
              <ButtonPrimary 
                v-if="dialog.action === 'publish'"
                @click="publish"
                :label="'Publish'"
              />
              <ButtonPrimary 
                v-else
                @click="unpublish"
                :label="'Unpublish'"
              />
            </div>
          </div>
        </div>
        <div class="pb-10" v-else>
          <div class="" v-if="!published">
            <v-img
              class="d-flex align-end"
              contain
              aspect-ratio="1.7"
              :src="require(`@/assets/images/course-animation-1.gif`)"
            />
              <h3 class="text-center primary--text poppins" v-if="dialog.action === 'publish'">Publishing Course...</h3>
              <h3 class="text-center primary--text poppins" v-else>Unpublishing Course...</h3>
          </div>
          <div class="text-center pt-7" v-else>
            <v-icon color="success" size="46">mdi-check-circle</v-icon>
            <h3 class="poppins primary--text fw600 mt-3" v-if="dialog.action === 'publish'">
              Course Published Successfully!
            </h3>
            <h3 class="poppins primary--text fw600 mt-3" v-else>
              Course Unpublished Successfully!
            </h3>
          </div>
        </div>
      </v-card>
    </v-dialog>
    <PreviewDialog
      v-else
      :data="course"
      :dialog="dialog.status"
      @close="review = false"
    />
  </section>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
import PreviewDialog from "../../components/teacher/course/PreviewDialog.vue";
export default {
  props: ["dialog"],
  components: { PreviewDialog },
  data: () => ({
    review: false,
    publishing: false,
    published: false,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
  }),
  computed: {
    ...mapState("instructor", {
      course: (state) => state.course,
    }),
  },
  methods: {
    ...mapMutations([ "alertMutation"]),
    ...mapMutations("instructor", ["statusCourseMutation", "courseMutation"]),
    ...mapActions("instructor", ["getInstructorDashboardCoursesAction", "updateStatusCourseAction", "showCourseAction", "getPublishedCoursesAction", "getUnpublishedCoursesAction", "getDraftsCoursesAction"]),

    close() {
      this.statusCourseMutation({ id: "", status: false, action: "" });
    },

    preview() {
      this.showCourseAction(this.dialog.id).then(() => {
        this.review = true;
      });
    },

    success() {
      setTimeout(() => {
        this.published = true;
        setTimeout(() => {
          this.close();
          this.published = false;
          this.publishing = false;
        }, 3000);
      }, 4000);
    },

    error() {
      this.published =  false
      this.publishing = false;
      this.alertMutation({
        show: true,
        text: 'Something went wrong in updating course status',
        type: "error"
      })
    },

    getData(data){
      let type = this.$route.name.split(' ')[1]
      if (type === 'Published') {
        this.getPublishedCoursesAction({ paginate: Number(this.$route.query.paginate), page: this.$route.query.page}).then(() => {
          this.success()
        }).catch(() => {
          this.error()
        });
      } else if (type === 'Unpublished') {
        this.getUnpublishedCoursesAction({ paginate: Number(this.$route.query.paginate), page: this.$route.query.page }).then(() => {
          this.success()
        }).catch(() => {
            this.error()
        });
      } else if (type === 'Drafted') {
        this.getDraftsCoursesAction({ paginate: Number(this.$route.query.paginate), page: this.$route.query.page }).then(res => {
          this.success()
        }).catch(() => {
          this.error()
        });
      } else if (this.$route.name === 'Instructor Dashboard') {
        this.getInstructorDashboardCoursesAction(this.$route.query).then(res => {
          this.success()
        }).catch(() => {
          this.error()
        });
      } else {
        this.success()
        this.courseMutation({ ...this.course, status: data.status });
      }
    },

    publish() {
      this.publishing = true;
      this.updateStatusCourseAction({
        id: this.dialog.id,
        status: "publish",
      }).then(res => {
        this.getData(res)
      }).catch(() => {
        this.error()
      });
    },

    unpublish() {
      this.publishing = true;
      this.updateStatusCourseAction({
        id: this.dialog.id,
        status: "unpublish",
      }).then(res => {
        this.getData(res)
      }).catch(() => {
        this.error()
      });
    },
  },
};
</script>
