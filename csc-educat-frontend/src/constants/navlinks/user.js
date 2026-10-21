const routes = [
  {
    subheader: null,
    lists: [
      {
        title: 'Home',
        icon: 'mdi-view-dashboard-outline',
        route: 'User Dashboard',
        sublinks: []
      },
    ]
  },
  {
    subheader: null,
    lists: [
      {
        title: 'Profile',
        icon: 'mdi-account-outline',
        route: 'User Profile',
        sublinks: []
      },
    ]
  },
  {
    subheader: 'MY ACTIVITIES',
    lists: [
      {
        title: 'My Courses',
        icon: 'mdi-book-open-outline',
        route: 'User My Courses',
        sublinks: []
      },
      {
        title: 'My List',
        icon: 'mdi-text-box-outline',
        route: 'User My List',
        sublinks: []
      },
      {
        title: 'My Calendar',
        icon: 'mdi-calendar-outline',
        route: 'User Calendar',
        sublinks: []
      },
    ]
  },
  {
    subheader: 'COMMUNICATION & HISTORY',
    lists: [
      {
        title: 'Inbox',
        icon: 'mdi-message-outline',
        route: 'User Inbox',
        sublinks: []
      },
      {
        title: 'Forum',
        icon: 'mdi-comment-account-outline',
        route: 'User Forum',
        sublinks: []
      },
      {
        title: 'Transaction History',
        icon: 'mdi-update',
        route: 'User Transaction History',
        sublinks: []
      },
    ]
  },
]

export default {
  install(Vue) {
    Vue.prototype.$userRoutes = routes
  }
}