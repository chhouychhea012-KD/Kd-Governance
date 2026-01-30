import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../pages/Dashboard.vue';
import Teams from '../pages/Teams.vue';
import Users from '../pages/Users.vue';
import Roles from '../pages/Roles.vue';
import Permissions from '../pages/Permissions.vue';
import RolePermissions from '../pages/RolePermissions.vue';
import Settings from '../pages/Settings.vue';
import Profile from '../pages/Profile.vue';
import UpdateProfile from '../pages/UpdateProfile.vue';
import ChangePassword from '../pages/ChangePassword.vue';
import Login from '../pages/Login.vue';
import ForgotPassword from '../pages/ForgotPassword.vue';
import ResetPassword from '../pages/ResetPassword.vue';

const routes = [
  { path: '/login', name: 'Login', component: Login },
  { path: '/forgot-password', name: 'ForgotPassword', component: ForgotPassword },
  { path: '/reset-password', name: 'ResetPassword', component: ResetPassword },
  { path: '/', name: 'Dashboard', component: Dashboard },
  { path: '/teams', name: 'Teams', component: Teams },
  { path: '/users', name: 'Users', component: Users },
  { path: '/roles', name: 'Roles', component: Roles },
  { path: '/permissions', name: 'Permissions', component: Permissions },
  { path: '/role-permissions', name: 'RolePermissions', component: RolePermissions },
  { path: '/settings', name: 'Settings', component: Settings },
  { path: '/profile', name: 'Profile', component: Profile },
  { path: '/profile/update', name: 'UpdateProfile', component: UpdateProfile },
  { path: '/profile/change-password', name: 'ChangePassword', component: ChangePassword },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
