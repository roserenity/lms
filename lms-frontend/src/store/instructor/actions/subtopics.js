import { api } from '@/services/axios'

export default {
  async addSubtopicAction({commit, dispatch}, payload) {
    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/sub-topics', payload.form, config).then(res => {
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

  async getSubtopicsAction({commit}) {
    await api.get('/instructor/sub-topics')
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

  async deleteSubtopicAction({commit, dispatch}, payload) {
    await api.delete(`/instructor/sub-topics/${payload.id}`)
    .then(res => {
      dispatch('updateCourseUpdatedAt', payload.course_id )
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

  async updateSubTopicAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/sub-topics/${payload.id}`, payload.form).then(res => {
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

  async sortSubTopicAction({commit, dispatch}, payload) {
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/sub-topic/sort`, payload).then(res => {
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
  
}