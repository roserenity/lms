import { api } from '@/services/axios'

export default {
  
  async getModuleEvaluationsAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/module/${payload}/evaluations`).then(res => {
        commit('moduleEvaluationsMutation', res.data.data)
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

  async showModuleEvaluationAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/module/evaluations/${payload}`).then(res => {
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

  async addModuleEvaluationAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/module/evaluations', payload.data).then(res => {
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

  async updateModuleEvaluationAction({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/module/evaluations/${payload.id}`, payload).then(res => {
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

  async moduleEvaluationValidityAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/module/evaluation/${payload.id}/validity`, payload).then(res => {
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

  async deleteModuleEvaluationAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/instructor/module/evaluations/${payload.id}`).then(res => {
        dispatch('getCourseModulesAction', payload.course_id )
        //dispatch('showCourseAction', payload.course_id)
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

  async addModuleEvaluationQuestionAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/module/evaluation/questions`, payload.data).then(res => {
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

  async getModuleEvaluationQuestionsAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/module/evaluation/questions/${payload}`).then(res => {
        commit('evaluationQuestionMutation', res.data.data.module_evaluation_questions)
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

  async updateModuleEvaluationQuestionAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/module/evaluation/questions`, payload).then(res => {
        dispatch('showModuleEvaluationAction', payload.module_evaluation_id[0])
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

  async deleteModuleEvaluationQuestionAction({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/instructor/module/evaluation/questions/${payload.data.id}`).then(res => {
        let index = payload.state.findIndex(item => item.id === payload.data.id)
        payload.state.splice(index, 1)
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

  async addModuleEvaluationQuestionImage({commit, dispatch}, payload) {
    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/module/evaluation/question/${payload.id}/image`, payload.form, config).then(res => {
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

  async deleteModuleEvaluationQuestionImage({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/instructor/module/evaluation/question/image/${payload}`, payload).then(res => {
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

  async getModuleEvaluationRespondentsAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/module/evaluation/${payload.id}/respondents`, { params: payload}).then(res => {
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

  async getModuleEvaluationAnswers({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/module/evaluation/${payload.id}/answers`, { params: payload}).then(res => {
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

  async getModuleEvaluationQuestionAnswers({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/module/evaluation/${payload.module_evaluation_id}/${payload.module_evaluation_question_id}/answers`, { params: payload }).then(res => {
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

  async exportModuleEvaluationList({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/module/evaluation/list/export`, { ...payload }).then(res => {
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

  async exportModuleEvaluationSummary({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/module/evaluation/summary/export`, { ...payload }).then(res => {
        resolve(res.data);
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

  async sortModuleEvaluationQuestionsAction({commit}, payload){
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/module/evaluation/${payload.module_evaluation_id}/sort`, payload).then(res => {
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