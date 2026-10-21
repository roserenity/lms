<template>
  <div class="mt-2 d-flex align-start sortable">
    <v-icon v-if="status != 'PUBLISH'" class="mt-2 mr-5">mdi-drag</v-icon>
    <v-icon class="mt-2 mr-2">mdi-subdirectory-arrow-right</v-icon>
    <section class="dashed-custom-div d-flex flex-column ">
      <v-text-field
        dense
        outlined
        hide-details
        :prepend-inner-icon="
          subtopic.learning_material.file_type == 'youtube'
            ? 'mdi-youtube'
            : subtopic.learning_material.file_type == 'pdf'
            ? 'mdi-file-pdf-box'
            : subtopic.learning_material.file_type == 'mp4'
            ? 'mdi-file-video-outline'
            : subtopic.learning_material.file_type == 'scorm_file'
            ? 'mdi-archive-outline'
            : (subtopic.learning_material.file_type == 'jpg' || subtopic.learning_material.file_type == 'png' || subtopic.learning_material.file_type == 'png')
            ? 'mdi-image'
            : 'mdi-folder-question-outline'
        "
        class="pointer-cursor no-outline-custom-field roboto f14 fw500 secondary--text"
        v-model="subtopic.title"
        readonly
      >
        <template v-slot:append>
          <section v-if="subtopic.learning_material.file_type == 'scorm_file' && subtopic.scorm && subtopic.scorm.status !== 'COMPLETE'">
            <v-chip dense x-small :color="subtopic.scorm.status === 'ERROR' ? 'error' : 'secondary-4'" class="f10 px-2">{{  subtopic.scorm.status === 'ERROR' ? 'Scorm file failed to upload' : 'Scorm file uploading ...' }}</v-chip>
            
            <v-tooltip top>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon dense small
                  v-bind="attrs"
                  v-on="on"
                  @click="getUploadProgress"
                  :loading="loading1"
                  v-if="subtopic.scorm.status !== 'ERROR'">
                  <v-icon>
                    mdi-sync
                  </v-icon>
                </v-btn>
              </template>
              <span class="f12">Fetch upload progress</span>
            </v-tooltip>
            <v-menu v-if="subtopic.scorm.status == 'ERROR'" offset-y transition="slide-y-transition" bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon v-bind="attrs" v-on="on" style="margin: -6px" :loading="loading" dense>
                  <v-icon> mdi-dots-vertical </v-icon>
                </v-btn>
              </template>
              <v-list dense class="custom-border">
                <v-list-item
                  link
                  v-if="!$isReadOnly.isReadOnly(status, permission)"
                  @click="$emit('deleteSubtopic', { item: subtopic, type: 'Sub-topic' })"
                >
                  <v-list-item-title class="roboto f14 d-flex align-center">
                    <v-icon size="22" class="mr-1">mdi-delete-outline</v-icon>
                    <div class="">Delete Sub-topic</div>
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </section>
          <v-menu v-else offset-y transition="slide-y-transition" bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon v-bind="attrs" v-on="on" style="margin: -6px" :loading="loading" dense>
                <v-icon size="20"> mdi-dots-vertical </v-icon>
              </v-btn>
            </template>
            <v-list dense class="custom-border">
              <v-list-item v-if="!$isReadOnly.isReadOnly(status, permission)" @click="$emit('addAssessment', { type: 'add', module_id: module_id, is_topic: false, topic_or_subtopic_id: subtopic.id, topic_or_subtopic_title: subtopic.title, title: 'Untitled' })">
                <v-list-item-title class="roboto f14 d-flex align-center">
                  <v-icon size="22" class="mr-1">mdi-notebook-edit-outline</v-icon>
                  <div class="">Add Assessment</div>
                </v-list-item-title>
              </v-list-item>
              <!-- <v-list-item v-if="!$isReadOnly.isReadOnly(status, permission)" @click="$emit('addGroupActivity', { type: 'add', module_id: module_id, is_topic: false, topic_or_subtopic_id: subtopic.id, topic_or_subtopic_title: subtopic.title, title: 'Untitled' })">
                <v-list-item-title class="roboto f14 d-flex align-center">
                  <v-icon size="22" class="mr-1">mdi-puzzle-plus-outline</v-icon>
                  <div class="">Add Group Activity</div>
                </v-list-item-title>
              </v-list-item> -->
              <v-list-item link @click="dialog = true" v-if="subtopic.learning_material.file_type != 'scorm_file' || (subtopic.learning_material.file_type === 'scorm_file' && subtopic.scorm.status === 'COMPLETE')">
                <v-list-item-title class="roboto f14 d-flex align-center">
                  <v-icon size="22" class="mr-1">mdi-eye-outline</v-icon>
                  <div class="">View Material</div>
                </v-list-item-title>
              </v-list-item>
              <v-list-item
                link
                v-if="!$isReadOnly.isReadOnly(status, permission)"
                @click="$emit('deleteSubtopic', { item: subtopic, type: 'Sub-topic' })"
              >
                <v-list-item-title class="roboto f14 d-flex align-center">
                  <v-icon size="22" class="mr-1">mdi-delete-outline</v-icon>
                  <div class="">Delete Sub-topic</div>
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-text-field>
      
      <section v-for="item in subtopic.sub_topic_has_assessments">
        <SubTopicAssessmentCard @viewAssessment="viewAssessment" :item="item" :topic_or_subtopic_id="subtopic.id" :topic_or_subtopic_title="subtopic.title" :status="status" :permission="permission"  @updateModules="$emit('updateModules')"/>
      </section>
    </section>

    <ViewMaterial :dialog="dialog" :data="subtopic" @close="dialog = false" type="subtopic" @updatePreviewLink="(link) => { $emit('updateSubtopicPreviewLink', link)}"/>
  </div>
</template>

<script>
import SubTopicAssessmentCard from "./SubTopicAssessmentCard.vue";
import ViewMaterial from "./ViewMaterial.vue";
import { mapActions, mapMutations } from "vuex";

export default {
  props: ["status", "subtopic", "loading", "module_id", "permission"],
  components: { ViewMaterial, SubTopicAssessmentCard },
  data: () => ({
    dialog: false,
    loading1: false,
  }),
  methods: {
    ...mapMutations([
      'alertMutation'
    ]),

    ...mapActions("instructor", [
      "getScormCourseUploadProgressAction",
    ]),

    getUploadProgress() {
      this.loading1 = true
      this.getScormCourseUploadProgressAction({jobId : this.subtopic.scorm.job_id}).then(res => {
        this.loading1 = false
        this.alertMutation({
          show: true,
          text: `Upload status is ${res.status}`,
          type: "success"
        })
        this.$emit('updateSubtopic', res)
      }).catch(() => {
        this.loading1 = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong in fetching the upload progress',
          type: "error"
        })
      })
    },

    viewAssessment(data) {
      this.$emit('viewAssessment', data)
    },
  }
};
</script>
