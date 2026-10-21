function userProgressElearning(progress) {
    return Math.round(((progress.total_done_assessments + progress.total_done_topics + progress.total_done_sub_topics + progress.total_done_course_evaluations + progress.total_done_module_evaluations + progress.total_done_group_activities) / (progress.assessments_count + progress.topics_count + progress.sub_topics_count + progress.course_evaluations_count + progress.module_evaluations_count + progress.group_activities_count)) * 100)
}

function userProgressBlended(progress) {
  return Math.round(((progress.total_done_assessments + progress.total_done_topics + progress.total_done_sub_topics + progress.total_done_course_evaluations + progress.total_done_module_evaluations + progress.total_attended_zoom_meetings + progress.total_done_group_activities) / (progress.assessments_count + progress.topics_count + progress.sub_topics_count + progress.course_evaluations_count + progress.module_evaluations_count + progress.zoom_meetings_count + progress.group_activities_count)) * 100)
}

export default {
  install(Vue) {
    Vue.prototype.$userProgressElearning = userProgressElearning
    Vue.prototype.$userProgressBlended = userProgressBlended
  }
}