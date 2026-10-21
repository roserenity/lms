import { api } from '@/services/axios'
import { announcementsMutation } from '../mutations';

export default {
  async getAnnouncementAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
        api.get(`/user/announcements/${payload}`).then(res => {
            commit('announcementsMutation', [res.data.data])
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
  async getCourseAnnouncementsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
        api.get(`/user/course/${payload}/announcements`).then(res => {
            commit('announcementsMutation', res.data.data)
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