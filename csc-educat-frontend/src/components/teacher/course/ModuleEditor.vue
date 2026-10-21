<template>
  <section id="module-editor">
    <LinearProgress v-if="loading2" />
    <v-row justify="center">
      <v-col cols="12" lg="9">
        <section>
          <draggable
            v-model="modules"
            @end="sortModules(modules)"
            ghost-class="ghost"
            v-if="modules.length > 0"
            :disabled="$vuetify.breakpoint.mobile ? true : $isReadOnly.isReadOnly(course.status, permission)" 
          >
            <transition-group type="transition" name="flip-list" >
              <section
                v-for="(item, m) in modules"
                :key="item.id">
                <v-expansion-panels
                  flat
                  accordion
                  class="sortable"
                >
                  <v-expansion-panel class="custom-border border mb-2">
                    <v-expansion-panel-header class="d-flex flex-column">
                      <div class="col-12 ma-0 pa-0 d-flex align-center justify-space-between">
                        <div class="col-md-4">
                          <FormLabel :label="`MODULE TITLE *`"/>
                          <v-text-field
                            @click.stop
                            dense
                            v-model="item.title"
                            @focus="readonly = true"
                            @focusout="readonly = false"
                            v-on:change="updateModule($event, item)"
                            class="secondary-1--text f14 fw600 poppins"
                            :disabled="$isReadOnly.isReadOnly(course.status, permission)"
                            counter
                            :rules="[v => v === '' || v.length <= 255 || 'Max 255 characters', v => !!v || 'This is required']"
                            :maxlength="255"
                          >
                          </v-text-field>
                        </div>
                        <v-spacer />
                        <ModuleOptions
                          :module="item"
                          :status="course.status"
                          v-if="!$isReadOnly.isReadOnly(course.status, permission)"
                          @addPrerequisite="prerequisite"
                          @showLock="showLock"
                          @deleteModule="deleteItem"
                          @addModuleEvaluation="addModuleEvaluation"
                        />
                      </div>
                      <div class="col-12 ma-0 pa-0">
                        <div class="col-md-8">
                          <FormLabel :label="`MODULE DESCRIPTION`"/>
                          <v-textarea 
                            class="mb-5 mt-3 secondary-1--text f14 fw600 poppins" 
                            dense 
                            v-model="item.description"
                            v-on:change="updateDesc($event, item)"
                            rows="2"
                            :disabled="$isReadOnly.isReadOnly(course.status, permission)"
                            counter
                            :rules="[v => v === '' || v.length <= 255 || 'Max 255 characters']"
                            :maxlength="255"
                            />
                        </div>
                      </div>
                      <div class="col-12 ma-0 pa-0">
                        <div class="col-md-8">
                          <FormLabel :label="`CONTACT PERSON`"/>
                          <v-text-field
                            @click.stop
                            dense
                            outlined
                            hide-details
                            v-model="item.contact_person"
                            @focus="readonly = true"
                            @focusout="readonly = false"
                            v-on:change="updateModule($event, item)"
                            class="secondary-1--text f14 poppins"
                            :disabled="$isReadOnly.isReadOnly(course.status, permission)"
                          >
                          </v-text-field>
                        </div>
                      </div>
                      <div class="col-12 ma-0 pa-0">
                        <div class="col-md-8">
                          <v-row dense>
                            <v-col>
                              <FormLabel :label="`VERSION`"/>
                              <v-text-field
                                @click.stop
                                dense
                                outlined
                                hide-details
                                v-model="item.version"
                                @focus="readonly = true"
                                @focusout="readonly = false"
                                v-on:change="updateModule($event, item)"
                                class="secondary-1--text f14 poppins"
                                :disabled="$isReadOnly.isReadOnly(course.status, permission)"
                              >
                              </v-text-field>
                            </v-col>
                            <v-col>
                              <FormLabel :label="`LANGUAGE`"/>
                              <FormSelectArray 
                                :class_="'secondary-1--text f14 poppins'"
                                :items="languages"
                                :loading="languageLoading"
                                :multiple="true"
                                :value.sync="item.language"
                                v-on:change="updateModule($event, item)"
                              />
                            </v-col>
                          </v-row>
                        </div>
                      </div>
                      <div class="col-12 ma-0 pa-0" v-for="field in fields" :key="field.input_field_label">
                        <div class="col-md-8">
                          <FormLabel :label="field.input_field_label"/>
                          <v-text-field
                            v-if="field.input_field_type === 'text_field'"
                            @click.stop
                            dense
                            outlined
                            hide-details
                            v-model="item.data[field.input_field_name]"
                            @focus="readonly = true"
                            @focusout="readonly = false"
                            v-on:change="updateModule($event, item)"
                            class="secondary-1--text f14 poppins"
                            :disabled="$isReadOnly.isReadOnly(course.status, permission)"
                            :rules="[v => v === '' || v.length <= 255 || 'Max 255 characters']"
                            :maxlength="255"
                          />
                          <v-textarea
                            v-if="field.input_field_type === 'text_area'"
                            @click.stop
                            dense
                            outlined
                            v-model="item.data[field.input_field_name]"
                            @focus="readonly = true"
                            @focusout="readonly = false"
                            v-on:change="updateModule($event, item)"
                            class="secondary-1--text f14 poppins"
                            :disabled="$isReadOnly.isReadOnly(course.status, permission)"
                            rows="2"
                            counter
                            :rules="[v => v === '' || v.length <= 255 || 'Max 255 characters']"
                            :maxlength="255"
                          />
                          <FormDatePicker 
                            v-if="field.input_field_type === 'calendar'" 
                            :val="item.data[field.input_field_name]"
                            @apply="(e) => {
                              item.data[field.input_field_name] = e
                              updateModule($event, item)
                            }"
                            :disabled="$isReadOnly.isReadOnly(course.status, permission)"
                          />

                          <FormSelectArray 
                            v-if="field.input_field_type === 'dropdown'"
                            :items="field.data ? JSON.parse(field.data) : []"
                            :value.sync="item.data[field.input_field_name]"
                            v-on:change="updateModule($event, item)"
                            :disabled="$isReadOnly.isReadOnly(course.status, permission)"
                            :multiple="Boolean(field.is_multiple)"
                          />
                          
                          <v-text-field
                            v-if="field.input_field_type === 'number'"
                            dense
                            hide-details
                            outlined
                            min="1"
                            type="number"
                            v-model="item.data[field.input_field_name]"
                            v-on:change="updateModule($event, item)"
                            :disabled="$isReadOnly.isReadOnly(course.status, permission)"
                          />
                        </div>
                      </div>
                      <!-- kung ano yung prerequisite module nya -->
                      <!-- <div class="col-12 ma-0 px-2 py-0 d-flex align-start mb-3" v-if="!item.module_is_prerequisite">
                        <v-checkbox @click.stop :ref="`checkbox-${item.id}`" dense hide-details class="mt-0" 
                          :value="checked[item.id]"
                          @change="check($event, item.id)"/>
                        <section class="align-center mt-1">
                          <label class="caption mr-1">SELECT <span class="error--text">PREREQUISITES</span>:</label>
                          <v-select
                            :items="getModulesNotPrerequisite(item.id, item.prerequisites)"
                            item-text="title"
                            item-value="id"
                            dense
                            :disabled="!checked[item.id]"
                            :value="getPrerequisite(item.prerequisites)"
                            class="general-custom-field roboto f14 fw500 secondary--text"
                            hide-details
                            multiple
                            :loading="loading1[item.id]"
                            v-on:change="updatePrerequisite($event, item.id)"
                            :menu-props="{ bottom: true, offsetY: true }"
                          >
                            <template v-slot:item="{item, attrs, on}">
                                <v-list-item-content >
                                    <v-list-item-title class="roboto f14 fw500">
                                      <span>{{ item.title }}</span>
                                    </v-list-item-title>
                                </v-list-item-content>
                            </template>
                          </v-select>
                        </section>
                      </div> -->

                    </v-expansion-panel-header>
                    <v-expansion-panel-content class="module">
                      <v-divider />
                      <draggable
                        v-model="item.module_content"
                        ghost-class="ghost"
                        @end="sortModuleContent(item.module_content)"
                        class="col-xl-7 col-lg-9"
                        :disabled="$vuetify.breakpoint.mobile ? true : $isReadOnly.isReadOnly(course.status, permission)"
                      >
                        <transition-group type="transition" name="flip-list">
                          <div
                            class="py-2"
                            v-for="(module_content, i) in item.module_content"
                            :key="module_content.id"
                            :class="i == 1 ? 'mt-3' : 'mb-2'"
                          >
                            <TopicCard
                              v-if="module_content.contentable_type === 'App\\Models\\Topic'"
                              :topic="module_content.contentable"
                              :status="course.status"
                              :permission="permission"
                              @newSubtopic="newSubtopic"
                              @deleteTopic="deleteItem"
                              @deleteSubtopic="deleteItem"
                              @addAssessment="addAssessment"
                              @viewAssessment="viewAssessment"
                              @sortSubTopic="sortSubTopic"
                              @updateModules="updateModules"
                              @updateTopic="(e) => updateTopic(m, i, e)"
                              @updateSubtopic="(e) => updateSubtopic(m, i, e.index, e.scorm)"
                              @updateTopicPreviewLink="(e) => updateTopicPreviewLink(m, i, e)"
                              @updateSubtopicPreviewLink="(e) => updateSubtopicPreviewLink(m, i, e.link)"
                              :loading="loading"
                            />
                            <AssessmentCard 
                              v-if="module_content.contentable_type === 'App\\Models\\Assessment'"
                              :item="module_content.contentable" 
                              @viewAssessment="viewAssessment" 
                              :topic_or_subtopic_id="module_content.contentable.id" 
                              :topic_or_subtopic_title="null" 
                              :status="status" 
                              :permission="permission" 
                              @updateModules="updateModules"/>
                            <GroupActivityCard 
                              v-if="module_content.contentable_type === 'App\\Models\\GroupActivity'"
                              :item="module_content.contentable" 
                              @viewGroupActivity="viewGroupActivity" 
                              :topic_or_subtopic_id="module_content.contentable.id" 
                              :topic_or_subtopic_title="null" 
                              :status="status" 
                              :permission="permission" 
                              @updateModules="updateModules"/>
                          </div>
                        </transition-group>
                      </draggable>
                      <div>
                        <label :class="course.status === 'PUBLISH' ? 'mx-8' : 'mx-6'" class="caption" v-if="item.module_evaluations.length > 0">MODULE EVALUATION</label>
                        <ModuleEvaluationCard 
                          v-for="(module_evaluation, i) in item.module_evaluations"
                          :key="i"
                          :evaluation="module_evaluation" 
                          :status="course.status" 
                          :course_id="course.id"
                          :permission="permission"
                          @viewModuleEvaluation="viewModuleEvaluation" 
                          @lockEvaluation="lockModuleEvaluation"
                          @deleteModuleEvaluation="deleteModuleEvaluation"/>
                        <!-- <ModuleEvaluationCard :evaluation="evaluation" :status="course.status" @viewModuleEvaluation="viewModuleEvaluation"/> -->
                      </div>
                      <div class="ma-2">
                        <v-btn
                          v-if="!$isReadOnly.isReadOnly(course.status, permission)"
                          text
                          small
                          color="primary"
                          class="poppins fw600 f12 text-capitalize ml-2"
                          @click="newTopic(item)"
                        >
                          <v-icon left size="18">mdi-plus</v-icon>Add Topic
                        </v-btn>
                        <v-btn
                          v-if="!$isReadOnly.isReadOnly(course.status, permission)"
                          text
                          small
                          color="primary"
                          class="poppins fw600 f12 text-capitalize ml-2"
                          @click="newAssessment(item)"
                          :loading="assessmentLoading"
                        >
                          <v-icon left size="18">mdi-plus</v-icon>Add Assessment
                        </v-btn>
                        <v-btn
                          v-if="!$isReadOnly.isReadOnly(course.status, permission)"
                          text
                          small
                          color="primary"
                          class="poppins fw600 f12 text-capitalize ml-2"
                          @click="newGroupActivity(item)"
                          :loading="groupActivityLoading"
                        >
                          <v-icon left size="18">mdi-plus</v-icon>Add Group Activity
                        </v-btn>
                      </div>
                    </v-expansion-panel-content>
                  </v-expansion-panel>
                </v-expansion-panels>
              </section>
            </transition-group>
          </draggable>
          <div v-else class="roboto f15 fw500 my-5 mx-1 secondary--text">
            No modules available
            <v-divider />
          </div>
        </section>
        

        <v-card
          class="custom-border border pt-3 mb-5"
          v-if="course_evaluations.length > 0"
        >
        <label class="caption fw500 mx-6 " v-if="course_evaluations.length > 0">COURSE EVALUATION</label>
          <CourseEvaluationCard 
            v-for="(course_evaluation, i) in course_evaluations"
            :key="i"
            :evaluation="course_evaluation" 
            :status="course.status" 
            :permission="permission"
            @viewCourseEvaluation="viewCourseEvaluation" 
            @lockEvaluation="lockCourseEvaluation" 
            @deleteCourseEvaluation="deleteCourseEvaluation"/>
        </v-card>

        <div class="my-5" v-if="!$isReadOnly.isReadOnly(course.status, permission)">
          <v-btn
            :loading="addingModule"
            outlined
            color="primary"
            class="poppins f12 fw600 text-capitalize mr-2"
            @click="addModule"
            dense
          >
            <v-icon left>mdi-plus</v-icon>
            Add Module
          </v-btn>
          <v-btn
            outlined
            color="primary"
            class="poppins f12 fw600 text-capitalize"
            @click="addCourseEvaluation"
            dense
            :loading="addingCourseEval"
            v-if="!$isReadOnly.isReadOnly(course.status, permission)"
          >
            <v-icon left>mdi-plus</v-icon>
            Course Evaluation
          </v-btn>
        </div>
      </v-col>
      <v-col cols="12" lg="3" class="">
        <CourseNavigator :modules="modules" v-if="modules.length > 0" :status="course.status"/>
      </v-col>
    </v-row>
    <EvaluationLock
      :evaluation="evaluation"
      :dialog="showEvaluationLockDialog"
      @close="() => { showEvaluationLockDialog = false, evaluation = null }"
    />
    <ModuleLock
      :dialog="showLockedDialog"
      @success="() => { updateCourseUpdatedAt(course.id), updateModules()}"
      @close="() => { showLockedDialog = false, module = null  }"
      :module="module"
    />
    <AddTopic :dialog="addingTopic" :module="module" @close="addingTopic = false" />
    <AddSubtopic
      :dialog="addingSubtopic"
      :topic="topic"
      :course_id="course.id"
      @close="addingSubtopic = false"
    />
    <DeleteDialog
      :dialog="removeItem"
      :item="itemToDelete"
      :course_id="course.id"
      :type="deleteType"
      @close="() => {
        removeItem = false
        if(deleteType === 'Module') {
          init()
        }
      }"
    />
    <!-- <AddPrerequisite
      :dialog="addingPrerequisite"
      :module="module"
      :modules="modules"
      @close="addingPrerequisite = false"
    /> -->
    <delete-toast 
      :dialog="removeCourseEvaluationDialog"
      type="Course Evaluation"
      @delete="() => { removeCourseEvaluation(), evaluation = null }"
      @cancel="() => { removeCourseEvaluationDialog=false, evaluation = null }"
    />
    <delete-toast 
      :dialog="removeModuleEvaluationDialog"
      type="Module Evaluation"
      @delete="() => { removeModuleEvaluation(), evaluation = null }"
      @cancel="() => { removeModuleEvaluationDialog=false, evaluation = null }"
    />
  </section>
