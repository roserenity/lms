import { middleware } from './middleware'
import { landing } from './landing-middleware'

export const SuperRoutes = [
  {
    path: '/super-admin/login',
    name: 'Super Admin Login',
    component: () => import('../views/super/Login.vue'),
  },
  {
    path: '/super-admin',
    component: () => import('../layouts/Main.vue'),
    redirect: { name: 'Super Admin Tenants' },
    meta: { middleware: [middleware] },
    children: [
      {
        path: '/super-admin/dashboard',
        name: 'Super Admin Dashboard',
        component: () => import('../views/super/Dashboard.vue'),
        meta: {
          parent: 'Super Admin Dashboard',
          collapse: false,
          middleware: [middleware]
        }
      },
      {
        path: '/super-admin/tenants',
        name: 'Super Admin Tenants',
        component: () => import('../views/super/Tenants.vue'),
        meta: {
          parent: 'Super Admin Tenants',
          collapse: false,
          middleware: [middleware]
        }
      },
      {
        path: '/super-admin/tenant/:id',
        name: 'Super Admin Manage Tenant',
        component: () => import('../views/super/ManageTenant.vue'),
        meta: {
          parent: 'Super Admin Tenants',
          collapse: true
        }
      },
    ]
  }
]