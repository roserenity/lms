import { api } from '@/services/axios'

export default {

  async getCertificateTemplatesAction({ commit, dispatch }, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/certificate-template`, { params: payload }).then(res => {

        if(payload.paginate) {
          commit('certificateTemplatesMutation', res.data.data.data)
        } else {
          commit('certificateTemplatesMutation', res.data.data)
        }
        resolve(res.data.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit('errorsMutation', err.response.data.errors, { root: true })
        } else {
          commit('errorsMutation', err.response.data.message, { root: true })
        }
        reject(err.response.data.message)
      })
    })
  },

  async getCertificateTemplateAction({ commit, dispatch }, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/certificate-template/${payload}`).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit('errorsMutation', err.response.data.errors, { root: true })
        } else {
          commit('errorsMutation', err.response.data.message, { root: true })
        }
        reject(err.response.data.message)
      })
    })
  },

  async addCertificateTemplateAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/certificate-template`, payload).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit('errorsMutation', err.response.data.errors, { root: true })
        } else {
          commit('errorsMutation', err.response.data.message, { root: true })
        }
        reject(err.response.data.message)
      })
    })
  },

  async updateCertificateTemplateAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/certificate-template/${payload.id}`, payload.data).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit('errorsMutation', err.response.data.errors, { root: true })
        } else {
          commit('errorsMutation', err.response.data.message, { root: true })
        }
        reject(err.response.data.message)
      })
    })
  },

  async getCertificateTemplateAction({ commit, dispatch }, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/certificate-template/${payload}`).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit('errorsMutation', err.response.data.errors, { root: true })
        } else {
          commit('errorsMutation', err.response.data.message, { root: true })
        }
        reject(err.response.data.message)
      })
    })
  },

  /* async updateCertificateTemplateAction({ commit, dispatch }, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/certificate-template/${payload.id}`, payload).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit('errorsMutation', err.response.data.errors, { root: true })
        } else {
          commit('errorsMutation', err.response.data.message, { root: true })
        }
        reject(err.response.data.message)
      })
    })
  }, */
  
  async duplicateCertificateTemplateAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/certificate-template/${payload}/duplicate`).then(res => {
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

  async deleteCertificateTemplateAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/instructor/certificate-template/${payload}`).then(res => {
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

  async getParticipantCertificatesAction({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`instructor/course/${payload.course_id}/certificates/enrollees?paginate=${payload.paginate}&page=${payload.page}&search=${payload.search}`).then(res => {
        res.data.data.data.forEach(item => {
          if (item.user.certificate_template_categories.length > 0){
            let _ids = item.user.certificate_template_categories.map(item => item.id)
            item.user.certificate_template_categories = _ids
          } else {
            item.user.certificate_template_categories = [1]
          }
        })
        commit('enrolleesMutation', res.data.data.data)
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

  async getCertificateTemplateCategory({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`instructor/certificate-template/category/list`).then(res => {

        res.data.data.forEach((item) => {
          item.text = item.name
          item.value = item.id
        })

        resolve(res.data.data)
      }).catch(err => {
        reject(err.response.data)
      })
    })
  },
  
  async setLearnerCertificateAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`instructor/course/${payload.course_id}/certificates/enrollees/assign`, payload).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err.response.data)
      })
    })
  }

}