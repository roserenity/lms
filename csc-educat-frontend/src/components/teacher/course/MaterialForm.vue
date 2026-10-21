<template>
  <section>
    <section class="mx-6 mb-3">
      <FormSelectObject
        :label="'MATERIAL TYPE *'"
        :items="types"
        :value.sync="material_type"
        :required="1"
        :error="errors.material_type"
      />
    </section>
    <section v-if="material_type === 'file' || material_type === 'scorm_file'" class="mx-6">
      <v-slide-y-transition>
        <div
          class="bulk-border pa-3"
          @dragover="dragover"
          @dragleave="dragleave"
          @drop="drop"
        >
          <div class="text-center">
            <v-icon color="rgba(63, 93, 208, 0.12)" size="32"
              >mdi-cloud-upload-outline</v-icon
            >
            <div class="secondary--text ml-3 fw500">
              <a
                v-if="material_type === 'file'"
                href="#"
                @click="$refs.file.click()"
                class="primary--text text-decoration-none"
                >Browse</a
              >
              <a
                v-if="material_type === 'scorm_file'"
                href="#"
                @click="$refs.scorm_file.click()"
                class="primary--text text-decoration-none"
                >Browse</a
              >
              or drag file here
              <div v-if="material_type === 'file'" class="secondary--text f12">( pdf, ppt, pptx, png, jpg, jpeg, doc, docx, mp4 )</div>
              <div v-else class="secondary--text f12">( zip )</div>
            </div>
          </div>
        </div>
      </v-slide-y-transition>
      <div class="error--text f11 mx-3 roboto" v-if="errors.scorm_file">{{ errors.scorm_file }}sas</div>
      <div class="error--text f11 mx-3 roboto" v-if="errors.file">{{ errors.file }}</div>
      <v-slide-y-transition>
        <section v-show="file.length">
          <div
            class="d-flex justify-space-between align-center my-3"
            v-for="(file, i) in file"
            :key="i"
          >
            <div class="d-flex align-center">
              <v-icon color="primary" left>mdi-file-pdf-box</v-icon>
              <div class="">
                <div class="roboto fw600 f12" style="margin-bottom: -8px">
                  {{ file.name }}
                </div>
                <span class="roboto fw500 f10 secondary--text">
                  {{ (file.size / 1000).toFixed(2) }} KB
                </span>
              </div>
            </div>
            <v-spacer />
            <v-progress-linear
              v-if="loading"
              indeterminate
              color="success"
              height="7"
              style="width: 120px"
              rounded
            />
            <v-btn icon small @click="remove(i)" v-else>
              <v-icon size="18" color="danger-1">mdi-close</v-icon>
            </v-btn>
          </div>
        </section>
      </v-slide-y-transition>
      <v-slide-y-transition>
        <section>
          <div
            class="d-flex justify-space-between align-center my-3"
            v-if="scorm_file"
          >
            <div class="d-flex align-center">
              <v-icon color="primary" left>mdi-file-pdf-box</v-icon>
              <div class="">
                <div class="roboto fw600 f12" style="margin-bottom: -8px">
                  {{ scorm_file.name }}
                </div>
                <span class="roboto fw500 f10 secondary--text">
                  {{ (scorm_file.size / 1000).toFixed(2) }} KB
                </span>
              </div>
            </div>
            <v-spacer />
            <v-progress-linear
              v-if="loading"
              indeterminate
              color="success"
              height="7"
              style="width: 120px"
              rounded
            />
            <v-btn icon small @click="removeScorm" v-else>
              <v-icon size="18" color="danger-1">mdi-close</v-icon>
            </v-btn>
          </div>
        </section>
      </v-slide-y-transition>
      <alert 
        v-if="msg.show"
        :show = "msg.show"
        :text = "msg.text"
        :type = "msg.type"
      />
    </section>
    <v-form ref="form" class="mx-6">
      <FormTextFieldRequired
        v-if="material_type === 'youtube_link'"
        :label="'YOUTUBE EMBED *'"
        :value.sync="youtube_link"
        :error="errors.youtube_link"
      />
      <FormTextFieldRequired
        v-if="material_type === 's3_link'"
        :label="'S3 LINK *'"
        :value.sync="s3_link"
        :error="errors.s3_link"
      />
      <FormTextFieldRequired
        v-if="material_type === 'gdrive_link'"
        :label="'GRIVE LINK * ( Only Photos, Videos, and PDF )'"
        :value.sync="gdrive_link"
        :error="errors.gdrive_link"
      />
      <div class="d-flex align-center" v-if="material_type === 'file'">
        <v-checkbox dense hide-details v-model="is_downloadable"/>
        <div class="roboto f14 mt-2 fw500 secondary-2--text mr-3">Tag as downloadable</div>
      </div>
    </v-form>
    <input
      type="file"
      ref="file"
      class="d-none"
      accept=".mp4,.pdf,.doc,.docx,.ppt,.png,.jpeg,.jpg"
      @change="accept"
    />
    <input
      type="file"
      ref="scorm_file"
      class="d-none"
      accept=".zip"
      @change="accept"
    />
    <v-divider class="mt-5"/>
    <v-card-actions class="d-flex align-center justify-end mx-2">
      <!-- <v-btn class="text-capitalize poppins" text @click="cancel">Cancel</v-btn> -->
      <v-btn
        class="text-capitalize poppins"
        width="140"
        color="primary"
        :loading="saving"
        @click="save"
        >Update</v-btn
      >
    </v-card-actions>
  </section>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
