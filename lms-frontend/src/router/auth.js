import { auth_middleware } from './auth-middleware'

export const AuthRoutes = [
  // {
  //   path: '/login',
  //   name: 'Login',
  //   component: () => import('../views/Login.vue'),
  //   meta: {
  //     middleware: [auth_middleware]
  //   }
  // },
  // {
  //   path: '/signup',
  //   name: 'Signup',
  //   component: () => import('../views/Signup.vue'),
  //   meta: {
  //     middleware: [auth_middleware]
  //   }
  // },
  {
    path: '/forgot-password',
    name: 'Forgot Password',
    component: () => import('../views/ForgotPassword.vue'),
    meta: {
      middleware: [auth_middleware]
    }
  },
  {
    path: '/reset-password/:token',
    name: 'Reset Password',
    component: () => import('../views/ResetPassword.vue'),
    meta: {
      middleware: [auth_middleware]
    }
  },
  {
    path: '/verify-email',
    name: 'Email Verified',
    component: () => import('../views/EmailVerified.vue'),
  },
  {
    path: '/validate-certificate/:certificate_uuid',
    name: 'Validate Certificate',
    component: () => import('../views/ValidateCertificate.vue')
  }
]