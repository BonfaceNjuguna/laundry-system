import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '@/pages/auth/LoginPage.vue';
import Bookings from '@/views/Bookings.vue';
import Customers from '@/views/Customers.vue';
import Services from '@/views/Services.vue';

const routes = [
  { path: '/login', component: LoginPage },
  { path: '/dashboard', component: () => import('@/pages/dashboard/DashboardPage.vue') },
  { path: '/bookings', component: Bookings },
  { path: '/customers', component: Customers },
  { path: '/services', component: Services }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
