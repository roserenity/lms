import { api } from '@/services/axios'

export default {
  async getCertificateTemplateCategoryAction({commit}, payload) {
    let _query = `/admin/certificate-template-category`
    if(payload) {
      _query = _query + `?page=${payload.page}&paginate=${payload.paginate}&search=${payload.search}`
    }
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(_query).then(res => {
        commit('certificateTemplateMutation', res.data.data.data)
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

  async addCertificateCertificateTemplateAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/certificate-template-category`, payload).then(res => {
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

  async deleteCertificateTemplateCategoryAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/admin/certificate-template-category/${payload.id}`).then(res => {
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

}