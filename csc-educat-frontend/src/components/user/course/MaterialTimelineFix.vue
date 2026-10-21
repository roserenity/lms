<template>
    <section ref="materialTimeline" :style="{'--bgColor': getPrimary}">
        <v-expansion-panels class="d-block" accordion flat v-model="expand_material" multiple>
            <v-expansion-panel
                v-for="(module, i) in modules"
                :key="module.uuid"
            >
                <v-expansion-panel-header color="secondary-5" class="m-0 p-0 mat_expansion_panel ">
                    <template v-slot:default="{ open }">
                        <section class="ma-0 pa-0 d-flex flex-column description_div">
                            <!-- <div v-if="module.module_is_prerequisite" class="f9 secondary-2--text mb-1">
                                PREREQUISITE
                            </div> -->
                            <ModuleItem :module="module" :index="i" :prev_module_done="i === 0 ? false : checkModuleIsDone(modules[i-1])" :custom_module_fields="course ? course.custom_module_fields : []"/>
                        </section>
                    </template>
                </v-expansion-panel-header>
                
                <v-expansion-panel-content class="expansion-timeline description_div ma-0 pa-0" >
                    <v-alert error
                        v-if="module.validity === 'lock' ? (i > 0 ? !checkModuleIsDone(modules[i-1]) : true): false"
                        class="f12 mt-1 roboto" 
                        dense
                        text
                        color="red">
                        <span v-if="i > 0">
                            <b>{{ modules[i-1].title }}</b> must be completed
                        </span>
                        <span v-else>
                            Contact your course administrator to update the module
                        </span>
                    </v-alert>
                    <div v-if="module.module_content.length === 0 && module.module_evaluations.length === 0" class="pl-5 my-1 fw500">
                        <FormLabel :label="'No content available'" />
                    </div>
                    <v-sheet v-else>
                        <div
                            v-for="module_content in module.module_content" 
                            :key="module_content.uuid">
                            <div v-if="module_content.contentable">
                                <ContentItem 
                                    v-if="module_content.contentable_type === 'App\\Models\\Topic'"
                                    :data="module_content.contentable" 
                                    :current="current"
                                    :loading="loading"
                                    :module_is_locked="module.validity === 'lock' ? ( i > 0 ? !checkModuleIsDone(modules[i-1]) : true ): module.module_is_locked"
                                    @click="$emit('click', module_content.contentable)"/>

                                    <div v-if="module_content.contentable_type === 'App\\Models\\Topic'" class="pl-5">
                                        <AssessmentItem 
                                            v-for="assessment in module_content.contentable.topic_has_assessments"
                                            :key="assessment.uuid"
                                            :data="assessment" 
                                            :current="current"
                                            :loading="loading"
                                            :module_is_locked="module.validity === 'lock' ? ( i > 0 ? !checkModuleIsDone(modules[i-1]) : true ): module.module_is_locked"
                                            @click="$emit('click', assessment)"
                                        />
                                        <div
                                            v-for="sub_topic in module_content.contentable.sub_topics" 
                                            :key="sub_topic.uuid">
                                            <ContentItem 
                                                :data="sub_topic" 
                                                :current="current"
                                                :loading="loading"
                                                :module_is_locked="module.validity === 'lock' ? ( i > 0 ? !checkModuleIsDone(modules[i-1]) : true ): module.module_is_locked"
                                                @click="$emit('click', sub_topic)"/>
                                            <div
                                                class="pl-5"
                                                v-for="assessment in sub_topic.sub_topic_has_assessments"
                                                :key="assessment.uuid">
                                                <AssessmentItem 
                                                    :data="assessment" 
                                                    :current="current"
                                                    :loading="loading"
                                                    :module_is_locked="module.validity === 'lock' ? ( i > 0 ? !checkModuleIsDone(modules[i-1]) : true ): module.module_is_locked"
                                                    @click="$emit('click', assessment)"/>
                                            </div>
                                        </div>
                                    </div>
                                <AssessmentItem 
                                    v-if="module_content.contentable_type === 'App\\Models\\Assessment'"
                                    :key="module_content.contentable.uuid"
                                    :data="module_content.contentable" 
                                    :current="current"
                                    :loading="loading"
                                    :module_is_locked="module.validity === 'lock' ? ( i > 0 ? !checkModuleIsDone(modules[i-1]) : true ): module.module_is_locked"
                                    @click="$emit('click', module_content.contentable)"
                                />
                                <GroupActivityItem
                                    v-if="module_content.contentable_type === 'App\\Models\\GroupActivity'"
                                    :key="module_content.contentable.uuid"
                                    :data="module_content.contentable" 
                                    :current="current"
                                    :loading="loading"
                                    :module_is_locked="module.validity === 'lock' ? ( i > 0 ? !checkModuleIsDone(modules[i-1]) : true ): module.module_is_locked"
                                    @click="$emit('click', module_content.contentable)"
                                />
                            </div>
                        </div>
                    </v-sheet> 
                    <EvaluationItem 
                        v-for="evaluation in module.module_evaluations"
                        :key="evaluation.uuid"
                        :data="evaluation"
                        :current="current"
                        :loading="loading"
                        :module_is_locked="module.validity === 'lock' ? ( i > 0 ? !checkModuleIsDone(modules[i-1]) : true ): module.module_is_locked"
                        @click="$emit('click', evaluation)"
                        :type="'module'"
                    />
                </v-expansion-panel-content>
            </v-expansion-panel>
            <v-expansion-panel v-if="evaluations.length > 0">
                <v-expansion-panel-header dense color="secondary-5" class="m-0 p-0 mat_expansion_panel ">
                    <template v-slot:default="{ open }">
                        <v-container class="ma-0 pa-0 d-flex flex-column description_div f14 fw500 secondary-1--text">
                            <b>Course Evaluation</b> 
                        </v-container>
                    </template>
                </v-expansion-panel-header>
                <v-expansion-panel-content class="expansion-timeline description_div">
                    <EvaluationItem 
                        v-for="evaluation in evaluations"
                        :key="evaluation.uuid"
                        :data="evaluation"
                        :current="current"
                        :loading="loading"
                        :module_is_locked="evaluation.course_evaluation_is_locked"
                        @click="$emit('click', evaluation)"
                        :type="'course'"
                    />
                </v-expansion-panel-content>
            </v-expansion-panel>
        </v-expansion-panels>
    </section>
