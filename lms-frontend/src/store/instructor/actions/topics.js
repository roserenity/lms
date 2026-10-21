import { api } from '@/services/axios'

export default {
  async addTopicAction({commit, dispatch}, payload) {
    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/topics', payload.form, config).then(res => {
        dispatch('updateCourseUpdatedAt', payload.course_id )
        dispatch('showCourseAction', payload.course_id)
        resolve(res.data)
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

  async getTopicsAction({commit}) {
    await api.get('/instructor/topics')
    .then(res => {
      // commit('classesMutation', res.data.data)
    })
    .catch(err => {
      return commit(
        'errorsMutation', 
        err.response.data.errors, 
        { root: true }
      )
    })
  },

  async deleteTopicAction({commit, dispatch}, payload) {
    await api.delete(`/instructor/topics/${payload.id}`)
    .then(res => {
      dispatch('updateCourseUpdatedAt', payload.course_id )
      dispatch('showCourseAction', payload.course_id)
    })
    .catch(err => {
      return commit(
        'errorsMutation', 
        err.response.errors, 
        { root: true }
      )
    })
  },

  async sortModuleContentAction({commit, dispatch}, payload) {
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/module-content/sort`, payload).then(res => {
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
  },

  async sortTopicAction({commit, dispatch}, payload) {
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/topic/sort`, payload).then(res => {
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
  },

  async updateTopicAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/topics/${payload.id}`, payload.form).then(res => {
        dispatch('updateCourseUpdatedAt', payload.course_id )
        dispatch('showCourseAction', payload.course_id)
        resolve(res.data)
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
}