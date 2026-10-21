import { api } from '@/services/axios'

export default {
  async getInstructorAssessmentAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/assessment/${payload}`).then(res => {
        commit('assessmentQuestionsMutation', res.data.data.assessment_questions)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.message, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },

  async getAssessmentAnswersAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/assessment/${payload.assessment_id}/${payload.assessment_question_id}/answers`, { params: payload }).then(res => {
        commit('assessmentAnswersMutation', res.data.data)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.message, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },

  async getInstructorAssessmentsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get('/instructor/assessments', { params: payload }).then(res => {
        if(res.data.data.data) {
          commit('assessmentsMutation', res.data.data.data)
        } else {
          commit('assessmentsMutation', res.data.data)
        }
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async addAssessmentAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/assessments', payload).then(res => {
        dispatch('getAssessmentsAction', {course_id: payload.course_id})
        dispatch('showCourseAction', payload.course_id)
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async addGroupActivityAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/group-activity', payload).then(res => {
        dispatch('showCourseAction', payload.course_id)
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async updateAssessmentAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/assessments/${payload.id}`, payload).then(res => {
        dispatch('updateCourseUpdatedAt', payload.course_id )
        dispatch('showCourseAction', payload.course_id)
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async getAssessmentsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/${payload.course_id}/assessments`).then(res => {
        commit('assessmentsMutation', res.data.data)
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async deleteAssessmentAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/instructor/assessments/${payload.id}`).then(res => {
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async moveAssessmentAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/assessment/${payload.assessment_id}/move-to-course`, payload).then(res => {
        dispatch('showCourseAction', payload.course_id)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async getTagClassAssessmentAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/assessment/${payload.id}/user-access-tags`).then(res => {
        commit('assessmentUsersMutation', res.data.data)
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async tagClassAssessmentAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/assessment/${payload.id}/user-access-tags`, payload).then(res => {
        // dispatch('updateCourseUpdatedAt', payload.id )
        dispatch('updateCourseUpdatedAt', payload.course_id )
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async showTaggedUsersAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/${payload.course_id}/assessment/${payload.id}/class-enrollee`, payload).then(res => {
        commit('assessmentClassesMutation', res.data.data[0].class_categories)
      // let _user_id = []
      // res.data.data.class_categories.forEach(classes => {
      //   classes.enrollments.forEach(element => {
      //     _user_id.push(element.user_id)
      //   });
      // })
      //commit('assessmentUsersMutation', res.data.data[0].class_categories)
      //dispatch('tagClassAssessmentAction', {id: payload.id, course_id: payload.course_id, user_id: _user_id})
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },
  

  async duplicateAssessmentAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/assessment/${payload.assessment_id}/duplicate`).then(res => {
        commit('duplicateIdMutation', res.data.data)
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },
  

  async getInstructorsToShareAction({commit}) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/assessment/instructor/list`).then(res => {
        commit('instructorsMutation', res.data.data)
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
    })
  },

  async shareAssessmentAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/assessment/${payload.assessment_id}/share`, payload).then(res => {
        //commit('instructorsMutation', res.data.data)
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async showSharedToOtherAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/assessment/shared-to-others`, payload).then(res => {
        commit('assessmentsMutation', res.data.data.data)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async showSharedWithMeAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/assessment/shared-with-me`, payload).then(res => {
        commit('assessmentsMutation', res.data.data.data)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async moveAssessmentSharedWithMeAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/assessment/shared-with-me/move`, payload).then(res => {
        // commit('instructorsMutation', res.data.data)
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async exportAssessmentList({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/assessment/${payload.assessment_id}/list/export`, payload, { responseType: 'blob' }).then(res => {
        console.log(res.data)
        resolve(res.data)
      }).catch(err => {
        console.log(err)
        reject(err)
      })
    })
  },

  async exportAssessmentResponses({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/assessment/${payload.assessment_id}/responses/export`, payload, { responseType: 'blob' }).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.message, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },

  async sortAssessmentQuestionsAction({commit}, payload){
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/assessment/${payload.assessment_id}/sort`, payload).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.errors)
      })
    })
  }
  
}