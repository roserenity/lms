<template>
    <section>
        <v-expansion-panel-header color="secondary-5"class="m-0 p-0 mat_expansion_panel ">
            <template v-slot:default="{ open }">
                <section class="d-flex flex-row align-center">
                    <section class="description_div poppins f14 fw500">
                        {{module.title ? module.title : 'Untitled' }} 
                    </section>
                    <v-spacer></v-spacer>
                    <v-badge
                        bordered
                        size='8'
                        inline
                        class="ml-auto"
                        :color="!hasUnfinishedItems ? 'success' : 'warning'"
                    />
                </section>
            </template>
        </v-expansion-panel-header>
        <v-expansion-panel-content class="expansion-timeline ">
            <div v-for="module_content in module.module_content" :key="module_content.uuid">
                <div v-if="module_content.contentable">
                    <div v-if="module_content.contentable_type === 'App\\Models\\Topic'">
                        <v-list-item dense
                            class="d-flex align-center pa-1"
                            @click.stop=""
                        >
                            <v-icon small color="primary">
                                mdi-archive-outline
                            </v-icon>

                            <v-list-item-title class="poppins ml-2 f13 fw500">
                                <div class="f9 secondary-2--text roboto">
                                    Topic
                                </div>
                                {{module_content.contentable.title}}
                                
                                <div class="f9 secondary-2--text roboto d-flex flex-row align-center" v-if="module_content.contentable.student_topic_progress && module_content.contentable.student_topic_progress.time_spent">
                                    TIME SPENT: {{ module_content.contentable.student_topic_progress.time_spent === 0 ? '-' : $dateFormat.duration(module_content.contentable.student_topic_progress.time_spent) }} 
                                </div>
                            </v-list-item-title>
                            <v-btn :disabled="loading" :loading="loading && selectedItem === module_content.contentable" @click="markTopicAsCompleted(module_content.contentable)" dense small class="primary ml-auto" flat v-if="['mp4', 'ppt', 'pptx', 'docx', 'png', 'jpg', 'jpeg', 'pdf', 'gdrive_link'].includes(module_content.contentable.learning_material.file_type) && !module_content.contentable.student_is_done">
                                Mark as completed
                            </v-btn>
                            <v-badge
                                bordered
                                class="thin-border"
                                :color="module_content.contentable.student_is_done ? 'success' : 'secondary'"
                                size='10'
                                inline
                            >
                            </v-badge>
                        </v-list-item>
                    
                        <v-list-item
                            v-for="assessment in module_content.contentable.topic_has_assessments" :key="assessment.uuid"
                            dense 
                            class="d-flex align-center pa-1 pl-4"
                            @click.stop="">
                            <v-icon small color="primary">
                                mdi-note-edit-outline
                            </v-icon>
                            <v-list-item-title  class="poppins ml-2 f12 fw500">
                                <div class="f9 secondary-2--text roboto">
                                    Assessment
                                </div>
                                {{assessment.title}}
                                <div class="f9 secondary-2--text roboto d-flex flex-row align-center" v-if="assessment?.student_assessment_progress">
                                    SCORE: {{ assessment?.assessment_scores?.[0]?.score ?? 0 }}/{{ assessment?.total_points ?? 0 }}
                                    <v-chip v-if="assessment?.assessment_scores?.[0]?.checked" x-small dense color="success" class="ml-1">
                                        CHECKED
                                    </v-chip>
                                    <v-chip v-else x-small dense color="warning" class="ml-1">
                                        UNCHECKED
                                    </v-chip>
                                </div>
                                
                                <div class="f9 secondary-2--text roboto d-flex flex-row align-center" v-if="assessment.student_assessment_progress && assessment.student_assessment_progress.time_spent">
                                    TIME SPENT: {{ assessment.student_assessment_progress.time_spent === 0 ? '-' : $dateFormat.duration(assessment.student_assessment_progress.time_spent) }} 
                                </div>
                            </v-list-item-title>
                            <v-spacer></v-spacer>
                            <v-badge
                                bordered
                                class="thin-border ml-1"
                                :color="assessment.student_assessment_progress ? 'success' : 'secondary'"
                                size='10'
                                inline
                            >
                            </v-badge>
                        </v-list-item>
                        <div v-for="sub_topic in module_content.contentable.sub_topics" :key="sub_topic.uuid" class=" pl-4">
                            <v-list-item dense
                                class="d-flex align-center pa-1"
                                @click.stop=""
                            >
                                <v-icon small color="primary">
                                    mdi-archive-outline
                                </v-icon>

                                <v-list-item-title class="poppins ml-2 f13 fw500">
                                    <div class="f9 secondary-2--text roboto">
                                        Subtopic
                                    </div>
                                    {{sub_topic.title}}
                                
                                    <div class="f9 secondary-2--text roboto d-flex flex-row align-center" v-if="sub_topic.student_sub_topic_progress && sub_topic.student_sub_topic_progress.time_spent">
                                        TIME SPENT: {{ sub_topic.student_sub_topic_progress.time_spent === 0 ? '-' : $dateFormat.duration(sub_topic.student_sub_topic_progress.time_spent) }} 
                                    </div>
                                </v-list-item-title>

                                <v-btn :disabled="loading" :loading="loading && selectedItem === module_content.contentable" @click="markSubtopicAsCompleted(module_content.contentable)" dense small class="primary ml-auto" flat v-if="['mp4', 'ppt', 'pptx', 'docx', 'png', 'jpg', 'jpeg', 'pdf', 'gdrive_link'].includes(module_content.contentable.learning_material.file_type) && !module_content.contentable.student_is_done">
                                    Mark as completed
                                </v-btn>
                                <v-badge
                                    bordered
                                    class="thin-border"
                                    :color="sub_topic.student_is_done ? 'success' : 'secondary'"
                                    size='10'
                                    inline
                                >
                                </v-badge>
                            </v-list-item>
                            <v-list-item
                                v-for="assessment in sub_topic.sub_topic_has_assessments"  :key="assessment.uuid"
                                dense 
                                class="d-flex align-center pa-1 pl-4"
                                @click.stop="">
                                <v-icon small color="primary">
                                    mdi-note-edit-outline
                                </v-icon>
                                <v-list-item-title  class="poppins ml-2 f12 fw500">
                                    <div class="f9 secondary-2--text roboto">
                                        Assessment
                                    </div>
                                    {{assessment.title}}
                                
                                    <div class="f9 secondary-2--text roboto d-flex flex-row align-center" v-if="assessment.student_assessment_progress && assessment.student_assessment_progress.time_spent">
                                        TIME SPENT: {{ assessment.student_assessment_progress.time_spent === 0 ? '-' : $dateFormat.duration(assessment.student_assessment_progress.time_spent) }} 
                                    </div>
                                </v-list-item-title>
                                <v-spacer></v-spacer>
                                <div
                                    v-if="assessment?.student_assessment_progress"
                                    class="roboto d-flex flex-column align-center f13 fw500">
                                    <div class="f9 secondary-2--text">
                                        score
                                    </div>
                                    {{ assessment?.assessment_scores?.[0]?.score ?? 0 }}/{{ assessment?.total_points ?? 0 }}
                                </div>
                                <v-badge
                                    bordered
                                    class="thin-border"
                                    :color="assessment.student_assessment_progress ? 'success' : 'secondary'"
                                    size='10'
                                    inline
                                >
                                </v-badge>
                            </v-list-item>
                        </div>
                    </div>
                    
                    <div v-if="module_content.contentable_type === 'App\\Models\\Assessment'">
                        <v-list-item
                            dense 
                            class="d-flex align-center pa-1"
                            @click.stop="">
                            <v-icon small color="primary">
                                mdi-note-edit-outline
                            </v-icon>
                            <v-list-item-title  class="poppins ml-2 f12 fw500">
                                <div class="f9 secondary-2--text roboto">
                                    Assessment
                                </div>
                                {{module_content.contentable.title}}
                                
                                <div class="f9 secondary-2--text roboto d-flex flex-row align-center" v-if="module_content?.contentable?.student_is_done">
                                    SCORE: {{ module_content?.contentable?.assessment_scores?.[0]?.score ?? 0 }}/{{ module_content?.contentable?.total_points ?? 0 }}
                                    <v-chip v-if="module_content?.contentable?.assessment_scores?.[0]?.checked" x-small dense color="success" class="ml-1">
                                        CHECKED
                                    </v-chip>
                                    <v-chip v-else x-small dense color="warning" class="ml-1">
                                        UNCHECKED
                                    </v-chip>
                                </div>
                                
                                <div class="f9 secondary-2--text roboto d-flex flex-row align-center" v-if="module_content.contentable.student_assessment_progress && module_content.contentable.student_assessment_progress.time_spent">
                                    TIME SPENT: {{ module_content.contentable.student_assessment_progress.time_spent === 0 ? '-' : $dateFormat.duration(module_content.contentable.student_assessment_progress.time_spent) }} 
                                </div>
                            </v-list-item-title>
                            <v-spacer></v-spacer>
                            <v-badge
                                bordered
                                class="thin-border ml-1"
                                :color="module_content.contentable.student_assessment_progress ? 'success' : 'secondary'"
                                size='10'
                                inline
                            >
                            </v-badge>
                        </v-list-item>
                    </div>

                    <div v-if="module_content.contentable_type === 'App\\Models\\GroupActivity'">
                        <v-list-item
                            dense 
                            class="d-flex align-center pa-1"
                            @click.stop="">
                            <v-icon small color="primary">
                                mdi-note-edit-outline
                            </v-icon>
                            <v-list-item-title  class="poppins ml-2 f12 fw500">
                                <div class="f9 secondary-2--text roboto">
                                    Group Activity
                                </div>
                                {{module_content.contentable.title}}
                                <div class="f9 secondary-2--text roboto d-flex flex-row align-center" v-if="module_content.contentable.student_is_done">
                                    <v-chip v-if="module_content.contentable.student_group_activity_status_check ? module_content.contentable.student_group_activity_status_check.checked : false" x-small dense color="success" class="ml-1"> CHECKED</v-chip>
                                    <v-chip v-else x-small dense color="warning" class="ml-1">UNCHECKED</v-chip>
                                </div>
                                
                                <div class="f9 secondary-2--text roboto d-flex flex-row align-center" v-if="module_content.contentable.student_group_activity_progress && module_content.contentable.student_group_activity_progress.time_spent">
                                    TIME SPENT: {{ module_content.contentable.student_group_activity_progress.time_spent === 0 ? '-' : $dateFormat.duration(module_content.contentable.student_group_activity_progress.time_spent) }} 
                                </div>
                            </v-list-item-title>
                            <v-spacer></v-spacer>
                            <v-badge
                                bordered
                                class="thin-border ml-1"
                                :color="module_content.contentable.student_is_done ? 'success' : 'secondary'"
                                size='10'
                                inline
                            >
                            </v-badge>
                        </v-list-item>
                    </div>
                    
                </div>
            </div>
            <v-list-item dense
                v-for="module_evaluation in module.module_evaluations"
                :key="module_evaluation.uuid"
                class="d-flex align-center pa-1"
                @click.stop=""
            >
                <v-icon small color="primary">
                    mdi-file-document-edit-outline
                </v-icon>

                <v-list-item-title class="poppins ml-2 f13 fw500">
                    <div class="f9 secondary-2--text roboto">
                        Module Evaluation
                    </div>
                    {{module_evaluation.title}}
                    
                    <div class="f9 secondary-2--text roboto d-flex flex-row align-center" v-if="module_evaluation.student_module_evaluation_progress && module_evaluation.student_module_evaluation_progress.time_spent">
                        TIME SPENT: {{ module_evaluation.student_module_evaluation_progress.time_spent === 0 ? '-' : $dateFormat.duration(module_evaluation.student_module_evaluation_progress.time_spent) }} 
                    </div>
                </v-list-item-title>

                <v-badge
                    bordered
                    class="thin-border"
                    :color="module_evaluation.student_is_done ? 'success' : 'secondary'"
                    size='10'
                    inline
                >
                </v-badge>
            </v-list-item>
        </v-expansion-panel-content>
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
import { mapActions, mapMutations } from 'vuex';

