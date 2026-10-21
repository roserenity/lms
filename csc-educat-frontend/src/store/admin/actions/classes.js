import { api } from '@/services/axios'

export default {
  async addClassAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/admin/class-categories', payload).then(res => {
        dispatch('getClassesAction')
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
  async getClassesAction({commit}, payload) {
    let _query = `/admin/class-categories`
    if(payload) {
      _query = _query + `?page=${payload.page}&paginate=${payload.paginate}&search=${payload.search}`
    }
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(_query).then(res => {
        if(!payload) {
          commit('classesMutation', res.data.data)
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

  async updateClassesAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/admin/class-categories/${payload.id}`, payload).then(res => {
        dispatch('getClassesAction')
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

  async deleteClassAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/admin/class-categories/${payload.id}`, payload).then(res => {
        dispatch('getClassesAction')
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
  }
  
}