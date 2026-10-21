import { api } from '@/services/axios'

export default {

    async getStudentOutputCoursesAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/student-output/courses`, { params: payload }).then(res => {
                res.data.data.data.forEach(item => {
                    if(item.class_categories.length > 0) {
                      item.class_name = `${ item.class_categories[0].class_code } - ${ item.class_categories[0].class_name }`
                    } else {
                      item.class_name = ''
                    }
                })
                commit('coursesMutation', res.data.data.data)
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

    async getEnrolleesAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/student-output/course/${payload.course_uuid}/enrollees`, { params: payload }).then(res => {
                res.data.data.users.data.forEach(user => {
                    user.name =  `${user.first_name ?? ''} ${user.last_name ?? ''} ${user.suffix ?? ''}`
                    user.progress =  user.course_progress.length > 0 ? user.course_progress[0].progress : 0
                })
                commit('enrolleesMutation', res.data.data.users.data)
                resolve({ title: res.data.data.title, ...res.data.data.users })
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

    async enrolleesExportAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/student-output/course/${payload.course_uuid}/enrollees/export`, { params: payload }, { responseType: 'blob' }).then(res => {
                resolve(res.data)
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

    async getEnrolleeProgressAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/student-output/course/${payload.course_uuid}/enrollees/${payload.user_id}/progress`).then(res => {
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

    async getEnrolleeZoomMeetingProgressAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/student-output/course/${payload.course_uuid}/enrollees/${payload.user_id}/progress/zoom-meeting`).then(res => {
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
    },

    async getEnrolleeCourseProgressAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/student-output/course/${payload.course_uuid}/enrollees/${payload.user_id}/progress/course`).then(res => {
                commit('modulesMutation', res.data.data.modules)
                commit('courseEvaluationsMutation', res.data.data.course_evaluations)
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

    async getEnrolleeOtherCoursesProgressAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/student-output/courses/enrollees/${payload.user_id}/progress/courses`, { params: payload }).then(res => {
                let _courses = []
                res.data.data.data.forEach(item => {
                    _courses.push(item.course)
                })
                commit('coursesMutation', _courses)
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

    async setEnrolleeCourseTopicMarkAsCompletedAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/student-output/courses/enrollees/${payload.user_id}/topic/${payload.topic_id}/mark-as-completed`).then(res => {
                resolve(res.data)
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

    async setEnrolleeCourseSubtopicMarkAsCompletedAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/student-output/courses/enrollees/${payload.user_id}/sub-topic/${payload.subtopic_id}/mark-as-completed`).then(res => {
                resolve(res.data)
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