<template>
  <v-dialog persistent v-model="dialog" max-width="550">
    <v-card class="rounded-lg">
      <v-card-title class="d-flex flex-row align-center justify-space-between mb-2">
        <div  class="primary--text poppins fw600 f16">
            Add a Subtopic to: <span class="secondary--text">{{ topic.title }}</span>
        </div>
        <v-btn icon @click="cancel">
          <v-icon size="22">mdi-close</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text>
        <v-form ref="form">
          <FormTextFieldRequired
            :label="'TITLE *'"
            :value.sync="title"
            :error="errors.title"
          />
          <FormSelectArray
            :label="'MATERIAL TYPE *'"
            :items="types"
            :value.sync="material_type"
            :required="1"
            :error="errors.material_type"
            class="mb-5"
          />
          <section v-if="material_type === 'File'||material_type === 'Scorm File'">
            <div
              class="bulk-border pa-3"
              v-if="!file.length"
              @dragover="dragover"
              @dragleave="dragleave"
              @drop="drop"
            >
              <div class="text-center">
                <v-icon color="rgba(63, 93, 208, 0.12)" size="32"
                  >mdi-cloud-upload-outline</v-icon
                >
                <div class="secondary--text">
                  <a
                    v-if="material_type === 'File'"
                    href="#"
                    @click="$refs.file.click()"
                    class="primary--text text-decoration-none"
                    >Browse</a
                  >
                  <a
                    v-else
                    href="#"
                    @click="$refs.scorm_file.click()"
                    class="primary--text text-decoration-none"
                    >Browse</a
                  >
                  or drag file here
                </div>
                <div v-if="material_type === 'File'" class="secondary--text f12">( pdf, ppt, pptx, png, jpg, jpeg, doc, docx, mp4 )</div>
                <div v-else class="secondary--text f12">( zip )</div>
              </div>
            </div>
            <alert 
              v-if="msg.show"
              :show = "msg.show"
              :text = "msg.text"
              :type = "msg.type"
            />
            <!-- <div class="error--text f11 mx-3" v-if="errors.scorm_file">{{ errors.scorm_file[0] }}</div> -->
            <v-slide-y-transition>
              <div
                class="bulk-border pa-3"
                v-if="material_type === 'File'"
                v-show="file.length"
                @dragover="dragover"
                @dragleave="dragleave"
                @drop="drop"
              >
                <div class="text-center">
                    <v-icon color="rgba(63, 93, 208, 0.12)" size="32"
                      >mdi-cloud-upload-outline</v-icon
                    >
                    <div class="secondary--text">
                      <a
                        href="#"
                        @click="$refs.file.click()"
                        class="primary--text text-decoration-none"
                        >Browse</a
                      >
                      or drag file here
                    </div>
                    <div v-if="material_type === 'File'" class="secondary--text f12">( pdf, ppt, pptx, png, jpg, jpeg, doc, docx, mp4 )</div>
                    <div v-else class="secondary--text f12">( zip )</div>
                  </div>
              </div>
              <!-- <div class="error--text f11 mx-3" v-if="errors.file">{{ errors.file[0] }}</div> -->
            </v-slide-y-transition>
            <v-slide-y-transition>
              <section v-show="file.length">
                <div
                  class="d-flex justify-space-between align-center my-3"
                  v-for="(file, i) in file"
                  :key="i"
                >
                  <div class="d-flex align-center">
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
            <div class="error--text f11" v-if="errors.scorm_file">{{ errors.scorm_file[0] }}</div>
            <div class="error--text f11" v-if="errors.file">{{ errors.file[0] }}</div>
          </section>

          <section v-if="material_type === 'Youtube Link'">
            <FormTextFieldRequired
              :label="'YOUTUBE EMBED *'"
              :value.sync="youtube_link"
              :error="errors.youtube_link"
            />
          </section>
          <section v-if="material_type === 'S3 Link'">
            <FormTextFieldRequired
              :label="'S3 LINK *'"
              :value.sync="s3_link"
              :error="errors.s3_link"
            />
          </section>
          <section v-if="material_type === 'GDrive Link'">
            <FormTextFieldRequired
              :label="'GRIVE LINK * ( Only Photos, Videos, and PDF )'"
              :value.sync="gdrive_link"
              :error="errors.gdrive_link"
            />
          </section>
          <div class="error--text f11 mx-3" v-if="file_error">{{ file_error }}</div>
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
        </v-form>
        <div class="d-flex align-center" v-if="material_type === 'File'">
          <v-checkbox dense hide-details v-model="is_downloadable"/>
          <div class="roboto f14 mt-2 fw500 secondary-2--text mr-3">Tag as downloadable</div>
        </div>
      </v-card-text>
      <v-divider />
      <v-card-actions class="d-flex align-center justify-end mx-2">
        <v-btn class="text-capitalize poppins" text @click="cancel">Cancel</v-btn>
        <v-btn
          class="text-capitalize poppins"
          width="140"
          color="primary"
          :loading="saving"
          @click="save"
          >Upload</v-btn
        >
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import FormLabel from "@/components/reusable/forms/FormLabel.vue";
import { mapActions, mapMutations, mapState } from "vuex";
export default {
  name: "AddSubtopic",
  props: ["dialog", "topic", "course_id"],
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
    types: ['File', 'Youtube Link', 'S3 Link', 'GDrive Link', 'Scorm File'],
    file_error: null,
    msg: {
      show: false,
      type: '',
      text: ''
    },
    is_downloadable: 0
  }),
  watch: {
    dialog(val) {
      this.resetAll()
    }
  },
  computed: {
    ...mapState({
      errs: (state) => state.errors,
    }),
  },
  methods: {
    ...mapActions("instructor", ["addSubtopicAction"]),
    ...mapMutations(["alertMutation"]),

    resetAll(){
      this.title = ""
      this.errors = [];
      this.youtube_link = ""
      this.s3_link = ""
      this.gdrive_link = ""
      this.scorm_file = null
      this.file = []
      this.material_type = ''
      this.file_error = null
    },

    save() {
      this.file_error = null
      this.errors=[]
      if(this.material_type === 'Scorm File' && !this.scorm_file){
        this.file_error = "Scorm file (.zip) is required"
      } else if(this.material_type === 'File' && this.file.length === 0){
        this.file_error = "File is required"
      } else if(this.$refs.form.validate()){
        this.saving = true;

        let form = new FormData();

        form.append("course_id", this.topic.course_id);
        form.append("module_id", this.topic.module_id);
        form.append("topic_id", this.topic.id);
        form.append("uploader_id", this.topic.uploader_id);
        form.append("is_downloadable", this.material_type === 'File' ? (this.is_downloadable ? 1 : 0) : 0 );
        form.append("title", this.title);
        if(this.material_type === 'Youtube Link') {
          form.append("youtube_link", this.youtube_link);
        } else if(this.material_type === 'S3 Link') {
          form.append("s3_link", this.s3_link);
        } else if(this.material_type === 'GDrive Link') {
          form.append("gdrive_link", this.gdrive_link);
        } else if(this.material_type === 'Scorm File') {
          form.append("scorm_file", this.scorm_file);
        }else {
          if (this.file.length > 0) form.append("file", ...this.file);
        }

        this.addSubtopicAction({
          course_id: this.course_id,
          module_id: this.topic.module_id,
          form: form,
        }).then(() => {
          this.saving = false;

          if (this.$errors()) return (this.errors = this.errs);
          this.alertMutation({
            show: true,
            text: this.material_type === 'Scorm File' ? "SCORM file is uploading. Please refresh in a few minutes to check the status." : "Subtopic Successfully Added",
            type: "success"
          });
          this.saving = false;
          this.title = ""
          this.errors = []
          this.youtube_link = ""
          this.s3_link = ""
          this.gdrive_link = ""
          this.file = []
          this.scorm_file = null
          this.material_type = ''
          this.$emit("close")
        }).catch((err) => {
          this.saving = false
          this.errors = err
        });
      }
      
    },

    changeType(e){
      if(e === 'File') {
        this.s3_link = ''
        this.youtube_link = ''
        this.gdrive_link = ''
        this.scorm_file = null
      } else if(e === 'Youtube Link') {
        this.s3_link = ''
        this.file = []
        this.gdrive_link = ''
        this.scorm_file = null
      } else if(e === 'S3 Link') {
        this.youtube_link = ''
        this.file = []
        this.gdrive_link = ''
        this.scorm_file = null
      } else if(e === 'GDrive Link') {
        this.youtube_link = ''
        this.file = []
        this.s3_link = ''
        this.scorm_file = null
      } else if(e === 'Scorm File') {
        this.youtube_link = ''
        this.file = []
        this.s3_link = ''
        this.gdrive_link = ''
      }
    },
    
    accept() {
      this.msg = {
        show: false,
        type: '',
        text: ''
      }
      this.loading = true;
      if(this.material_type === 'File') {
          this.file = [...this.$refs.file.files];
      } else if(this.material_type === 'Scorm File') {
          this.scorm_file = this.$refs.scorm_file.files[0];
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

    cancel() {
      this.errors = [];
      this.file = [];
      this.$emit("close");
    },
  },
};
</script>
