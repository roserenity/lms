import { api } from '@/services/axios'

export default {
  async getNotificationAction({commit, state}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/user/notifications?page=${payload.page}`).then(res => {
        if(payload.page === 1 || !payload.page) {
          commit('notificationMutation', res.data.data.notifications.data)
        } else {
          commit('notificationMutation', [ ...state.notification, ...res.data.data.notifications.data ])
        }
        resolve(res.data.data)
      }).catch(err => {
          reject(err.response.data.message)
      })
    })
  },

  async markNotification({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/mark/notification`, payload).then(res => {
          resolve(res.data.data)
      }).catch(err => {
          reject(err.response.data.message)
      })
    })
  },

  async markAllAsReadAction({commit, state}){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/user/mark/all/notifications`).then(res => {
        let _notification = state.notification.map(item => {
          return {
            ...item,
            read_at: new Date().toISOString()
          };
        });
        commit('notificationMutation', _notification)

        resolve(res.data.data)
      }).catch(err => {
          reject(err.response.data.message)
      })
    })
  }
}