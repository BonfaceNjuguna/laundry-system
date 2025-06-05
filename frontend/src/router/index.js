import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '@/pages/auth/LoginPage.vue';

const routes = [
  { path: '/login', component: LoginPage },
  { path: '/dashboard', component: () => import('@/pages/dashboard/DashboardPage.vue') }, // placeholder
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
