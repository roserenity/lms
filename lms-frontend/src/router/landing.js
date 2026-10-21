import { auth_middleware } from './auth-middleware'

export const LandingRoutes = [
  {
    path: '/',
    name: 'Homepage',
    component: () => import('../layouts/landing/Main.vue'),
    redirect: { name: 'Home' },
    children: [
      {
        path: '/',
        name: 'Home',
        parent: 'Homepage',
        component: () => import('../views/landing/Home.vue'),
        meta: {
          parent: 'Homepage',
          collapse: false,
        },
        meta: {
          middleware: [auth_middleware]
        },
      },
      {
        path: '/learn_more/:uuid',
        name: 'Course Learn More',
        component: () => import('../views/landing/CourseDetails.vue'),
        meta: {
          parent: 'Course Learn More',
          collapse: false,
        },
      },
      {
        path: '/list',
        name: 'User Cart',
        component: () => import('../views/landing/UserCart.vue'),
        meta: {
          parent: 'User Cart',
          collapse: false,
        },
      },
      {
        path: '/the-institute',
        name: 'The Institute',
        component: () => import('../views/landing/AboutUs/TheInstitute.vue'),
        meta: {
          parent: 'The Institute',
          collapse: false,
        },
      },
      {
        path: '/the-vision-is-the-challenge',
        name: 'The Vision is the Challenge',
        component: () => import('../views/landing/AboutUs/TheVision.vue'),
        meta: {
          parent: 'The Vision is the Challenge',
          collapse: false,
        },
      },
      {
        path: '/our-solutions',
        name: 'Our Solutions',
        component: () => import('../views/landing/AboutUs/OurSolutions.vue'),
        meta: {
          parent: 'Our Solutions',
          collapse: false,
        },
      },
      {
        path: '/our-partners',
        name: 'Our Partners',
        component: () => import('../views/landing/AboutUs/OurPartners.vue'),
        meta: {
          parent: 'Our Partners',
          collapse: false,
        },
      },
      {
        path: '/our-promise',
        name: 'Our Promise',
        component: () => import('../views/landing/AboutUs/OurPromise.vue'),
        meta: {
          parent: 'Our Promise',
          collapse: false,
        },
      },
      {
        path: '/how-it-works',
        name: 'How It Works',
        component: () => import('../views/landing/HowItWorks.vue'),
        meta: {
          parent: 'How It Works',
          collapse: false,
        },
      },
      {
        path: '/free-trainings',
        name: 'Free Training',
        component: () => import('../views/landing/FreeTraining.vue'),
        meta: {
          parent: 'Free Training',
          collapse: false,
        },
      },
      {
        path: '/browse-courses',
        name: 'Browse Courses',
        component: () => import('../views/landing/BrowseCourses.vue'),
        meta: {
          parent: 'Browse Courses',
          collapse: false,
        },
      },
      {
        path: '/contact-us',
        name: 'Contact Us',
        component: () => import('../views/landing/ContactUs.vue'),
        meta: {
          parent: 'Contact Us',
          collapse: false,
        },
      },
      {
        path: '/frequently-asked-questions',
        name: 'FAQ',
        component: () => import('../views/landing/FAQ.vue'),
        meta: {
          parent: 'FAQ',
          collapse: false,
        },
      },
    ]
  },
]