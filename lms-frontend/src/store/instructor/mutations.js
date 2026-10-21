import router from "@/router"

export function announcementsMutation(state, payload) {
  state.announcements = payload
}

export function coursesMutation(state, payload) {
  state.courses = payload
}

export function courseMutation(state, payload) {
  state.course = payload
}

export function courseUpdatedAtMutation(state, payload) {
  state.course = { ...state.course, updated_at: payload}
}

//components dir/ layouts/teacher
export function statusCourseMutation(state, payload) {
  state.statusCourse.id = payload.id
  state.statusCourse.status = payload.status
  state.statusCourse.action = payload.action
}

export function classesMutation(state, payload) {
  state.classes = payload
}

export function modulesMutation(state, payload) {
  state.modules = payload
}

export function assessmentsMutation(state, payload) {
  state.assessments = payload
}

export function courseEvaluationsMutation(state, payload) {
  state.course_evaluations = payload
}

export function moduleEvaluationsMutation(state, payload) {
  state.module_evaluations = payload
}

export function duplicateIdMutation(state, payload) {
  state.duplicate_id = payload
}

export function instructorsMutation(state, payload) {
  state.instructors = payload
}

export function rubricsMutation(state, payload) {
  state.rubrics = payload
}

const filterQuestion = (state, payload) => {
  let item;
  state.assessment_questions.forEach(assessment => {
    if(assessment.id == payload.assessment_id) {
      assessment.assessment_questions.forEach(question => {
        if(question.id == payload.question_id) {
          item = question   
        }
      })
    }
  });
  return item
}

export function addChoicesMutation(state, payload) {
  
  let question = null

  state.assessment_questions.forEach(assessment_question => {
    if(assessment_question.id == payload.question_id) {
      question = assessment_question   
    }
  });
  
  if(question.id == payload.question_id) {
    if(!question.assessment_answer.choices) {
      let choices = []
      choices.push({ choice_0: '', feedback: '' })
      return question.assessment_answer.choices = choices  
    }
    let choices = question.assessment_answer.choices
    let index = choices.length;
    choices.push({ 
      [`choice_${index}`] : question.type_of_question == 'identification' ? `` : `Type your answer ${index+1}`
      , feedback: '' 
    });
    return console.log(question.assessment_answer.choices = choices)          
  }

}

export function inputAnswerMutation(state, payload) {
  let question = filterQuestion(state, payload)

  if(question.id == payload.question_id) {
    question.assessment_answer.choices
    let choices = question.assessment_answer.choices
    
    choices[payload.index][payload.type]= payload.text
    question.assessment_answer.choices = choices 
    console.log(question.assessment_answer.choices);  
  }
  
}


const filterQuestionPoolQuestion = (state, payload) => {
  let item;
  state.questions.forEach(question => {
    if(question.id == payload.id) {
      item = question   
    }
  });
  return item
}

export function inputQuestionPoolQuestionAnswerMutation(state, payload) {
  let question = filterQuestionPoolQuestion(state, payload)

  if(question.id == payload.question_id) {
    question.answer.choices
    let choices = question.answer.choices
    
    choices[payload.index][payload.type]= payload.text
    question.answer.choices = choices 
    console.log(question.answer.choices);  
  }
  
}

export function addQuestionPoolQuestionChoicesMutation(state, payload) {
  
  let question = filterQuestionPoolQuestion(state, payload)

  console.log(question)
  if(question.id == payload.id) {
    if(!question.answer.choices) {
      let choices = []
      choices.push({ choice_0: '', feedback: '' })
      return question.answer.choices = choices  
    }
    let choices = question.answer.choices
    let index = choices.length;
    choices.push({ 
      [`choice_${index}`] : question.type_of_question == 'identification' ? `` : `Type your answer ${index+1}`
      , feedback: '' 
    });
    return console.log(question.answer.choices = choices)          
  }

}

export function gradingMutation(state, payload) {
  state.grading = payload
}

export function gradingAssessmentUsersMutation(state, payload) {
  state.gradingUsers = payload
}

export function trashMutation(state, payload) {
  state.trash = payload
}

export function contactsMutation(state, payload) {
  state.contacts = payload
}

export function notificationMutation(state, payload) {
  state.notification = payload
}

export function chatListsMutation(state, payload) {
  state.chat_lists = payload
}

export function groupListsMutation(state, payload) {
  state.group_lists = payload
}

export function chatWrapperMutation(state, payload) {
  state.chat_wrapper = payload
}

export function messagesMutation(state, payload) {
  state.messages = payload
}

export function assessmentQuestionsMutation(state, payload) {
  state.assessment_questions = payload
}
export function assessmentAnswersMutation(state, payload) {
  state.assessment_answers = payload
}

export function assessmentUsersMutation(state, payload) {
  state.assessment_users = payload
}

export function assessmentClassesMutation(state, payload) {
  state.assessment_classes = payload
}

export function studentOutputMutation(state, payload) {
  state.student_output = payload
}

export function studentOutputUserMutation(state, payload) {
  state.student_output = {...state.student_output, ...payload}
}

export function meetingsMutation(state, payload) {
  state.meetings = payload
}

export function meetingParticipantsMutation(state, payload) {
  state.meeting_participants = payload
}

export function activityLogMutation(state, payload) {
  state.activity_log = payload
}

export function evaluationQuestionMutation(state, payload) {
  state.questions = payload
}

export function enrolleesMutation(state, payload) {
  state.enrollees = payload
}

export function evaluationAnswersMutation(state, payload) {
  state.evaluation_answers = payload
}

export function scormActivityQuestionMutation(state, payload) {
  state.scorm_activity_questions = payload
}

export function scormActivityAnswersMutation(state, payload) {
  state.scorm_activity_answers = payload
}

export function activitylogMutation(state, payload) {
  state.activitylogs = payload
}

export function certificateTemplatesMutation(state, payload) {
  state.certificate_templates = payload
}

export function forumPostMutation(state, payload) {
  state.forum_posts = payload
}

export function questionPoolsMutation(state, payload) {
  state.question_pools = payload
}

export function questionPoolsQuestionMutation(state, payload) {
  state.questions = payload
}

export function userGroupMutation(state, payload) {
  state.course_groups = payload
}

export function trainingCreditMutation(state, payload) {
  state.training_credits = payload
}

export function groupActivityMutation(state, payload) {
  state.group_activities = payload
}

export function exportsMutation(state, payload) {
  state.file_exports = payload
}