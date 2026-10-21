import { api } from '@/services/axios'

export default {
  async addTenantAction({commit, dispatch}, payload) {
    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/super-admin/tenants', payload, config).then(res => {
        dispatch('getTenantsAction')
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
  async getTenantsAction({commit, dispatch}, payload) {

    commit('loadingMutation', true)
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get('/super-admin/tenants', {params: { ...payload }}).then(res => {
        let _res = res.data.data.data
      
        _res.forEach(i => {
          i.organization = i.info.organization
          i.email = i.info.email
        })
        commit('tenantsMutation', _res)
        commit('loadingMutation', false)
        resolve(res.data.data)
      }).catch(err => {
        commit('loadingMutation', false)
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },
  async showTenantAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/super-admin/tenants/${payload}`).then(res => {
        commit('showMutation', res.data.data, { root: true })
        resolve( res.data.data)
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
  async updateStatusTenantAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/super-admin/tenants/update/status', payload).then(res => {
        resolve( res.data)
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
  async updateTenantAction({commit, dispatch}, payload) {
    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/super-admin/tenants/${payload.id}`, payload.formm, config).then(res => {
        dispatch('getTenantsAction')
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

  async removeTenantPhotoAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/super-admin/tenants/remove/photo/${payload.id}`, payload).then(res => {
        dispatch('getTenantsAction')
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