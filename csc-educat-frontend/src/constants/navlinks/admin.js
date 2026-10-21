
const routes = [
  {
    lists: [
      {
        title: 'Dashboard',
        icon: 'mdi-view-dashboard',
        route: 'Admin Dashboard'
      },
      {
        title: 'User Management',
        icon: 'mdi-account-box-multiple',
        route: 'Admin User Management',
        query: { search: '', page: 1, paginate: 10, role: '', status: '', created_from: '', created_to: '', timestamp: Date.now() },
      },
      {
        title: 'Enrollment',
        icon: 'mdi-notebook-multiple',
        route: 'Admin Course Enrollment',
        query: { search: '', page: 1, paginate: 10, type_of_learning: '', status: '', created_from: '', created_to: '', timestamp: Date.now() },
      },
      {
        title: 'Announcement',
        icon: 'mdi-forum-outline',
        route: 'Admin Announcement',
        query: { search: '', page: 1, paginate: 10, course_id: '', posted_from: `${new Date().getFullYear()}-1`, posted_to: `${new Date().getFullYear()}-12`},
      },
      {
        title: 'Forum',
        icon: 'mdi-comment-account-outline',
        route: 'Admin Forum',
        query: { topic_category: '', page: 1, paginate: 10, category: '',  timestamp: Date.now() },
      },
      {
        title: 'Inbox',
        icon: 'mdi-message-processing-outline',
        route: 'Admin Inbox'
      },
      {
        title: 'Tickets',
        icon: 'mdi-ticket-account',
        route: 'Admin Ticket',
        query: { search: '', page: 1, paginate: 10, category: '', date_from: '', date_to: '', status: '', timestamp: Date.now() },
      },
      {
        title: 'Activity Logs',
        icon: 'mdi-timeline-text',
        route: 'Admin Activity Logs',
        query: { search: '', page: 1, paginate: 10, log_name: ['user_enrollment', 'user_deletion', 'user_course_activity', 'user_certificate_gen', 'user_course_complete', 'advisory_update', 'course_checkout', 'user_transaction'], timestamp: Date.now() },
      },
      // {
      //   title: 'Class Section',
      //   icon: 'mdi-school',
      //   route: 'Admin Class Section'
      // },
      // {
      //   title: 'Enrollees',
      //   icon: 'mdi-account-multiple',
      //   route: 'Admin Enrollees'
      // },
      // {
      //   title: 'Invoices',
      //   icon: 'mdi-currency-usd',
      //   route: 'Admin Invoices'
      // },
    ]
  },
  {
    subheader: 'CUSTOMIZATION',
    lists: [
      {
        title: 'Class Category',
        icon: 'mdi-account-supervisor-circle-outline',
        route: 'Admin Class Category'
      },
      {
        title: 'Registration',
        icon: 'mdi-playlist-edit',
        route: 'Admin Registration'
      },
      {
        title: 'Module Settings',
        icon: 'mdi-clipboard-edit-outline',
        route: 'Admin Module Settings'
      },
      {
        title: 'Page Assets',
        icon: 'mdi-monitor-edit',
        route: 'Admin Page Assets'
      },
      {
        title: 'Training Credits',
        icon: 'mdi-book-education-outline',
        route: 'Admin Training Credits'
      },
      {
        title: 'Certificate Management',
        icon: 'mdi-certificate-outline',
        route: 'Admin Certificate Management'
      },
      {
        title: 'Terms',
        icon: 'mdi-account-box-multiple',
        route: 'Admin Manage Terms'
      }
    ]
  },
  {
    subheader: 'Tracking',
    lists: [
      {
        title: 'Exports',
        icon: 'mdi-export-variant',
        route: 'Admin Exports',
        query: { search: '', page: 1, paginate: 10, log_name: ['user_management', 'course', 'invoice', 'activity_log'], timestamp: Date.now() },
      },
      {
        title: 'Import Logs',
        icon: 'mdi-clipboard-text-outline',
        route: 'Admin Import Logs'
      },
    ]
  },
  // {
  //   subheader: 'COMMUNICATION',
  //   lists: [
  //     {
  //       title: 'Inbox',
  //       icon: 'mdi-mail',
  //       route: 'Teacher Live Session'
  //     },
  //   ]
  // },
  // {
  //   subheader: 'HELP DESK',
  //   lists: [
  //     {
  //       title: 'Ticketing Support',
  //       icon: 'mdi-ticket-account',
  //       route: 'User Tickets' 
  //     },
  //     {
  //       title: 'FAQS',
  //       icon: 'mdi-help-circle',
  //       route: ''
  //     },
  //   ]
  // },
  // {
  //   subheader: 'ACCOUNT AND SECURITY',
  //   lists: [
  //     {
  //       title: 'Account Settings',
  //       icon: 'mdi-account',
  //       route: 'Teacher Settings' 
  //     },
  //   ]
  // },
]

export default {
  install(Vue) {
    Vue.prototype.$adminRoutes = routes
  }
}