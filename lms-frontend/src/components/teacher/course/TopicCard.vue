<template>
  <v-card outlined class="mx-3 b5 sortable" :class="status == 'PUBLISH' && 'ml-5'">
    <div class="pt-3 col-xl-9 col-lg-10">
      <label :class="status != 'PUBLISH' ? 'ml-9' : 'ml-3'" class="caption">TOPIC</label>
      <section class="d-flex align-start" >
        <v-icon class="mt-2 mr-1" v-if="!$isReadOnly.isReadOnly(status, permission)">
          mdi-drag
        </v-icon>
        <section class="general-custom-div pointer-cursor d-flex flex-column ">
          <v-text-field
            dense
            outlined
            hide-details
            readonly
            :prepend-inner-icon="
              topic.learning_material
                ? topic.learning_material.file_type == 'youtube'
                  ? 'mdi-youtube'
                  : topic.learning_material.file_type == 'pdf'
                  ? 'mdi-file-pdf-box'
                  : topic.learning_material.file_type == 'mp4'
                  ? 'mdi-file-video-outline'
                  : topic.learning_material.file_type == 'scorm_file'
                  ? 'mdi-archive-outline'
                  : (topic.learning_material.file_type == 'jpg' || topic.learning_material.file_type == 'png' || topic.learning_material.file_type == 'png')
                  ? 'mdi-image'
                  : 'mdi-folder-question-outline'
                : ''
            "
            class="pointer-cursor no-outline-custom-field roboto f14 fw500 secondary--text"
            v-model="topic.title"
            >
            <template v-slot:append>
              <section v-if="topic.learning_material.file_type == 'scorm_file' && topic.scorm && topic.scorm.status !== 'COMPLETE'">
              <!-- <section v-if="topic.learning_material.file_type == 'scorm_file' && topic.scorm && topic.scorm.status !== 'COMPLETE'" class="d-flex align-center mx-3 mt-1"> -->
                <v-chip dense x-small :color="topic.scorm.status === 'ERROR' ? 'error' : 'secondary-4'" class="f10 px-2">{{  topic.scorm.status === 'ERROR' ? 'Scorm file failed to upload' : 'Scorm file uploading ...' }}</v-chip>
                
                <v-tooltip top>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon dense small
                      v-bind="attrs"
                      v-on="on"
                      @click="getUploadProgress"
                      :loading="loading"
                      v-if="topic.scorm.status !== 'ERROR'">
                      <v-icon>
                        mdi-sync
                      </v-icon>
                    </v-btn>
                  </template>
                  <span class="f12">Fetch upload progress</span>
                </v-tooltip>
                <v-menu v-if="topic.scorm.status == 'ERROR'" offset-y transition="slide-y-transition" bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon v-bind="attrs" v-on="on" style="margin: -6px" :loading="topicLoading" dense>
                      <v-icon> mdi-dots-vertical </v-icon>
                    </v-btn>
                  </template>
                  <v-list dense class="custom-border">
                    <v-list-item
                      v-if="!$isReadOnly.isReadOnly(status, permission)"
                      link
                      @click="$emit('deleteTopic', { item: topic, type: 'Topic' })"
                    >
                      <v-list-item-title class="roboto f14 d-flex align-center">
                        <v-icon size="22" class="mr-1">mdi-delete-outline</v-icon>
                        <div class="">Delete Topic</div>
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </section>
              <v-menu v-else offset-y transition="slide-y-transition" bottom>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon v-bind="attrs" v-on="on" style="margin: -6px" :loading="topicLoading" dense>
                    <v-icon> mdi-dots-vertical </v-icon>
                  </v-btn>
                </template>
                <v-list dense class="custom-border">
                  <v-list-item v-if="!$isReadOnly.isReadOnly(status, permission)" @click="$emit('addAssessment', { type: 'add', module_id: topic.module_id, is_topic: true, topic_or_subtopic_id: topic.id, topic_or_subtopic_title: topic.title, title: 'Untitled' })">
                    <v-list-item-title class="roboto f14 d-flex align-center">
                      <v-icon size="22" class="mr-1">mdi-notebook-edit-outline</v-icon>
                      <div class="">Add Assessment</div>
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item link @click="viewMaterial()" v-if="topic.learning_material.file_type != 'scorm_file' || (topic.learning_material.file_type === 'scorm_file' && topic.scorm.status === 'COMPLETE')">
                    <v-list-item-title class="roboto f14 d-flex align-center">
                      <v-icon size="22" class="mr-1">mdi-eye-outline</v-icon>
                      <div class="">View Material</div>
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item
                    v-if="!$isReadOnly.isReadOnly(status, permission)"
                    link
                    @click="$emit('deleteTopic', { item: topic, type: 'Topic' })"
                  >
                    <v-list-item-title class="roboto f14 d-flex align-center">
                      <v-icon size="22" class="mr-1">mdi-delete-outline</v-icon>
                      <div class="">Delete Topic</div>
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
          </v-text-field>
          
          <section v-for="item in topic.topic_has_assessments" :key="item.id">
            <TopicAssessmentCard :item="item" @viewAssessment="viewAssessment" :topic_or_subtopic_id="topic.id" :topic_or_subtopic_title="topic.title" :status="status" :permission="permission" @updateModules="$emit('updateModules')"/>
          </section>
        </section>
      </section>
      <draggable
        v-model="topic.sub_topics"
        ghost-class="ghost"
        @end="sortSubtopics(topic.sub_topics)"
        :disabled="$vuetify.breakpoint.mobile ? true : $isReadOnly.isReadOnly(status, permission)" 
      >
        <transition-group type="transition" name="flip-list">
          <div class="sub-topic mt-5" v-for="(item, s) in topic.sub_topics" :key="item.id">
            <SubTopicCard 
              :subtopic="item" 
              :module_id="topic.module_id" 
              @addAssessment="addAssessment" 
              @viewAssessment="viewAssessment" 
              @deleteSubtopic="deleteSubtopic" 
              :loading="subtopicLoading" 
              :status="status" 
              :permission="permission"  
              @updateSubtopic="(e) => {updateSubtopic(s, e)}"
              @updateSubtopicPreviewLink="(e) => { $emit('updateSubtopicPreviewLink', { index: index, link: e})}"
              @updateModules="$emit('updateModules')"/>
          </div>
        </transition-group>
      </draggable>

      <v-btn
        v-if="!$isReadOnly.isReadOnly(status, permission)"
        @click="$emit('newSubtopic', topic)"
        text
        small
        color="primary"
        class="roboto fw600 f12 text-capitalize mt-5 mb-2"
      >
        <v-icon left>mdi-subdirectory-arrow-right</v-icon>
        Sub Topic
      </v-btn>
    </div>
    <ViewMaterial :dialog="dialog" :data="topic" @close="dialog = false" type="topic"  @updatePreviewLink="(link) => { $emit('updateTopicPreviewLink', link)}"/>
  </v-card>