export default {
  props: ["item", "type"],
  data: () => ({
    material_type: null,
    saving: false,
    title: "",
    youtube_link: "",
    file: [],
    s3_link: "",
    gdrive_link: "",
    scorm_file: null,
    loading: false,
    errors: [],
    types: [
      { text: 'File', value: 'file' },
      { text: 'Youtube Link', value: 'youtube_link' },
      { text: 'S3 Link', value: 's3_link' },
      { text: 'GDrive Link', value: 'gdrive_link' },
      { text: 'Scorm File', value: 'scorm_file' }  ],
    file_error: null,
    msg: {
      show: false,
      type: '',
      text: ''
    },
    is_downloadable: 0
  }),
  mounted() {
    this.material_type = this.item.learning_material.file_type
    if(this.material_type === 'youtube') {
      this.material_type = 'youtube_link'
      this.youtube_link = this.item.learning_material.url
    } else if(this.material_type === 'gdrive_link') {
      this.gdrive_link = this.item.learning_material.url
    } else if(this.material_type === 's3_link'){
      this.material_type === 's3_link'
      this.s3_link = this.item.learning_material.url
    } else {
      this.material_type === 'scorm_file'
    } 

    this.is_downloadable = this.item.learning_material.is_downloadable
  },
  computed: {
    ...mapState({
      errs: (state) => state.errors,
    }),
    ...mapState("instructor", {
      course: (state) => state.course,
    }),
  },
  methods: {
    ...mapActions("instructor", ["updateTopicAction", "updateSubTopicAction"]),
    ...mapMutations(["alertMutation"]),

    save() {
      this.saving = true;

      this.type == "topic"
        ? this.updateTopicAction({
            course_id: this.course.id,
            id: this.item.id,
            form: this.formTopic()
          }).then(() => {
            this.saving = false;
            this.close(this.type);
          }).catch(err => {
            this.saving = false;
            this.errors = err
          })
        : this.updateSubTopicAction({
            course_id: this.course.id,
            id: this.item.id,
            form: this.formSubTopic()
          }).then(() => {
            this.saving = false;
            this.close(this.type);
          }).catch(err => {
            this.saving = false;
            this.errors = err
          })
    },

    close(text) {
      this.youtube_link = "";
      this.s3_link = ""
      this.file = []
      this.gdrive_link = "",
      this.scorm_file = ""
      this.alertMutation({
        show: true,
        text: `You have successfully updated a ${text}.`,
        type: "success"
      });
      this.$emit("close");
    },

    formTopic() {
      let form = new FormData();
      form.append("id", this.item.id);
      form.append("title", this.item.title);
      form.append("module_id", this.item.module_id);
      form.append("uploader_id", this.item.uploader_id);
      if(this.material_type === 'youtube_link') {
        form.append("youtube_link", this.youtube_link);
      } else if(this.material_type === 's3_link') {
        form.append("s3_link", this.s3_link);
      } else if(this.material_type === 'gdrive_link') {
        form.append("gdrive_link", this.gdrive_link);
      } else if(this.material_type === 'scorm_file') {
        form.append("scorm_file", this.scorm_file);
      } else {
        if (this.file.length > 0) form.append("file", ...this.file);
      }
      form.append("_method", "PUT");
      form.append("is_downloadable", this.material_type === 'file' ? (this.is_downloadable ? 1 : 0) : 0);

      return form;
    },

    formSubTopic() {
      let form = new FormData();
      form.append("id", this.item.id);
      form.append("topic_id", this.item.topic_id);
      form.append("title", this.item.title);
      form.append("sort_no", this.item.sort_no);
      if(this.material_type === 'youtube_link') {
        form.append("youtube_link", this.youtube_link);
      } else if(this.material_type === 's3_link') {
        form.append("s3_link", this.s3_link);
      } else if(this.material_type === 'gdrive_link') {
        form.append("gdrive_link", this.gdrive_link);
      } else if(this.material_type === 'scorm_file') {
        form.append("scorm_file", this.scorm_file);
      } else {
        if (this.file.length > 0) form.append("file", ...this.file);
      }
      form.append("_method", "PUT");
      form.append("is_downloadable", this.material_type === 'file' ? (this.is_downloadable ? 1 : 0) : 0);
      return form;
    },

    accept() {
      this.loading = true;
      this.errors = []
      this.file_error = null
      if(this.material_type === 'file') {
        if(!['image/png', 
            'image/jpg', 
            'image/jpeg', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/msword', 
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation', 
            'video/mp4'].includes(this.$refs.file.files[0].type)){
            this.msg = {
              show: true,
              type: 'error',
              text: 'File type not supported'
            }
            this.$refs.file.value = ''
        } else {
          this.file = [...this.$refs.file.files];
        }
      } else if(this.material_type === 'scorm_file') {
        // if(!['application/zip'].includes(this.$refs.scorm_file.files[0].type)){
        //     this.msg = {
        //       show: true,
        //       type: 'error',
        //       text: 'File type not supported'
        //     }
        //     this.$refs.scorm_file.value = ''
        // } else {
          this.scorm_file = this.$refs.scorm_file.files[0];
        // }
      }
      setTimeout(() => {
        this.loading = false;
      }, 3000);
    },

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
      this.$refs.file.files = e.dataTransfer.files;
      this.accept();
      e.currentTarget.classList.remove("bulk-border-drag");
      // console.log(this.$refs.file.files);
    },

    remove(i) {
      this.file.splice(i, 1);
    },

    removeScorm() {
      this.scorm_file = null;
      this.$refs.scorm_file.value = ''
    },
  },
};
</script>

<style></style>