</template>

<script>
import draggable from "vuedraggable";
import { mapActions, mapState, mapMutations } from "vuex";
import TopicCard from "@/components/teacher/course/TopicCard.vue";
import CourseNavigator from "@/components/teacher/course/CourseNavigator.vue";
import ModuleOptions from "@/components/teacher/course/ModuleOptions.vue";
import ModuleLock from "@/components/teacher/course/ModuleLock.vue";
import AddTopic from "@/components/teacher/course/AddTopic.vue";
import AddSubtopic from "@/components/teacher/course/AddSubtopic.vue";
import DeleteDialog from "@/components/teacher/course/DeleteDialog.vue";
import AddPrerequisite from "@/components/teacher/course/AddPrerequisite.vue";
import CourseEvaluationCard from "@/components/teacher/course/CourseEvaluationCard.vue";
import ModuleEvaluationCard from "@/components/teacher/course/ModuleEvaluationCard.vue";
import EvaluationLock from "@/components/teacher/evaluation/EvaluationLock.vue";
import AssessmentCard from "./AssessmentCard.vue";
import GroupActivityCard from "./GroupActivityCard.vue";

export default {
  props: ["course", "loading", "permission", "assessmentLoading", "groupActivityLoading"],
  components: {
    TopicCard,
    draggable,
    CourseNavigator,
    ModuleOptions,
    ModuleLock,
    AddTopic,
    DeleteDialog,
    AddSubtopic,
    AddPrerequisite,
    CourseEvaluationCard,
    ModuleEvaluationCard,
    EvaluationLock,
    AssessmentCard,
    GroupActivityCard
  },
  data: () => ({
    addingModule: false,
    showLockedDialog: false,
    module: null,
    topic: [],
    addingTopic: false,
    readonly: false,
    addingSubtopic: false,
    addingCourseEval: false,
    addingModuleEval: false,

    deleteType: "",
    removeItem: false, //deleteDialog
    itemToDelete: [],

    evaluation: null,
    showEvaluationLockDialog: false,
    removeCourseEvaluationDialog: false,
    removeModuleEvaluationDialog: false,

    addingPrerequisite: false,

    moduleEvaluations: [],
    checked: {},
    loading2: false,
    loading1: {},
    languageLoading: false, 
    languages: [],
    selectedLanguage: [],
    fields: []
    /* prerequisite_module_id: [],
    prerequisite_modules: [],
    start_modules: [],
    end_modules: [] */
  }),
  mounted() {
    this.getLanguage()
    this.init()

    this.getModuleFieldsAction().then(res => {
      this.fields = res
    })
  },
  computed: {
    ...mapState('instructor', {
      course_evaluations: (state) => state.course_evaluations,
    }),

    ...mapState({
      user: (state) => state.user,
    }),
    
    modules: {
      get() {

        this.$store.state["instructor"].modules.forEach((_module) => {

            this.fields.forEach(field => {
              if(!_module.data) {
                _module.data = {}
              } 
              if (!_module.data[field.input_field_name]) {
                _module.data[field.input_field_name] = '';
              }
          })
        })
        return this.$store.state["instructor"].modules;
      },
      set(val) {
        return this.$store.commit("instructor/modulesMutation", val);
      },
    }
  },
  methods: {
    ...mapMutations([
      'alertMutation'
    ]),

    ...mapMutations('instructor', [
      'modulesMutation'
    ]),

    ...mapActions("instructor", [
      "addModuleAction",
      "showCourseAction",
      "updateModuleAction",
      "sortModuleAction",
      "sortModuleContentAction",
      "showModulePrerequisiteAction",
      "sortSubTopicAction",
      "addCourseEvaluationAction",
      "deleteCourseEvaluationAction",
      "addModuleEvaluationAction",
      "deleteModuleEvaluationAction",
      "getCourseModulesAction",
      "addModulePrerequisiteAction",
      "updateCourseUpdatedAt",
      "getModuleFieldsAction"
    ]),

    getLanguage(){
      this.languageLoading = true
      fetch('https://cdn.jsdelivr.net/gh/umpirsky/language-list@master/data/en/language.json')
      .then(response => response.json())
      .then(languages => {
        Object.entries(languages).forEach(([code, name]) => {
          this.languages.push(name)
        })
        this.languageLoading = false
      })
      .catch(error => {
        console.log(error)
        this.languageLoading = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      });
    },

    updateTopic(moduleIndex, index, scorm){
      let modules = [...this.modules];

      modules[moduleIndex].module_content[index].contentable.scorm = scorm 

      this.$store.commit("instructor/modulesMutation", modules);
    },

    updateTopicPreviewLink(moduleIndex, index, link){
      let modules = [...this.modules];

      modules[moduleIndex].module_content[index].contentable.scorm.preview_link = link 

      this.$store.commit("instructor/modulesMutation", modules);
    },

    updateSubtopic(moduleIndex, index, subtopicIndex, scorm){
      let modules = [...this.modules];

      modules[moduleIndex].module_content[index].contentable.scorm = scorm 

      this.$store.commit("instructor/modulesMutation", modules);
    },

    updateSubtopicPreviewLink(moduleIndex, index, link){
      let modules = [...this.modules];

      modules[moduleIndex].module_content[index].contentable.scorm.preview_link = link 

      this.$store.commit("instructor/modulesMutation", modules);
    },

    init(){
      this.checked = {}
      /* this.start_modules = []
      this.end_modules = []
      this.modules.map(item => {
        this.checked[item.id] = item.prerequisites.length > 0
        this.loading1[item.id] = false
        this.prerequisite_modules.push(...item.prerequisites)

        //check if end of list
        if(!item.module_is_prerequisite && item.prerequisites.length > 0) {
          this.end_modules.push(item.id)
        } 

        //check if start of list or root prerequisite
        if(item.module_is_prerequisite && item.prerequisites.length === 0) {
          this.start_modules.push(item.id)
        } 
      }) */
    },

    addAssessment(data) {
      this.$emit('addAssessment', data)
    },

    viewAssessment(data) {
      this.$emit('viewAssessment', data)
    },

    viewGroupActivity(data) {
      this.$emit('viewGroupActivity', data)
    },

    updateModules(){
      this.loading2 = true
      this.getCourseModulesAction(this.course.id).then(() => {
        this.loading2 = false
      }).catch(() => {
        this.loading2 = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      })
    },

    sortModules(items) {
      let _ids = [];
      items.forEach((el) => {
        _ids.push(el.id);
      });

      this.loading2 = true
      this.sortModuleAction({
        course_id: this.course.id,
        module_id: _ids,
      }).then(() => {
        this.updateCourseUpdatedAt(this.course.id)
        this.updateModules()
        this.alertMutation({
          show: true,
          text: 'Successfully sorted modules',
          type: "success"
        })
      }).catch(() => {
        this.loading2 = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong in sorting modules',
          type: "error"
        })
      })
    },

    addModule() {
      this.addingModule = true;
      this.addModuleAction({
        title: 'Untitled',
        course_id: this.course.id,
      }).then(() => {
        this.addingModule = false;
        this.alertMutation({
          show: true,
          text: 'Module successfully added',
          type: "success"
        })
      }).catch(() => {
        this.addingModule = false;
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      });
    },

    updateModule(e, _module) {
      this.updateModuleAction({
        title: _module.title,
        description: _module.description,
        // valid_until: '',
        course_id: this.course.id,
        module_id: _module.id,
        contact_person: _module.contact_person,
        version: _module.version,
        language: _module.language,
        data: _module.data
      }).then(()=>{
        this.alertMutation({
          show: true,
          text: 'Module successfully updated',
          type: "success"
        })
      })
    },

    updateDesc(e, _module) {
      this.updateModuleAction({
        title: _module.title,
        description: e,
        // valid_until: '',
        course_id: this.course.id,
        module_id: _module.id,
      }).then(()=>{
        this.alertMutation({
          show: true,
          text: 'Module successfully updated',
          type: "success"
        })
      })
    },

    showLock(e) {
      this.module = e;
      this.showLockedDialog = true;
    },

    newTopic(item) {
      this.addingTopic = true;
      this.module = item;
    },

    newAssessment(item) {
      this.$emit('addAssessment', 
        { type: 'add', 
          module_id: item.id, 
          title: 'Untitled' })
    },

    newGroupActivity(item) {
      this.$emit('addGroupActvity', 
        { 
          type: 'add', 
          course_id: item.id,
          module_id: item.id, 
          title: 'Untitled',
          group_activity_tool: 'GDOC',
          group_count: 0
        })
    },

    deleteItem(e) {
      this.removeItem = true;
      this.deleteType = e.type;
      this.itemToDelete = e.item;
    },

    newSubtopic(e) {
      this.addingSubtopic = true;
      this.topic = e;
    },

    sortModuleContent(e) {
      let _ids = [];
      e.forEach((item) => {
        _ids.push(item.id);
      });
      this.loading2 = true
      this.sortModuleContentAction({
        course_id: this.course.id,
        module_id: _ids,
      }).then(() => {
        this.updateModules()
        this.alertMutation({
          show: true,
          text: 'Successfully sorted content',
          type: "success"
        })
      }).catch(() => {
        this.loading2 = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong.',
          type: "error"
        })
      })
    },

    prerequisite(e) {
      this.showModulePrerequisiteAction(e.id).then(() => {
        this.addingPrerequisite = true;
        this.module = e;
      });
    },

    sortSubTopic(e){
      this.loading2 = true
      this.sortSubTopicAction({
        course_id: this.course.id,
        sub_topic_id: e,
      }).then(() => {
        this.updateCourseUpdatedAt(this.course.id)
        this.updateModules()
        this.alertMutation({
          show: true,
          text: 'Successfully sorted sub-topics',
          type: "success"
        })
      }).catch(() => {
        this.loading2 = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong in sorting sub-topics',
          type: "error"
        })
      })
    },

    addModuleEvaluation(module) {
      this.addModuleEvaluation = true
      this.addModuleEvaluationAction({course_id: this.course.id,
      data: {
        module_id: module.id,
        title: 'Untitled',
        description: '',
        is_shuffle_questions: false,
        is_send_feedback_to_user: false,
        submission_limit: 1,
        validity: 'anytime',
        is_allow_to_review: false,
        uploader_id: this.user.id,
      }}).then(()=>{
        this.showCourseAction(this.course.id).then(() => {
          this.alertMutation({
            show: true,
            text: 'Module Evaluation successfully created',
            type: "success"
          })
        }).finally(() => {
          this.addModuleEvaluation = false
        })
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
        this.addModuleEvaluation = false
      })
    },

    lockModuleEvaluation(data) {
      this.evaluation = data
      this.showEvaluationLockDialog = true
    },

    deleteModuleEvaluation(data){
      this.evaluation = data
      this.removeModuleEvaluationDialog = true
    },

    removeModuleEvaluation(){
      this.deleteModuleEvaluationAction({...this.evaluation, course_id: this.course.id}).then(() => {
        this.removeModuleEvaluationDialog = false
        this.alertMutation({
          show: true,
          text: 'Module Evaluation successfully deleted',
          type: "success"
        })        
      }).catch(() => {
        this.removeModuleEvaluationDialog = false
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      })
    },

    viewModuleEvaluation(data) {
      this.$emit('viewModuleEvaluation', data)
    },

    addCourseEvaluation(){
      this.addingCourseEval = true
      this.addCourseEvaluationAction({state: this.questions,
      data: {
        course_id: this.course.id,
        title: 'Untitled',
        description: '',
        is_shuffle_questions: false,
        is_send_feedback_to_user: false,
        submission_limit: 1,
        validity: 'anytime',
        is_allow_to_review: false,
        uploader_id: this.user.id,
      }}).then(()=>{
        this.alertMutation({
          show: true,
          text: 'Course Evaluation successfully created',
          type: "success"
        })
        this.addingCourseEval = false
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
        this.addingCourseEval = false
      })
    },

    lockCourseEvaluation(data) {
      this.evaluation = data
      this.showEvaluationLockDialog = true
    },

    deleteCourseEvaluation(data){
      this.evaluation = data
      this.removeCourseEvaluationDialog = true
    },

    removeCourseEvaluation(){
      this.deleteCourseEvaluationAction(this.evaluation).then(() => {
        this.removeCourseEvaluationDialog = false
        this.alertMutation({
          show: true,
          text: 'Course Evaluation successfully deleted',
          type: "success"
        })
      })
    },

    viewCourseEvaluation(data) {
      this.$emit('viewCourseEvaluation', data)
    },

    updatePrerequisite(selected, module_id){
      this.loading1 = { ...this.loading1, [module_id]: true };
      
      this.addModulePrerequisiteAction({
        course_id: this.course.id,
        module_id: module_id,
        prerequisite_module_id: selected,
      }).then(() => {
        this.getCourseModulesAction(this.course.id).then(() => {
          this.init()
          this.alertMutation({
            show: true,
            text: "Pre-requisite Successfully Updated",
            type: "success"
          });
        }).finally(() => {
          this.loading1 = { ...this.loading1, [module_id]: false };
        })
      }).catch(() => {
        this.loading1 = { ...this.loading1, [module_id]: false };
        this.alertMutation({
          show: true,
          text: "Something went wrong",
          type: "error"
        });
      })
    },

    /* getPrerequisite(val){
      if(val.length === 0) return []
      
      else return val.map(item => item.module)
    },

    getModulesNotPrerequisite(id, prerequisites) {

      let _module = this.modules.filter(item => id !== item.id)

      return [ 
        // curent set prerequisite
          ...prerequisites.map(item => item.module), 
          ..._module.filter(item => {
            if(!item.module_is_prerequisite) {
              if(!this.isModulePrerequisite(item.id, id)) {
                if(!item.module_is_prerequisite) {
                  if(this.start_modules.includes(id) || this.end_modules.includes(item.id)) return false
                  return true
                }
                return false
              }
              return false
            }
            return false
          })
        ]
    },

    check(val, id) {
      this.checked = { ...this.checked, [id]: !val ? false : true };

      if(!val) {
        this.updatePrerequisite([], id)
      }
    },

    isModulePrerequisite(module_id, prerequisite_module_id){
      let _module = this.modules.find(item => item.id === module_id)
      let _prerequisites = _module.prerequisites.map(item => item.module)

      if(_prerequisites.length === 0) return false
      return _prerequisites.findIndex(item => item.id === prerequisite_module_id) !== -1
    }, */
  },
};
</script>
