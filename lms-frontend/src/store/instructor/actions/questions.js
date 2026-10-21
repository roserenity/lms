import { api } from '@/services/axios'

export default {
  async addQuestionAction({commit, dispatch}, payload) {
    await api.post('/instructor/assessment/questions', payload)
    .then(res => {
      dispatch('getAssessmentsAction', { course_id: payload.course_id })
      dispatch('updateCourseUpdatedAt', payload.course_id )
    })
    .catch(err => {
      commit(
        'errorsMutation', 
        err.response.data.errors, 
        { root: true }
      )
    })
  },

  async updateQuestionAction({commit, dispatch}, payload) {
    await api.put(`/instructor/assessment/questions/${payload.id}`, payload)
    .then(res => {
      dispatch('updateCourseUpdatedAt', payload.course_id )
      console.log(res.data);
    })
    .catch(err => {
      commit(
        'errorsMutation', 
        err.response.data.errors, 
        { root: true }
      )
    })
  },

  async removeQuestionAction({commit, dispatch}, payload) {
    await api.delete(`/instructor/assessment/questions/${payload.id}`)
    .then(res => {
      dispatch('updateCourseUpdatedAt', payload.course_id )
      console.log(res.data);
    })
    .catch(err => {
      commit(
        'errorsMutation', 
        err.response.data.errors, 
        { root: true }
      )
    })
  },

  async getQuestionsAction({commit}) {
    await api.get('/instructor/assessment/questions')
    .then(res => {
      // commit('classesMutation', res.data.data)
    })
    .catch(err => {
      return commit(
        'errorsMutation', 
        err.response.data.errors, 
        { root: true }
      )
    })
  },

  
}