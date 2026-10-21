import Vue from 'vue'
import VueRouter from 'vue-router'

const { push } = VueRouter.prototype

const { isNavigationFailure, NavigationFailureType } = VueRouter

// VueRouter.prototype.push = function (location) {
//   return push.call(this, location).catch(error => {
//     if (!isNavigationFailure(error, NavigationFailureType.duplicated)) {
//       throw Error(error)
//     }
//   })
// }


Vue.use(VueRouter)

import middlewarePipeline from './middleware-pipeline'
import { domain } from './domain-middleware'
import { AuthRoutes } from './auth'
import { LandingRoutes } from './landing'
import { TeacherRoutes } from './teacher'
import { UserRoutes } from './user'
import { AdminRoutes } from './admin'
// SuperRoutes intentionally omitted — no central domain / super-admin in standalone mode

let scrollX = 0
let scrollY = 0

const routes = [
  ...LandingRoutes,
  ...AuthRoutes,
  ...TeacherRoutes,
  ...UserRoutes,
  ...AdminRoutes,
  {
    path: '/forbidden',
    component: () => import('../views/Forbidden.vue'),
  },
  {
    path: '*',
    component: () => import('../views/404.vue')
  },
  {
    path: '/404',
    component: () => import('../views/404.vue')
  },
  {
    path: '/403',
    component: () => import('../views/403.vue')
  },
  {
    path: '/419',
    component: () => import('../views/419.vue')
  },
  {
    path: '/error',
    component: () => import('../views/ServerError.vue')
  },
]

// Standalone: super-admin routes are never loaded (no VUE_APP_MAIN_DOMAIN check)

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes,
  scrollBehavior(to, from) {
    if(to.name !== from.name) {
      return { x: 0, y: 0 };
    } else {
      return { 
        x: scrollX,
        y: scrollY
      };
    }
  }
})

window.addEventListener('scroll', () => {
  scrollX = window.scrollX;
  scrollY = window.scrollY;
});

router.beforeEach((to, from, next) => {
  
  window.scrollTo(0, 0);
  
  domain({ next })

  if (!isDifferentRoute(to, from)) {
    return; 
  }

  if (to.meta.middleware) {
    const middleware = Array.isArray(to.meta.middleware)
      ? to.meta.middleware
      : [to.meta.middleware];
    const ctx = { from, next, to };
    const nextMiddleware = middlewarePipeline(ctx, middleware, 1);

    return middleware[0]({ ...ctx, next: nextMiddleware });
  }
  return next();
  
});

// router.afterEach((to) => {
//   if (typeof gtag === 'function') {
//     gtag('event', 'page_view', {
//       page_path: to.fullPath,
//       page_title: to.name
//     });
//   }
// });

function isDifferentRoute(route1, route2) {
  return route1.name !== route2.name || JSON.stringify(route1.query) !== JSON.stringify(route2.query);
}

export default router
