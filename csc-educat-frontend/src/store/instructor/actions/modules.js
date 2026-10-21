import { api } from '@/services/axios'

export default {
  async getCourseModulesAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/${payload}/modules`).then(res => {
        commit('modulesMutation', res.data.data)
        resolve('res.data.data')
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
  
  async addModuleAction({commit, dispatch}, payload) {
    await api.post('/instructor/modules', payload)
      .then(res => {
        //dispatch('getCourseModulesAction', payload.course_id)
        dispatch('updateCourseUpdatedAt', payload.course_id )
        dispatch('getCourseModulesAction', payload.course_id )
        //dispatch('showCourseAction', payload.course_id)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
      })
  },

  async updateModuleAction({commit, dispatch}, payload) {
    await api.put(`/instructor/modules/${payload.module_id}`, payload)
    .then(res => {
      dispatch('updateCourseUpdatedAt', res.data.data.course_id )
      dispatch('getCourseModulesAction', payload.course_id )
      //dispatch('showCourseAction', payload.course_id)
    })
    .catch(err => {
      return commit(
        'errorsMutation', 
        err.response.data.errors, 
        { root: true }
      )
    })
  },

  async sortModuleAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/module/sort`, payload).then(res => {
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

  async moduleValidityAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/module/${payload.id}/validity`, payload).then(res => {
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

  async deleteModuleAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/instructor/modules/${payload.id}`).then(res => {
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

  async showModulePrerequisiteAction({commit, dispatch}, payload) {
    await api.get(`/instructor/modules/${payload}`)
    .then(res => {
      commit('modulesMutation', res.data.data)
      dispatch('updateCourseUpdatedAt', payload )
      dispatch('showCourseAction', payload.course_id)
    })
    .catch(err => {
      return commit(
        'errorsMutation', 
        err.response.data.errors, 
        { root: true }
      )
    })
  },

  async addModulePrerequisiteAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/module/sync/prerequisites`, payload).then(res => {
        resolve('res.data.data')
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

  async getModuleFieldsAction({ commit, dispatch }, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/module/fields`).then(res => {
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

  async addModuleEvaluationAction({commit, dispatch}, payload) {
    commit('moduleEvaluationsMutation', payload)
  },

  async updateModuleEvaluationQuestionAction({commit}, payload) {
    commit('evaluationQuestionMutation', payload)
  },

  async updateModuleEvaluationAction({commit, dispatch}, payload) {
    commit('moduleEvaluationsMutation', payload)
  },

  async deleteModuleEvaluationAction({commit, dispatch}, payload) {
    commit('moduleEvaluationsMutation', payload)
  },

} 