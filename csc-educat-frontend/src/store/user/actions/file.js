import { api } from '@/services/axios'

export default {
  getFilesAction({commit}, payload) {
    commit('filesMutation', res.data.data)
  }

//   async getFilesAction({commit}, payload) {
//     await api.get(`/user/assessments/${payload}`)
//     .then((res) => {
//       commit('assessmentsMutation', res.data.data)
//     }).catch((err) => {
//       console.log(err);
//     });
//   },

}