</template>


<style scoped>

.v-expansion-panel-header.mat_expansion_panel {
  align-items: start !important;
  white-space: normal !important;
  word-wrap: break-word !important;
}

.description_div {
  white-space: normal !important;
  word-wrap: break-word !important;
}

.expansion-timeline .v-expansion-panel-content__wrap {
  padding-bottom: 0 !important;
  padding-left: 0 !important;
  padding-right: 0 !important;
}

.topic.timeline-container.active::after {
  background-color: var(--bgColor);
  border:  2px solid var(--bgColor);
}
.subtopic.timeline-container.active::after {
  background-color: var(--bgColor);
  border:  2px solid var(--bgColor);
}
.assessments.timeline-container.active::after {
  background-color: var(--bgColor);
  border:  2px solid var(--bgColor);
}

.v-badge.thin-border .v-badge__badge::after {
  border-color: #BDBDBD !important;
  border-width: 1px !important;
  margin: 1px !important;
}

</style>

<script>
import { mapActions, mapState } from 'vuex';
import ModuleItem from '@/components/user/course/module_timeline/ModuleItem.vue'
import ContentItem from '@/components/user/course/module_timeline/ContentItem.vue'
import AssessmentItem from '@/components/user/course/module_timeline/AssessmentItem.vue'
import EvaluationItem from '@/components/user/course/module_timeline/EvaluationItem.vue'
import GroupActivityItem from '@/components/user/course/module_timeline/GroupActivityItem.vue'

export default {
    props: {
        current: {
            type: String,
            default: ''
        },
        loading: {
            type: Boolean,
            default: false
        },
        course: {
            type: Object || null || Array,
            default: null
        },
    },

    components: {
        ModuleItem,
        ContentItem,
        AssessmentItem,
        EvaluationItem,
        GroupActivityItem
    },

    data: () => ({
        expand_material: [],
    }),

    created() {
        this.expandAll()
    },

    computed: {
        ...mapState({
            customization: (state) => state.customization
        }),

        ...mapState('usr', {
            modules: (state) => state.modules,
            evaluations: (state) => state.evaluations,
        }),

        getPrimary(){
            return this.customization.hasOwnProperty('primary_buttons_color') ? this.customization.primary_buttons_color : this.$vuetify.theme.currentTheme.primary
        },
    },

    methods: {

        checkModuleIsDone(_module) {
            if(!_module) return false
            //if(_module.topics.length === 0 && _module.module_evaluations.length === 0) return false

            return [_module.module_topic_is_done, _module.module_subtopic_is_done, _module.module_assessment_is_done, _module.module_evaluation_is_done].every((item) => item === true)
        },

        expandAll(){
            let i = 0
            while ( i < (this.modules.length + this.evaluations.length)) {
                this.expand_material.push(i);
                i++;
            }
        }
    }
}
</script>