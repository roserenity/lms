const routes = [
  {
    subheader: 'Organization',
    lists: [
      {
        title: 'General Education',
        value: 'general_education',
        icon: 'mdi-view-dashboard',
        route: 'Super Admin Dashboard'
      },
      {
        title: 'Corporate',
        value: 'corporate',
        icon: 'mdi-account-multiple',
        route: 'Super Admin Tenants'
      },
      {
        title: 'NGO',
        value: 'ngo',
        icon: 'mdi-account-multiple',
        route: 'Super Admin Tenants'
      },
    ]
  },
  {
    subheader: 'Domain Status',
    lists: [
      {
        title: 'Active',
        value: 'active',
        icon: 'mdi-view-dashboard',
        route: 'Super Admin Dashboard'
      },
      {
        title: 'Inactive',
        value: 'inactive',
        icon: 'mdi-account-multiple',
        route: 'Super Admin Tenants'
      },
    ]
  },
  {
    subheader: 'Date of Registry',
    lists: [
      {title: 'Today', value: 'today'},
      {title: 'This week', value: 'this_week'},
      {title: 'This month', value: 'this_month'},
      {title: 'This year', value: 'this_year'},
    ]
  },

]

export default {
  install(Vue) {
    Vue.prototype.$superRoutes = routes
  }
}