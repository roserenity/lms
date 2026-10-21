<template>
  <section>
    <v-dialog persistent v-model="dialog" max-width="650">
      <v-card class="rounded-lg">
        
        <v-card-title class="d-flex flex-row justify-space-between">
          <div>
            <div class="secondary--text primary-font fw600 f14">{{ data.hasOwnProperty('topic_id') ? 'SUBTOPIC' : 'TOPIC' }}</div>
          </div>
          <v-btn icon @click="close()">
            <v-icon size="22">mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        
        <div class="d-flex flex-row justify-space-between px-6 mb-2">
          <div :style="onEdit ? 'width: 100%;' : ''" class="d-flex flex-row align-center poppins secondary-1--text fw600">
            <v-btn class="mr-1" icon dense small @click="onEdit = !onEdit">
              <v-icon>mdi-pencil-outline</v-icon>
            </v-btn>
            <div style="width: 100%;" v-if="onEdit" >
              <v-text-field 
                v-model="data.title" 
                class="poppins f14 secondary-1--text fw600 general-custom-field" 
                v-on:change="setTitle" 
                :maxlength="255" 
                counter
                :rules="[v => v === '' || v.length <= 255 || 'Max 255 characters', v => !!v || 'This is required']"></v-text-field>
            </div>
            <span v-if="!onEdit" class="primary--text primary-font fw600 ellipsis" style="max-width: 90% !important;">
              {{ data.title }}
            </span>
          </div>

          <v-btn :loading="previewLinkGenerateLoading" @click="regenerateLink()" dense small class="mx-1 primary ml-auto" flat v-if="data.learning_material && ['scorm_file'].includes(data.learning_material.file_type)" >
              Regenerate preview link
          </v-btn>
        </div>
        <section v-if="data.learning_material && !update" flat class="px-6 mb-3">
          <vimeo v-if="['mp4', 'youtube'].includes(data.learning_material.file_type) && showVimeo"
            :uri="data.learning_material.url"
            :type="data.learning_material.file_type"
            :data="data"
            class="fade"
          />
          <div id="container-frame" class="container-frame" v-else>
              <iframe 
                  v-if="['ppt', 'pptx', 'docx'].includes(data.learning_material.file_type)"
                  id="material-frame"
                  :src="`https://view.officeapps.live.com/op/embed.aspx?src=${data.learning_material.url}`"
                  class="responsive-iframe"
                  allowfullscreen
                  webkitallowfullscreen="true"
                  mozallowfullscreen="true"
              ></iframe>
              <v-img 
                  v-else-if="['png', 'jpg', 'jpeg'].includes(data.learning_material.file_type)"
                  id="material-iframe"
                  :src="data.learning_material.url"
              />
              <iframe 
                  v-else-if="['scorm_file'].includes(data.learning_material.file_type)"
                  id="material-frame"
                  :src="data.scorm ? data.scorm.preview_link : data.learning_material.url"
                  class="responsive-iframe"
                  allowfullscreen
                  webkitallowfullscreen="true"
                  mozallowfullscreen="true"
              ></iframe>
              <iframe 
                  v-else
                  id="material-iframe"
                  :src="data.learning_material.url"
                  class="responsive-iframe"
                  allowfullscreen
                  webkitallowfullscreen="true"
                  mozallowfullscreen="true"
              ></iframe>  
          </div>
        </section>
        <v-card-text v-if="!update" class="d-flex flex-row">
          <v-spacer />
          <v-btn
            v-if="!['scorm_file'].includes(data.learning_material.file_type)"
            text
            class="text-capitalize primary-font f14 fw500 no-space"
            color="primary"
            @click="update = true"
          >
            <v-icon left small>mdi-pencil-outline</v-icon>
            Update Material
          </v-btn>
        </v-card-text>
        <MaterialForm v-if="update" :item="material" :type="type" @close="close()"/>
      </v-card>
    </v-dialog>
  </section>
</template>

<script>
import { mapMutations, mapActions } from "vuex";
import MaterialForm from "./MaterialForm.vue";
export default {
  props: ["dialog", "data", "type"],
  components: {
    MaterialForm,
  },
  data: () => ({
    update: false,
    material: null,
    showVimeo: false,
    previewLinkGenerateLoading: false,
    onEdit: false,
  }),
  watch: {
    dialog(val){
      const access_payload = localStorage.getItem('access_payload');
      if(val){
        this.material = JSON.parse(JSON.stringify(this.data))
        setTimeout(() => {
            this.showVimeo = true
            localStorage.setItem('access_payload', access_payload);
        }, 1000)
      } else {
        this.showVimeo = false
        localStorage.setItem('access_payload', access_payload);
        this.onEdit = false
      }
    },
  },
  mounted(){
    console.log('mouneted view mat')
  },
  created(){
    console.log('created view mat')
  },
  methods: {
    ...mapMutations(["playerMutation", "alertMutation"]),
    ...mapActions('instructor', ['updateScormCoursePreviewLinkAction', 'updateTopicAction', 'updateSubTopicAction']),

    close() {
      this.playerMutation({
        action: "stop",
        duration: 0,
      });
      
      this.update = false;
      this.$emit("close");
    },

    regenerateLink() {
      this.previewLinkGenerateLoading = true
      this.updateScormCoursePreviewLinkAction({ id: this.data.scorm.id }).then(res => {
        this.previewLinkGenerateLoading = false
        this.$emit('updatePreviewLink', res)
        this.alertMutation({
          show: true,
          text: 'Preview link successfully updated',
          type: "success"
        })
      }).catch(() => {
        this.previewLinkGenerateLoading = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
        this.close()
      })
    },
    
    setTitle(e) {
      if(this.data.hasOwnProperty('topic_id')) {
        let form = new FormData();
        form.append("id", this.data.id);
        form.append("title", this.data.title);
        form.append("topic_id", this.data.topic_id);
        form.append("course_id", this.data.course_id);
        form.append("_method", "PUT");
        this.updateSubTopicAction({id: this.data.id, course_id: this.data.course_id,form: form}).then(()=>{
          this.alertMutation({
              show: true,
              text: 'Course content successfully updated',
              type: "success"
          })
        })
      } else {
        let form = new FormData();
        form.append("id", this.data.id);
        form.append("title", this.data.title);
        form.append("module_id", this.data.module_id);
        form.append("_method", "PUT");
        this.updateTopicAction({id: this.data.id, form: form}).then(()=>{
          this.alertMutation({
              show: true,
              text: 'Course content successfully updated',
              type: "success"
          })
        })
      }
    }

  },
};
</script>

<style scoped>
    .container-frame {
        position: relative;
        overflow: hidden;
        width: 100%; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
    }

    .responsive-iframe {
        height: 54vh;
    }

    @media only screen and (max-width: 768px){
    .responsive-iframe {
        height: 54vh;
        width: 100%;
    }
}

</style>