export default {
    props: ['module', 'completed'],
    data: () => ({
        loading: false,
        selectedItem: null
    }),
    methods: {
        ...mapActions("instructor", ['setEnrolleeCourseTopicMarkAsCompletedAction', 'setEnrolleeCourseSubtopicMarkAsCompletedAction']),
        ...mapMutations(['alertMutation']),

        markSubtopicAsCompleted(item) {
            this.loading = true
            this.selectedItem = item
            this.setEnrolleeCourseSubtopicMarkAsCompletedAction({ user_id: this.$route.params.user_id, subtopic_id: item.id }).then(() => {
                this.alertMutation({
                    show: true,
                    text: "Successfully marked as completed",
                    type: "success"
                })
                this.$emit("fetchModules")
            }).catch(() => {
                this.alertMutation({
                    show: true,
                    text: "Something went wrong",
                    type: "error"
                })
            }).finally(() => {
                this.loading = false
                this.selectedItem = null
            })
        },

        markTopicAsCompleted(item) {
            this.loading = true
            this.selectedItem = item
            this.setEnrolleeCourseTopicMarkAsCompletedAction({ user_id: this.$route.params.user_id, topic_id: item.id }).then(() => {
                this.alertMutation({
                    show: true,
                    text: "Successfully marked as completed",
                    type: "success"
                })
                this.$emit("fetchModules")
            }).catch(() => {
                this.alertMutation({
                    show: true,
                    text: "Something went wrong",
                    type: "error"
                })
            }).finally(() => {
                this.loading = false
                this.selectedItem = null
            })
        },

    },
    computed: {
        hasUnfinishedItems() {
            // Iterate through module content
            for (const module_content of this.module.module_content) {
                if(module_content.contentable) {
                    if(module_content.contentable_type === 'App\\Models\\Topic') {
                        if (!module_content.contentable.student_is_done) {
                            return true; // If any topic is not done, return true
                        }
                        for (const assessment of module_content.contentable.topic_has_assessments) {
                            if (!assessment.student_assessment_progress) {
                                return true; // If any assessment is not done, return true
                            }
                        }
                        for (const subtopic of module_content.contentable.sub_topics) {
                            if (!subtopic.student_is_done) {
                                return true; // If any subtopic is not done, return true
                            }
                            for (const subAssessment of subtopic.sub_topic_has_assessments) {
                                if (!subAssessment.student_assessment_progress) {
                                    return true; // If any sub-assessment is not done, return true
                                }
                            }
                        }
                    } 

                    if(module_content.contentable_type === 'App\\Models\\Assessment') { 
                        if (!module_content.contentable.student_assessment_progress) {
                            return true; // If assessment is not done, return true
                        }
                    }

                    if(module_content.contentable_type === 'App\\Models\\GroupActivity') { 
                        if (!module_content.contentable.student_is_done) {
                            return true; // If group activity is not done, return true
                        }
                    }
                }
            }
            // Check module evaluations
            for (const moduleEvaluation of this.module.module_evaluations) {
                if (!moduleEvaluation.student_is_done) {
                    return true; // If any module evaluation is not done, return true
                }
            }
            return false; // If all items are done, return false
        }
    },
}
</script>