</template>

<script>
import SubTopicCard from "./SubTopicCard.vue";
import draggable from "vuedraggable";
import ViewMaterial from "./ViewMaterial.vue";
import TopicAssessmentCard from "./TopicAssessmentCard.vue";
import { mapActions, mapMutations } from "vuex";

export default {
  props: ["status", "topic", "topicLoading", "subtopicLoading", "permission"],
  components: { SubTopicCard, draggable, ViewMaterial, TopicAssessmentCard },
  data: () => ({
    dialog: false,
    loading: false,
  }),
  methods: {
    ...mapMutations([
      'alertMutation'
    ]),

    ...mapActions("instructor", [
      "getScormCourseUploadProgressAction",
    ]),

    updateSubtopic(index, e){
      this.$emit('updateSubtopic', { index: index, scorm: e})
    },

    getUploadProgress() {
      this.loading = true
      this.getScormCourseUploadProgressAction({jobId : this.topic.scorm.job_id}).then(res => {
        this.loading = false
        this.alertMutation({
          show: true,
          text: `Upload status is ${res.status}`,
          type: "success"
        })
        this.$emit('updateTopic', res)
      }).catch(() => {
        this.loading = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong in fetching the upload progress',
          type: "error"
        })
      })
    },
    deleteSubtopic(e) {
      this.$emit("deleteSubtopic", e);
    },
    sortSubtopics(e) {
      let _ids = [];
      e.forEach((item) => {
        _ids.push(item.id);
      });
      this.$emit("sortSubTopic", _ids);
    },
    viewMaterial() {
      this.dialog = true;
    },
    viewAssessment(data) {
      this.$emit('viewAssessment', data)
    },
    addAssessment(data) {
      this.$emit('addAssessment', data)
    }
  },
};
</script>
