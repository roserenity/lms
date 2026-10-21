import { api } from '@/services/axios'

export default {

  async getCourseEvaluationsAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/${payload}/evaluations`).then(res => {
        commit('courseEvaluationsMutation', res.data.data)
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

  async showCourseEvaluationAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/evaluations/${payload}`).then(res => {
        commit('evaluationQuestionMutation', res.data.data.course_evaluation_questions)
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

  async addCourseEvaluationAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/course/evaluations', payload.data).then(res => {
        dispatch('showCourseAction', payload.data.course_id)
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

  async updateCourseEvaluationAction({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/course/evaluations/${payload.id}`, payload).then(res => {
        dispatch('getCourseEvaluationsAction', payload.course_id)
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
  
  async courseEvaluationValidityAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/course/evaluation/${payload.id}/validity`, payload).then(res => {
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

  async deleteCourseEvaluationAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/instructor/course/evaluations/${payload.id}`).then(res => {
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

  async getCourseEvaluationQuestionsAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/evaluation/questions/${payload}`).then(res => {
        commit('evaluationQuestionMutation', res.data.data.course_evaluation_questions)
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

  async addCourseEvaluationQuestionAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/course/evaluation/questions`, payload.data).then(res => {
        let _data = res.data.data
        payload.state.push(_data)
        commit('evaluationQuestionMutation', payload.state)
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

  async updateCourseEvaluationQuestionAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/course/evaluation/questions`, payload).then(res => {
        dispatch('showCourseEvaluationAction', payload.course_evaluation_id[0])
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

  async deleteCourseEvaluationQuestionAction({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/instructor/course/evaluation/questions/${payload.data.id}`).then(res => {
        let index = payload.state.findIndex(item => item.id === payload.data.id)
        payload.state.splice(index, 1)
        commit('evaluationQuestionMutation', payload.state)
        // dispatch('showCourseEvaluationAction', payload.course_evaluation_id)
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

  async addCourseEvaluationQuestionImage({commit, dispatch}, payload) {
    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/course/evaluation/question/${payload.id}/image`, payload.form, config).then(res => {
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

  async deleteCourseEvaluationQuestionImage({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/instructor/course/evaluation/question/image/${payload}`, payload).then(res => {
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

  async getCourseEvaluationRespondentsAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/evaluation/${payload.id}/respondents`, { params: payload}).then(res => {
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

  async getCourseEvaluationAnswers({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/evaluation/${payload.id}/answers`, { params: payload}).then(res => {
        commit('evaluationAnswersMutation', res.data.data)
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

  async getCourseEvaluationQuestionAnswers({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    console.log(payload)
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/evaluation/${payload.course_evaluation_id}/${payload.course_evaluation_question_id}/answers`, { params: payload }).then(res => {
        commit('evaluationAnswersMutation', res.data.data)
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

  async exportCourseEvaluationList({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/course/evaluation/list/export`, { ...payload }).then(res => {
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

  async exportCourseEvaluationSummary({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/course/evaluation/summary/export`, { ...payload }).then(res => {
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

  async sortCourseEvaluationQuestionsAction({commit}, payload){
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/course/evaluation/${payload.course_evaluation_id}/sort`, payload).then(res => {
        commit('evaluationQuestionMutation', res.data.data)
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