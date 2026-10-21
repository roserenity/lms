import { middleware } from './middleware'

const routes = [
  { name: 'User Dashboard' },
  { name: 'User Profile' },
];

function randomRedirect(routes) {
  const randomRoute = routes[Math.floor(Math.random() * routes.length)];
  return randomRoute;
}

export const UserRoutes = [
  
  // {
  //   path: '/course/:id/join-meeting/:mn',
  //   name: 'User Course Zoom Meeting',
  //   component: () => import('../views/user/CourseZoomMeeting.vue'),
  //   meta: {
  //     middleware: [middleware],
  //     parent: 'User Course Zoom Meeting',
  //     collapse: true,
  //   },
  // },
  // {
  //   path: '/course/:id/join/:mn',
  //   name: 'User Course Meeting',
  //   component: () => import('../views/user/ZoomMeeting.vue'),
  //   meta:{ 
  //     middleware: [middleware],
  //     collapse: false
  //   }
  // },
  // {
  //   path: '/course/:id/meeting/leave',
  //   name: 'User Course Zoom Meeting Leave',
  //   component: () => import('../views/user/CourseZoomMeetingLeave.vue'),
  //   meta: {
  //     middleware: [middleware],
  //     parent: 'User Course Zoom Meeting Leave',
  //     collapse: true,
  //   },
  // },
  {
    path: '/dashboard',
    component: () => import('../layouts/Main.vue'),
    name: 'User Layout',
    redirect: randomRedirect(routes),
    children: [
      {
        path: '',
        name: 'User Dashboard',
        component: () => import('../views/user/Dashboard.vue'),
        meta: {
          middleware: [middleware],
          parent: 'User Dashboard',
          collapse: false
        }
      },
      {
        path: '/calendar',
        name: 'User Calendar',
        component: () => import('../views/user/Calendar.vue'),
        meta: {
          middleware: [middleware],
          parent: 'User Calendar',
          collapse: false,
        }
      },
      {
        path: '/course/:id',
        name: 'User Course Details',
        component: () => import('../views/user/CourseDetailsFix.vue'),
        meta: {
          middleware: [middleware],
          parent: 'User My Courses',
          collapse: false,
        },
      },
      {
        path: '/course/:id/content/:content_uuid/:index',
        name: 'User Course Content',
        component: () => import('../views/user/CourseContentFix.vue'),
        meta: {
          middleware: [middleware],
          parent: 'User My Courses',
          collapse: false,
        }
      },
      {
        path: '/my-list',
        name: 'User List',
        component: () => import('../views/user/AddToList/UserList.vue'),
        meta: {
          middleware: [middleware],
          parent: 'User List',
          collapse: false,
        },
      },
      {
        path: '/my-list',
        name: 'User My List',
        component: () => import('../views/user/AddToList/UserList.vue'),
        meta: {
          middleware: [middleware],
          parent: 'User My List',
          collapse: false,
        },
      },
      {
        path: '/my-courses',
        name: 'User My Courses',
        component: () => import('../views/user/MyCourses.vue'),
        meta: {
          middleware: [middleware],
          parent: 'User My Courses',
          collapse: false,
        },
      },
      {
        path: '/course/:uuid/learn_more',
        name: 'User Other Course Details',
        component: () => import('../views/user/AddToList/OtherCourseDetails.vue'),
        meta: {
          middleware: [middleware],
          parent: 'User Other Course Details',
          collapse: false,
        },
      },
      // {
      //   path: '/course/:course_uuid/learning-material/:type/:content_uuid',
      //   name: 'User Learning Material',
      //   component: () => import('../views/user/Material.vue'),
      //   meta: {
      //     middleware: [middleware],
      //     parent: 'User Learning Material',
      //     collapse: false,
      //     learning: true
      //   }
      // },
      // {
      //   path: '/assessment/:uuid',
      //   name: 'User Assessment',
      //   component: () => import('../views/user/Assessment.vue'),
      //   meta: {
      //     middleware: [middleware],
      //     parent: 'User Course Details',
      //     collapse: false
      //   }
      // },
      {
        path: '/forum',
        name: 'User Forum',
        component: () => import('../views/common/Forum.vue'),
        meta: {
          parent: 'User Forum',
          collapse: false,
          middleware: [middleware],
        }
      },
      {
        path: '/inbox',
        name: 'User Inbox',
        component: () => import('../views/user/Inbox.vue'),
        meta: {
          parent: 'User Inbox',
          collapse: false,
          middleware: [middleware],
        }
      },
      {
        path: '/transaction-history',
        name: 'User Transaction History',
        component: () => import('../views/user/TransactionHistory.vue'),
        meta: {
          parent: 'User Transaction History',
          collapse: false,
          middleware: [middleware],
        }
      },
      {
        path: '/user-profile',
        name: 'User Profile',
        component: () => import('../views/user/AccountProfile.vue'),
        meta: {
          parent: 'User Profile',
          collapse: false,
          middleware: [middleware],
        }
      },
      {
        path: '/account-settings',
        name: 'User Settings',
        component: () => import('../views/user/AccountSettings.vue'),
        redirect: {name: 'User Account Profile'},
        meta: {
          parent: 'User Settings',
          collapse: false,
          middleware: [middleware],
        },
        children: [
          {
            path: '/account-settings/profile',
            name: 'User Account Profile',
            component: () => import('../views/user/Profile.vue'),
            meta: {
              parent: 'User Settings',
              collapse: false,
              middleware: [middleware],
            },
          },
          {
            path: '/account-settings/notification',
            name: 'User Notification Settings',
            component: () => import('../views/user/NotificationSettings.vue'),
            meta: {
              parent: 'User Settings',
              collapse: false,
              middleware: [middleware],
            },
          },
          {
            path: '/account-settings/change-password',
            name: 'User Change Password',
            component: () => import('../views/user/ChangePassword.vue'),
            meta: {
              parent: 'User Settings',
              collapse: false,
              middleware: [middleware],
            },
          }
        ]
      },
      {
        path: '/help',
        name: 'User Help Desk',
        component: () => import('../views/user/Tickets.vue'),
        meta: {
          middleware: [middleware],
          parent: 'User Help Desk',
          collapse: false
        }
      },
      {
        path: '/scan-certificate',
        name: 'User Scan Certificate',
        component: () => import('../views/Qr.vue'),
        meta: {
          parent: 'User Scan Certificate',
          collapse: false
        }
      },
      // {
      //   path: '/assessments',
      //   name: 'User Assessments',
      //   component: () => import('../views/user/Assessments.vue'),
      //   meta: {
      //     parent: 'User Assessments',
      //     collapse: false
      //   }
      // },
    ]
  },
  {
    path: '/unverified',
    name: 'Unverified',
    component: () => import('../views/Unverified.vue'),
    meta: {
      parent: 'User Unverified',
      collapse: false
    }
  },
]