import state from './state'
import * as getters from './getters'
import * as mutations from './mutations'
import announcements from './actions/announcements'
import course from './actions/course'
import classes from './actions/classes'
import modules from './actions/modules'
import topics from './actions/topics'
import subtopics from './actions/subtopics'
import assessments from './actions/assessments'
import questions from './actions/questions'
import rubrics from './actions/rubrics'
import grading from './actions/grading'
import trash from './actions/trash'
import inbox from './actions/inbox'
import notification from './actions/notification'
import student_output from './actions/student_output'
import meetings from './actions/meetings'
import course_evaluation from './actions/course_evaluation'
import module_evaluation from './actions/module_evaluation'
import enrollment from './actions/enrollment'
import reporting from './actions/reporting'
import scorm_activities from './actions/scorm_activities'
import scorm from './actions/scorm'
import activitylogs from './actions/activitylogs'
import certificate_template from './actions/certificate_template'
import forum from './actions/forum'
import question_pools from './actions/question_pools'
import course_groups from './actions/course_groups';
import group_activity from './actions/group_activity'
import file_exports from './actions/file_exports'

export default {
  namespaced: true,
  mutations,
  getters,
  state,
  actions: {
    ...announcements,
    ...course,
    ...classes,
    ...modules,
    ...topics,
    ...subtopics,
    ...assessments,
    ...questions,
    ...rubrics,
    ...grading,
    ...trash,
    ...inbox,
    ...notification,
    ...student_output,
    ...meetings,
    ...course_evaluation,
    ...module_evaluation,
    ...enrollment,
    ...reporting,
    ...scorm_activities,
    ...scorm,
    ...activitylogs,
    ...certificate_template,
    ...forum,
    ...question_pools,
    ...course_groups,
    ...group_activity,
    ...file_exports
  }
}