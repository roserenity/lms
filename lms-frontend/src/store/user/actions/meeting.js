import { api } from '@/services/axios'

export default {
    async getMeetingAction({commit}, payload) {
        await api.get(`/user/zoom-meetings/${payload}`, payload).then((res) => {
          commit('meetingMutation', res.data.data)
          //dispatch('getMeetingsAction', {course_id: payload.course_id})
        }).catch((err) => {
            console.log(err);
        });
      }
    }