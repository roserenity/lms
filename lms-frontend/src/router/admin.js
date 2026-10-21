import { middleware } from './middleware'

const routes = [
  { name: 'Admin Dashboard', query: { paginate: 10 } },
  { name: 'Admin User Management', query: { search: '', page: 1, paginate: 10, role: '', status: '', created_from: '', created_to: '', timestamp: Date.now() } }
];

function randomRedirect(routes) {
  const randomRoute = routes[Math.floor(Math.random() * routes.length)];
  return randomRoute;
}

export const AdminRoutes = [
  {
    path: '/admin',
    component: () => import('../layouts/Main.vue'),
    name: 'Admin Layout',
    redirect: randomRedirect(routes),
    children: [
      {
        path: '/admin/dashboard',
        name: 'Admin Dashboard',
        component: () => import('../views/admin/Dashboard.vue'),
        meta: {
          childname: 'Admin Dashboard',
          collapse: false,
          title_append: '',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/user-management',
        name: 'Admin User Management',
        component: () => import('../views/admin/UserManagement.vue'),
        meta: {
          childname: 'Admin User Management',
          collapse: false,
          title_append: '',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/announcement',
        name: 'Admin Announcement',
        component: () => import('../views/admin/Announcements.vue'),
        meta: {
          childname: 'Admin Enrollment',
          collapse: false,
          title_append: '',
          middleware: [middleware]
        },
      },
      {
        path: '/admin/enrollment',
        name: 'Admin Course Enrollment',
        component: () => import('../views/admin/enrollment/CourseEnrollment.vue'),
        meta: {
          childname: 'Admin Course Enrollment',
          collapse: false,
          title_append: '',
          middleware: [middleware]
        },
      },
      {
        path: '/admin/enrollments/:course_uuid',
        name: 'Admin User Enrollment',
        component: () => import('../views/admin/enrollment/CourseStudentsEnrollment.vue'),
        meta: {
          parent: 'Admin Course Enrollment',
          collapse: false,
          title_append: '',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/class-category',
        name: 'Admin Class Category',
        component: () => import('../views/admin/ClassCategory.vue'),
        meta: {
          childname: 'Admin Class Category',
          collapse: false,
          title_append: 'Customization',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/manage-terms',
        name: 'Admin Manage Terms',
        component: () => import('../views/admin/ManageTerms.vue'),
        meta: {
          childname: 'Admin Manage Terms',
          collapse: false,
          title_append: 'Customization',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/forum',
        name: 'Admin Forum',
        component: () => import('../views/common/Forum.vue'),
        meta: {
          childname: 'Admin Forum',
          collapse: false,
          title_append: '',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/forum/restrictions',
        name: 'Admin Forum Restrictions',
        component: () => import('../views/admin/ForumRestrictions.vue'),
        meta: {
          childname: 'Admin Forum',
          collapse: false,
          title_append: '',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/inbox',
        name: 'Admin Inbox',
        component: () => import('../views/admin/Inbox.vue'),
        meta: {
          childname: 'Admin Inbox',
          collapse: false,
          title_append: '',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/ticket',
        name: 'Admin Ticket',
        component: () => import('../views/admin/Ticket.vue'),
        meta: {
          childname: 'Admin Ticket',
          collapse: false,
          title_append: '',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/module-settings',
        name: 'Admin Module Settings',
        component: () => import('../views/admin/ModuleSettings.vue'),
        meta: {
          childname: 'Admin Page Asset',
          collapse: false,
          title_append: 'Customization',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/page-assets',
        name: 'Admin Page Assets',
        component: () => import('../views/admin/PageAssets.vue'),
        meta: {
          childname: 'Admin Page Asset',
          collapse: false,
          title_append: 'Customization',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/training-credits',
        name: 'Admin Training Credits',
        component: () => import('../views/admin/TrainingCredits.vue'),
        meta: {
          childname: 'Admin Training Credits',
          collapse: false,
          title_append: 'Customization',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/certificate-management',
        name: 'Admin Certificate Management',
        component: () => import('../views/admin/CertificateManagement.vue'),
        meta: {
          childname: 'Admin Certificate Management',
          collapse: false,
          title_append: 'Customization',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/registration',
        name: 'Admin Registration',
        component: () => import('../views/admin/Registration.vue'),
        meta: {
          childname: 'Admin Registration',
          collapse: false,
          title_append: 'Customization',
          middleware: [middleware]
        }
      },
      {
        path: '/admin/activity-logs',
        name: 'Admin Activity Logs',
        component: () => import('../views/admin/ActivityLogs.vue'),
        meta: {
          childname: 'Admin Activity Logs',
          collapse: false,
          middleware: [middleware]
        }
      },
      {
        path: '/admin/import-logs',
        name: 'Admin Import Logs',
        component: () => import('../views/admin/ImportLogs.vue'),
        meta: {
          childname: 'Admin Import Logs',
          collapse: false,
          middleware: [middleware]
        }
      },
      {
        path: '/admin/export',
        name: 'Admin Exports',
        component: () => import('../views/admin/Exports.vue'),
        meta: {
          childname: 'Admin Exports',
          collapse: false,
          middleware: [middleware]
        }
      },
      {
        path: '/admin/account-settings',
        name: 'Admin Settings',
        component: () => import('../views/admin/AccountSettings.vue'),
        redirect: {name: 'Admin Account Profile'},
        meta: {
          childname: 'Admin Settings',
          collapse: false,
          title_append: '',
          middleware: [middleware],
        },
        children: [
          {
            path: 'profile',
            name: 'Admin Account Profile',
            component: () => import('../views/user/Profile.vue'),
            meta: {
              parent: 'Admin Settings',
              collapse: false,
              middleware: [middleware],
            },
          },
          {
            path: 'change-password',
            name: 'Admin Change Password',
            component: () => import('../views/user/ChangePassword.vue'),
            meta: {
              parent: 'Admin Settings',
              collapse: false,
              middleware: [middleware],
            },
          }
        ]
      },
      {
        path: '/admin/course/:uuid/learn_more',
        name: 'Admin Course Details',
        component: () => import('../views/admin/CoursePage.vue'),
        meta: {
          middleware: [middleware],
          parent: 'Admin Course Details',
          collapse: false,
        },
      },
    ]
  }
]
