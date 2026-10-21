import { type_of_learning } from '@/constants/typeoflearning';
import { middleware } from './middleware'

const routes = [
  { name: 'Instructor Dashboard', query: { search: '', page: 1, paginate: 10, type_of_learning: ''} },
  { name: 'Instructor Published Courses', query: { search: '', page: 1, paginate: 10, type_of_learning: '' } }
];

function randomRedirect(routes) {
  const randomRoute = routes[Math.floor(Math.random() * routes.length)];
  return randomRoute;
}

export const TeacherRoutes = [
  {
    path: '/instructor',
    component: () => import('../layouts/Main.vue'),
    name: 'Instructor Layout',
    meta: { middleware: [middleware] },
    redirect: randomRedirect(routes),
    children: [
      {
        path: '/instructor/dashboard',
        name: 'Instructor Dashboard',
        component: () => import('../views/teacher/Dashboard.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Dashboard',
          collapse: false,
        },
      },
      {
        path: '/instructor/my-courses/',
        name: 'Instructor My Courses',
        component: () => import('../views/teacher/Courses.vue'),
        redirect: { name: 'Instructor Uploaded Course' },
        meta: { 
          middleware: [middleware],
          parent: 'Instructor My Courses',
          collapse: true,
        },
        children: [
          {
            path: 'published',
            name: 'Instructor Published Courses',
            component: () => import('../views/teacher/ViewCourses.vue'),
            meta: { 
              middleware: [middleware],
              parent: 'Instructor My Courses',
              collapse: true,
            },
          },
          {
            path: 'unpublished',
            name: 'Instructor Unpublished Courses',
            component: () => import('../views/teacher/ViewCourses.vue'),
            meta: { 
              middleware: [middleware],
              parent: 'Instructor My Courses',
              collapse: true,
            },
          },
          {
            path: 'drafts',
            name: 'Instructor Drafted Courses',
            component: () => import('../views/teacher/ViewCourses.vue'),
            meta: { 
              middleware: [middleware],
              parent: 'Instructor My Courses',
              collapse: true,
            },
          },
          {
            path: ':status/manage/:id',
            name: 'Instructor Manage Course',
            component: () => import('../views/teacher/CourseManager.vue'),
            meta: { 
              middleware: [middleware],
              parent: 'Instructor My Courses',
              collapse: true,
            },
          },
        ]
      },
      {
        path: '/instructor/assessments/',
        name: 'Instructor Assessment',
        component: () => import('../views/teacher/AssessmentBank.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Assessment',
          collapse: false,
        },
      },
      {
        path: '/instructor/rubrics/',
        name: 'Instructor Rubric',
        component: () => import('../views/teacher/RubricBank.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Rubric',
          collapse: false,
        },
        children: [
          {
            path: ':id/edit',
            name: 'Instructor Rubric Edit',
            component: () => import('../views/teacher/Rubric.vue'),
            meta: { 
              middleware: [middleware],
              parent: 'Instructor Rubric',
              collapse: false,
            },
          },
          {
            path: ':id/view',
            name: 'Instructor Rubric View',
            component: () => import('../views/teacher/Rubric.vue'),
            meta: { 
              middleware: [middleware],
              parent: 'Instructor Rubric',
              collapse: false,
            },
          }
        ]
      },
      {
        path: '/instructor/grading',
        name: 'Instructor Grading',
        component: () => import('../views/teacher/Grading.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Grading',
          collapse: false,
        },
      },
      {
        path: '/instructor/grading/:id/scorm/:scorm_id',
        name: 'Instructor Scorm',
        component: () => import('../views/teacher/GradingScormSummary.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Grading',
          collapse: false,
        },
      },
      {
        path: '/instructor/grading/:id/scorm/:scorm_id/registration/:scorm_registration_id/user/:user_id',
        name: 'Instructor Scorm Student',
        component: () => import('../views/teacher/GradingScormStudent.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Grading',
          collapse: false,
        },
      },
      // {
      //   path: '/instructor/grading/:id/scorm_activities/:scorm_activity_id',
      //   name: 'Instructor Scorm Activity',
      //   component: () => import('../views/teacher/GradingScormActivitySummary.vue'),
      //   meta: { 
      //     middleware: [middleware],
      //     parent: 'Instructor Grading',
      //     collapse: false,
      //   },
      // },
      // {
      //   path: '/instructor/grading/:id/scorm_activities/:scorm_activity_id/user/:user_id',
      //   name: 'Instructor Scorm Activity Student',
      //   component: () => import('../views/teacher/GradingScormActivityStudent.vue'),
      //   meta: { 
      //     middleware: [middleware],
      //     parent: 'Instructor Grading',
      //     collapse: false,
      //   },
      // },
      {
        path: '/instructor/grading/:id/survey/:type/:survey_id',
        name: 'Instructor Survey',
        component: () => import('../views/teacher/GradingSurvey.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Survey',
          collapse: false,
        },
      },
      {
        path: '/instructor/:id/survey/:type/:survey_id/:student_id',
        name: 'Instructor Survey Student',
        component: () => import('../views/teacher/GradingSurveyStudent.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Survey',
          collapse: false,
        },
      },
      {
        path: '/instructor/grading/:id/group_activity/:group_activity_id',
        name: 'Instructor Group Activity',
        component: () => import('../views/teacher/GradingGroupActivity.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Grading',
          collapse: false,
        },
      },
      {
        path: '/instructor/grading/:id/group_activity/:group_activity_id/:group_id',
        name: 'Instructor Group Activity Group',
        component: () => import('../views/teacher/GradingGroupActivityGroup.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Grading',
          collapse: false,
        },
      },
      {
        path: '/instructor/grading/:id/:type',
        name: 'Instructor Grading Course',
        component: () => import('../views/teacher/GradingAssessment.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Grading',
          collapse: false,
        },
      },
      {
        path: '/instructor/grading/:course_id/assessment/:id',
        name: 'Instructor Grading Assessment',
        component: () => import('../views/teacher/GradingAssessmentSummary.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Grading',
          collapse: false,
        },
      },
      {
        path: '/instructor/grading/:course_id/assessment/:id/:user_assessment_id/user/:user_id',
        name: 'Instructor Grading Assessment User',
        component: () => import('../views/teacher/GradingStudent.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Grading',
          collapse: false,
        },
      },
      {
        path: '/instructor/enrollment',
        name: 'Instructor Enrollment',
        component: () => import('../views/teacher/Enrollment.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Enrollment',
          collapse: false,
        },
      },
      {
        path: '/instructor/enrollment/:course_uuid',
        name: 'Instructor User Enrollment',
        component: () => import('../views/teacher/EnrollmentCourse.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Enrollment',
          collapse: false,
        },
      },
      {
        path: '/instructor/question-pools/',
        name: 'Instructor Question Pools',
        component: () => import('../views/teacher/QuestionPools.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Question Pools',
          collapse: true,
        },
      },
      {
        path: '/instructor/question-pools/:id',
        name: 'Instructor Question Pool Edit',
        component: () => import('../views/teacher/QuestionPool.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Question Pools',
          collapse: true,
        },
      },
      {
        path: '/instructor/inbox',
        name: 'Instructor Inbox',
        component: () => import('../views/teacher/Inbox.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Inbox',
          collapse: false,
        },
      },
      
      {
        path: '/instructor/student-outputs',
        name: 'Instructor Student Outputs',
        component: () => import('../views/teacher/StudentOutput1.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Student Outputs',
          collapse: false,
        },
      },

      {
        path: '/instructor/student-outputs/:course_uuid/enrollees',
        name: 'Instructor Student Outputs Enrollees',
        component: () => import('../views/teacher/StudentOutputEnrollees.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Student Outputs',
          collapse: false,
        },
      },

      {
        path: '/instructor/student-outputs/:course_uuid/enrollee/:user_id',
        name: 'Instructor Student Outputs Enrollee Progress',
        component: () => import('../views/teacher/StudentOutputEnrolleeProgress.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Student Outputs',
          collapse: false,
        },
      },

      {
        path: '/instructor/reporting',
        name: 'Instructor Reporting',
        component: () => import('../views/teacher/Reporting.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Reporting',
          collapse: false,
        },
      },

      {
        path: '/instructor/trash',
        name: 'Instructor Trash',
        component: () => import('../views/teacher/Trash.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Trash',
          collapse: false,
        },
      },
      // {
      //   path: '/teacher/my-courses/:id',
      //   name: 'Teacher Course Details',
      //   component: () => import('../views/teacher/CourseDetails.vue'),
      // },
      // {
      //   path: '/teacher/student-outputs',
      //   name: 'Teacher Student Outputs',
      //   component: () => import('../views/teacher/StudentOutput.vue'),
      // },
      {
        path: '/instructor/announcements',
        name: 'Instructor Announcements',
        component: () => import('../views/teacher/Announcements.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Announcements',
          collapse: false,
        },
      },
      {
        path: '/instructor/course-groups',
        name: 'Instructor Course Groups',
        component: () => import('../views/teacher/CourseGroups.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Course Groups',
          collapse: false,
        },
      },
      {
        path: '/instructor/course-group/:id',
        name: 'Instructor Course Group',
        component: () => import('../views/teacher/CourseGroup.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Course Group',
          collapse: false,
        },
      },
      {
        path: '/instructor/forum',
        name: 'Instructor Forum',
        component: () => import('../views/common/Forum.vue'),
        meta: { 
          middleware: [middleware],
          parent: 'Instructor Forum',
          collapse: false,
        },
      },
      {
        path: '/instructor/account-settings',
        name: 'Instructor Settings',
        component: () => import('../views/teacher/AccountSettings.vue'),
        redirect: {name: 'Instructor Account Profile'},
        meta: {
          middleware: [middleware],
          parent: 'Instructor Settings',
          collapse: false
        },
        children: [
          {
            path: 'profile',
            name: 'Instructor Account Profile',
            component: () => import('../views/user/Profile.vue'),
            meta: {
              parent: 'Instructor Settings',
              collapse: false,
              middleware: [middleware],
            },
          },
          {
            path: 'change-password',
            name: 'Instructor Change Password',
            component: () => import('../views/user/ChangePassword.vue'),
            meta: {
              parent: 'Instructor Settings',
              collapse: false,
              middleware: [middleware],
            },
          }
        ]
      },
      {
        path: '/instructor/activity-logs',
        name: 'Instructor Activity Logs',
        component: () => import('../views/teacher/ActivityLogs.vue'),
        meta: {
          childname: 'Instructor Activity Logs',
          collapse: false,
          middleware: [middleware]
        }
      },
      {
        path: '/instructor/exports',
        name: 'Instructor Exports',
        component: () => import('../views/teacher/Exports.vue'),
        meta: {
          childname: 'Instructor Exports',
          collapse: false,
          middleware: [middleware]
        }
      },
      {
        path: '/instructor/certificate-templates',
        name: 'Instructor Certificate Templates',
        component: () => import('../views/teacher/CertificateTemplates.vue'),
        meta: {
          collapse: false,
          middleware: [middleware],
          parent: 'Instructor Certificate Templates',
        },
      },
      {
        path: '/instructor/certificate-templates/create',
        name: 'Instructor Certificate Template Create',
        component: () => import('../views/teacher/CertificateTemplate.vue'),
        meta: {
          collapse: false,
          middleware: [middleware],
          parent: 'Instructor Certificate Templates',
        },
      },
      {
        path: '/instructor/certificate-templates/:id/view',
        name: 'Instructor Certificate Template View',
        component: () => import('../views/teacher/CertificateTemplate.vue'),
        meta: {
          collapse: false,
          middleware: [middleware],
          parent: 'Instructor Certificate Templates',
        },
      },
      {
        path: '/instructor/certificate-templates/:id/edit',
        name: 'Instructor Certificate Template Edit',
        component: () => import('../views/teacher/CertificateTemplate.vue'),
        meta: {
          collapse: false,
          middleware: [middleware],
          parent: 'Instructor Certificate Templates',
        },
      },
      // {
      //   path: '/teacher/assessment/:id',
      //   name: 'Teacher Assessment Details',
      //   component: () => import('../views/teacher/AssessmentDetails.vue'),
      // },
      // {
      //   path: '/teacher/ticket',
      //   name: 'Teacher Manage Ticket',
      //   component: () => import('../views/teacher/Ticket.vue'),
      // },
      // {
      //   path: '/teacher/settings',
      //   name: 'Teacher Settings',
      //   component: () => import('../views/teacher/Settings.vue'),
      //   redirect: { name: 'Teacher Account Setting' },
      //   children: [
      //     {
      //       path: 'account',
      //       name: 'Teacher Account Setting',
      //       component: () => import('../views/teacher/Account.vue')
      //     }
      //   ]
      // },
    ]
  }
]