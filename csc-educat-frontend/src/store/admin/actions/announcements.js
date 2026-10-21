import { api } from '@/services/axios'

export default {
    async getInstructorAnnouncementAction({commit}, payload) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.get(`/admin/announcement/${payload}`).then(res => {
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

    async getInstructorAllAnnouncementsAction({commit}, payload) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.get(`/admin/announcements`, { params: payload }).then(res => {
          commit('announcementsMutation', res.data.data.data)
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

    async createAccouncementAction({commit, dispatch}, payload) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.post(`/admin/announcements`, payload).then(res => {
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

    async updateAnnouncementAction({commit}, payload) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.put(`/admin/announcements/${payload.id}`, payload).then(res => {
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

    async deleteAnnoucementAction({commit}, payload) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.delete(`/admin/announcements/${payload}`).then(res => {
          resolve(res)
        }).catch(err => {
          reject(err)
        })
      })
    },

    async submitCommentAction({commit}, payload) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.post(`/admin/announcement/comments`, payload).then(res => {
          resolve(res)
        }).catch(err => {
          reject(err)
        })
      })
    }
}