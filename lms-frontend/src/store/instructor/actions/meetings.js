import { api } from '@/services/axios'

export default {
  async addMeetingAction({commit, dispatch}, payload) {
    // await api.post('instructor/zoom-meetings/request/access_token')
    // .then((res) => {
    //     console.log(res.data.data)
        // let _access_token = res.data.data
        // payload.access_token = _access_token
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('instructor/zoom-meetings', payload).then(res => {
        dispatch('getMeetingsAction', {course_id: payload.course_id})
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
        // await api.post('instructor/zoom-meetings', payload).then((res) => {
        //     console.log(res.data.data)
        //     dispatch('getMeetingsAction', {course_id: payload.course_id})
        // }).catch((err) => {
        //     console.log(err);
        // });
  },

  async getMeetingsAction({commit}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`instructor/zoom-meetings`, { params: { course_id: payload.course_id }}).then(res => {
        commit('meetingsMutation', res.data.data)
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
    // await api.get('instructor/zoom-meetings').then((res) => {
    //     commit('meetingsMutation', res.data.data.filter(_meeting => _meeting.course_id == payload.course_id))
    // }).catch((err) => {
    //     console.log(err);
    // });
  },

  async deleteMeetingAction({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`instructor/zoom-meetings/${payload.id}`).then(res => {
        dispatch('getMeetingsAction', {course_id: payload.course_id})
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
    // await api.delete(`instructor/zoom-meetings/${payload.id}`).then((res) => {
    //   dispatch('getMeetingsAction', {course_id: payload.course_id})
    // }).catch((err) => {
    //     console.log(err);
    // });
  },

  async updateMeetingAction({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`instructor/zoom-meetings/${payload.id}`, payload).then(res => {
        dispatch('getMeetingsAction', {course_id: payload.course_id})
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
    // await api.patch(`instructor/zoom-meetings/${payload.id}`, payload).then((res) => {
    //   dispatch('getMeetingsAction', {course_id: payload.course_id})
    // }).catch((err) => {
    //     console.log(err);
    // });
  },
  
  async getMeetingParticipantsAction({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`instructor/${payload.course_id}/zoom-meetings/participants?zoom_meeting_id=${payload.zoom_meeting_id}&paginate=${payload.paginate}&page=${payload.page}&search=${payload.search}&status=${payload.status}`).then(res => {
        commit('meetingParticipantsMutation', res.data.data.data)
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

  async updateMeetingParticipantsAttendanceStatusAction({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`instructor/${payload.course_id}/zoom-meetings/participants/attendance/update`, payload).then(res => {
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

  async exportMeetingParticipantsAttendanceAction({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`instructor/${payload.course_id}/zoom-meetings/participants/attendance/update`, payload).then(res => {
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

  async exportMeetingParticipantsAction({commit, dispatch}, payload){ 
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`instructor/${payload.course_id}/zoom-meetings/participants/export`, payload).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response, 
          { root: true }
        )
        reject(err.response)
      })
    })
  },

  async getMeetingParticipantAction({commit, dispatch}, payload){ 
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`instructor/zoom-meetings/${payload.zoom_meeting_id}/participant/${payload.user_id}`).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response, 
          { root: true }
        )
        reject(err.response)
      })
    })
  },

  async postMeetingParticipantScoreAndCommentAction({commit, dispatch}, payload){ 
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`instructor/zoom-meetings/${payload.zoom_meeting_id}/participant/${payload.user_id}`, payload).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response, 
          { root: true }
        )
        reject(err.response)
      })
    })
  }

}