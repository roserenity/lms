import { type_of_learning } from "../typeoflearning"

const routes = [
  {
    subheader: '',
    lists: [
      {
        title: 'Dashboard',
        icon: 'mdi-view-dashboard',
        route: 'Instructor Dashboard',
        query: { search: '', page: 1, paginate: 10 },
      },
      {
        title: 'My Courses',
        icon: 'mdi-book-open-outline',
        route: 'Instructor My Courses'
      },
      {
        title: 'Assessments',
        icon: 'mdi-note-edit-outline',
        route: 'Instructor Assessment',
        query: { search: '', page: 1, paginate: 10, tab: 0},
      },
      {
        title: 'Question Pools',
        icon: 'mdi-inbox-full-outline',
        route: 'Instructor Question Pools',
        query: { search: '', page: 1, paginate: 10, type_of_question_pool: '' },
      },
      {
        title: 'Certificate Templates',
        icon: 'mdi-file-sign',
        route: 'Instructor Certificate Templates',
        query: { search: '', page: 1, paginate: 10 },
      },
      {
        title: 'Rubrics',
        icon: 'mdi-text-box-outline',
        route: 'Instructor Rubric',
        query: { search: '', page: 1, paginate: 10, tab: 0 },
      },
    ]
  },
  {
    subheader: 'USER MANAGEMENT',
    lists: [
      {
        title: 'Enrollments',
        icon: 'mdi-account-clock-outline',
        route: 'Instructor Enrollment',
        query: { search: '', page: 1, paginate: 10, status: '', type_of_learning: '', date_from: ``, date_to: `` },
      },
      {
        title: 'Course Groups',
        icon: 'mdi-account-group-outline',
        route: 'Instructor Course Groups',
        query: { search: '', page: 1, paginate: 10, status: '', },
      },
      {
        title: 'Grading',
        icon: 'mdi-notebook-outline',
        route: 'Instructor Grading',
        query: { search: '', page: 1, paginate: 10, status: '', created_from: ``, created_to: ``, type_of_learning: '' },
      },
      {
        title: 'Student Outputs',
        icon: 'mdi-book-open-blank-variant',
        route: 'Instructor Student Outputs',
        query: { search: '', page: 1, paginate: 10, classes: '', status: '', created_from: ``, created_to: ``, type_of_learning: '' },
      },
      {
        title: 'Reporting',
        icon: 'mdi-book-open-page-variant-outline',
        route: 'Instructor Reporting'
      },
      {
        title: 'Activity Logs',
        icon: 'mdi-timeline-text',
        route: 'Instructor Activity Logs',
        query: { search: '', page: 1, paginate: 10, log_name: ['user_enrollment', 'user_deletion', 'user_course_activity', 'user_certificate_gen', 'user_course_complete', 'advisory_update', 'course_checkout', 'user_transaction'], timestamp: Date.now() },
      },
      {
        title: 'Exports',
        icon: 'mdi-export-variant',
        route: 'Instructor Exports',
        query: { search: '', page: 1, paginate: 10, type: ['course_evaluation', 'module_evaluation', 'invoice', 'assessment', 'course'], timestamp: Date.now() },
      },
    ]
  },
  {
    subheader: 'COMMUNICATION',
    lists: [
      {
        title: 'Announcements',
        icon: 'mdi-forum-outline',
        route: 'Instructor Announcements',
        query: { search: '', page: 1, paginate: 10, course_id: '', posted_from: `${new Date().getFullYear()}-1`, posted_to: `${new Date().getFullYear()}-12`},
      },
      {
        title: 'Forum',
        icon: 'mdi-comment-account-outline',
        route: 'Instructor Forum',
        query: { search: '', page: 1, paginate: 10 },
      },
      {
        
        title: 'Inbox',
        icon: 'mdi-message-processing-outline',
        route: 'Instructor Inbox'
      },
    ]
  },
  {
    subheader: '',
    lists: [
      {
        title: 'Trash',
        icon: 'mdi-trash-can-outline',
        route: 'Instructor Trash',
        query: { search: '', page: 1, paginate: 10 },
      },
    ]
  },
  // {
  //   subheader: 'COURSE BUILDER',
  //   lists: [
  //     {
  //       title: 'Lesson Manager',
  //       icon: 'mdi-book-open',
  //       route: 'Teacher Lesson Manager'
  //     },
  //     {
  //       title: 'Assessment Manager',
  //       icon: 'mdi-numeric',
  //       route: 'Teacher Assessment Manager'
  //     },
  //     {
  //       title: 'Live Sessions',
  //       icon: 'mdi-video-plus',
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
  //       route: 'Teacher Manage Ticket' 
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
    Vue.prototype.$teacherRoutes = routes
  }
}