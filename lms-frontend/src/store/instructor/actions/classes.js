import { api } from '@/services/axios'

export default {
  async addClassAction({commit, dispatch}, payload) {
    await api.post('/admin/class-categories', payload)
    .then(res => {
      commit('successMutation', [], { root: true })
      dispatch('getClassesAction', { root: true })
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
  async getClassesAction({commit}) {
    await api.get('/instructor/class-categories')
    .then(res => {
      commit('classesMutation', res.data.data)
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