import { api } from '@/services/axios'

export default {
  async getCalendarEventsAction({commit}, payload) {
    await api.post('/user/calendar/events', payload)
    .then((res) => {
        commit('calendarMutation', res.data.data)
    }).catch((err) => {
        console.log(err);
    });
  },
}