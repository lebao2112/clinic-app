import { createRouter, createWebHistory } from 'vue-router';
import Home from '../pages/Home.vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import Dashboard from '../pages/Dashboard.vue';
import Patients from '../pages/Patients.vue';
import Appointments from '../pages/Appointments.vue';
import Examinations from '../pages/Examinations.vue'; 
import Specialties from '../pages/Specialties.vue'; 
import Users from '../pages/Users.vue';
import Doctors from '../pages/Doctors.vue';
import Invoices from '../pages/Invoices.vue';
import Login from '../pages/Login.vue';
import Medicines from '../pages/Medicines.vue';
import Prescriptions from '../pages/Prescriptions.vue';

const routes = [
    {
        path: '/',
        name: 'Home',
        component: Home,
        meta: { requiresAuth: false }
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { requiresAuth: false }
    },
    {
        path: '/admin-system',
        component: AdminLayout,
        meta: { requiresAuth: true },
        children: [
            { path: '/dashboard', name: 'Dashboard', component: Dashboard },
            { path: '/patients', name: 'Patients', component: Patients },
            { path: '/appointments', name: 'Appointments', component: Appointments },
            { path: '/examinations', name: 'Examinations', component: Examinations }, 
            { path: '/specialties', name: 'Specialties', component: Specialties }, 
            { path: '/users', name: 'Users', component: Users },
            { path: '/doctors', name: 'Doctors', component: Doctors },
            { path: '/invoices', name: 'Invoices', component: Invoices },
            { path: '/medicines', name: 'Medicines', component: Medicines },
            { path: '/prescriptions', name: 'Prescriptions', component: Prescriptions },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');
    
    if (to.meta.requiresAuth && !token) {
        next({ name: 'Login' });
    } else if (to.name === 'Login' && token) {
        next({ name: 'Patients' });
    } else {
        next();
    }
});

export default router;