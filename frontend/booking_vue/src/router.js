import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth';

import UserLogin from './views/UserLogin.vue';
import UserDashboard from './views/UserDashboard.vue';
import AdminDashboard from './views/AdminDashboard.vue';
import BusinessDashboard from './views/BusinessDashboard.vue';
import AdminBusiness from './components/AdminBusiness.vue';
import BusinessService from './components/BusinessService.vue';
import AdminUser from './components/AdminUser.vue';
import UserReview from './components/UserReview.vue';
import UserBooking from './components/UserBooking.vue';
import ForgotPassword from './views/ForgotPassword.vue';
import ResetPassword from './views/ResetPassword.vue';


const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'Login', component: UserLogin },
  { path: '/user/dashboard', name: 'UserDashboard', component: UserDashboard },
  { path: '/admin/dashboard', name: 'AdminDashboard', component: AdminDashboard },
  { path: '/business/dashboard', name: 'BusinessDashboard', component: BusinessDashboard },
  { path: '/admin/businesses', name: 'AdminBusiness', component: AdminBusiness },
  { path: '/admin/users', name: 'AdminUser', component: AdminUser },
  { path: '/business/service', name: 'BusinessService', component: BusinessService },
  { path: '/user/review', name: 'UserReview', component: UserReview },
  { path: '/user/booking', name: 'UserBooking', component: UserBooking },
    { path: '/forgot-password', name: 'ForgotPassword', component: ForgotPassword },
  { path: '/reset-password', name: 'ResetPassword', component: ResetPassword },

];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Router guard using Pinia store for auth check
router.beforeEach((to, from, next) => {
  const auth = useAuthStore();

  if (to.meta.requiresAdmin && (!auth.user || auth.user.role !== 'admin')) {
    alert('Only admin access');
    return next('/login');
  }

  next();
});

export default router;
