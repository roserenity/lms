import { api } from '@/services/axios'

export default {
  async getRegistrationTablesAction({commit}) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get('/admin/custom-registration/pages').then(res => {
        commit('registrationTablesMutation', res.data.data.sort((a, b) => a.sort_no - b.sort_no ))
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
  
  async addRegistrationTableAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/admin/custom-registration/pages', payload).then(res => {
        dispatch('getRegistrationTablesAction')
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

  async addFieldAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/custom-registration/fields`, payload).then(res => {
        dispatch('getRegistrationTablesAction')
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

  async updateFieldAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/admin/custom-registration/fields/${payload.custom_registration_field_id}`, payload).then(res => {
        dispatch('getRegistrationTablesAction')
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

  async changeColumnPositionAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/admin/sort/custom-registration/fields', payload).then(res => {
        dispatch('getRegistrationTablesAction')
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

  async deleteTableAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/admin/custom-registration/pages/${payload.custom_registration_page_id}`).then(res => {
        dispatch('getRegistrationTablesAction')
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

  async deleteFieldAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/admin/custom-registration/fields/${payload}`).then(res => {
        dispatch('getRegistrationTablesAction')
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

  async sortTableAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/admin/sort/custom-registration/pages', payload).then(res => {
        dispatch('getRegistrationTablesAction')
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

  async getRegistrationFieldsWithOptionsAction(){
    return await new Promise((resolve, reject) => {
      api.get(`/admin/custom-registration/field/lists-options`).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err.response.data.errors)
      })
    })
  },
}