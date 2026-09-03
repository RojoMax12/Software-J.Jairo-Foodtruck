import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../components/LoginView.vue'
import RegisterView from '../components/RegisterView.vue'
import ForgotPasswordView from '../components/ForgotPasswordView.vue'
import ResetPasswordView from '../components/ResetPasswordView.vue'
import HomeView from '../views/Home/HomeView.vue'
import { globalLoading } from '@/composables/useLoading';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { hideNavbar: false }
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { hideNavbar: true, useLoader: true }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { hideNavbar: true, useLoader: true }
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: ForgotPasswordView,
      meta: { hideNavbar: true, useLoader: true }
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: ResetPasswordView,
      meta: { hideNavbar: true, useLoader: true }
    },
    {
      path: '/checkorderstatus',
      name: 'checkorderstatus',
      component: () => import('../views/Operations/CheckOrderStatus.vue'),
      meta: { useLoader: true }
    },
    {
      path: '/cotizacion',
      name: 'quotation',
      component: () => import('../views/Checkout/QuotationView.vue'),
      meta: { useLoader: true }
    },
    {
      path: '/cotizacion-exitosa',
      name: 'CotizacionExitosa',
      component: () => import('@/views/Checkout/SuccesfulQuotationView.vue'),
      meta: { useLoader: true }
    },
    {
      path: '/menu-board',
      name: 'menu-board',
      component: () => import('@/views/Operations/MenuBoardView.vue'),
      meta: { hideNavbar: true, useLoader: false }
    },
    {
      path: '/tv-menu',
      redirect: '/menu-board'
    },

    // ==========================================
    // RUTAS DE CLIENTES / DISTRIBUIDOR (Rol 2, 1)
    // ==========================================
    {
      path: '/mis-cotizaciones',
      name: 'my-quotations',
      component: () => import('@/views/Distributor/MyQuotationsView.vue'),
      meta: { useLoader: true, requiresAuth: true }
    },
    {
      path: '/cotizacion/:id',
      name: 'quotation-detail',
      component: () => import('@/views/Distributor/QuotationDetailView.vue'),
      meta: { useLoader: true, requiresAuth: true }
    },
    {
      path: '/mis-pedidos',
      name: 'my-orders',
      component: () => import('@/views/Distributor/MyOrdersView.vue'),
      meta: { useLoader: true, requiresAuth: true }
    },
    {
      path: '/pedido/:id',
      name: 'order-detail',
      component: () => import('@/views/Distributor/OrderDetailView.vue'),
      meta: { useLoader: true, requiresAuth: true }
    },
    {
      path: '/mi-perfil',
      name: 'my-profile',
      component: () => import('@/views/Distributor/ProfileView.vue'),
      meta: { useLoader: true, requiresAuth: true }
    },
    {
      path: '/perfil',
      name: 'profile',
      redirect: '/mi-perfil'
    },

    // ==========================================
    // RUTAS OPERATIVAS & COCINA (Rol 1, 3)
    // ==========================================
    {
      path: '/general-home',
      name: 'General-home',
      component: () => import('../views/Operations/GeneralHomeView.vue'),
      meta: { useLoader: true, roles: [1, 3] }
    },
    {
      path: '/general-home/orders',
      name: 'general-home-orders',
      component: () => import('../views/Operations/Orders.vue'),
      meta: { useLoader: true, roles: [1, 3] }
    },
    {
      path: '/general-home/generate-quote',
      name: 'general-home-generate-quote',
      component: () => import('../views/Operations/GenerateQuoteView.vue'),
      meta: { useLoader: true, roles: [1, 3] }
    },
    {
      path: '/general-home/inventory',
      name: 'general-home-inventory',
      component: () => import('../views/Operations/InventoryView.vue'),
      meta: { useLoader: true, roles: [1, 3] }
    },
    {
      path: '/general-home/admin/product',
      name: 'general-home-admin-product',
      component: () => import('../views/Admin/ProductView.vue'),
      meta: { useLoader: true, roles: [1] }
    },
    {
      path: '/general-home/admin/categories',
      name: 'general-home-admin-categories',
      component: () => import('../views/Admin/CategoryView.vue'),
      meta: { useLoader: true, roles: [1] }
    },
    {
      path: '/general-home/admin/sizes',
      name: 'general-home-admin-sizes',
      component: () => import('../views/Admin/SizeView.vue'),
      meta: { useLoader: true, roles: [1] }
    },
    {
      path: '/general-home/admin/history',
      name: 'general-home-admin-history',
      component: () => import('../views/Admin/CatalogHistoryView.vue'),
      meta: { useLoader: true, roles: [1] }
    },
    {
      path: '/general-home/admin/banners',
      name: 'general-home-admin-banners',
      component: () => import('../views/Admin/BannersAndAnnouncementsView.vue'),
      meta: { useLoader: true, roles: [1] }
    },

    // ==========================================
    // RUTAS EXCLUSIVAS DE ADMINISTRACIÓN (Rol 1)
    // ==========================================
    {
      path: '/general-home/admin/cash-flow',
      name: 'general-home-admin-cash-flow',
      component: () => import('../views/Admin/CashFlowView.vue'),
      meta: { useLoader: true, roles: [1,3] }
    },
    {
      path: '/general-home/admin/worker',
      name: 'general-home-admin-worker',
      component: () => import('../views/Admin/WorkerView.vue'),
      meta: { useLoader: true, roles: [1] }
    },
    {
      path: '/general-home/admin/schedules',
      name: 'general-home-admin-schedules',
      component: () => import('../views/Admin/ScheduleView.vue'),
      meta: { useLoader: true, roles: [1] }
    },

    // ==========================================
    // 404 NOT FOUND
    // ==========================================
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/Error/NotFoundView.vue'),
      meta: { hideNavbar: true, useLoader: true }
    }
  ],
});

// Guardián de Navegación y Roles
router.beforeEach((to, from, next) => {
  if (to.meta.useLoader) {
    globalLoading.value = true;
  }

  const token = localStorage.getItem('token');
  let user: any = null;
  try {
    const rawUser = localStorage.getItem('user');
    if (rawUser) user = JSON.parse(rawUser);
  } catch {
    user = null;
  }

  const roleId = user ? Number(user.id_rol) : null;

  // 1. Si ya está autenticado e intenta ir a login o registro
  if (token && (to.path === '/login' || to.path === '/register')) {
    if (roleId === 1 || roleId === 3) {
      return next('/general-home');
    } else {
      return next('/mis-pedidos');
    }
  }

  // 2. Si la ruta requiere autenticación genérica
  if (to.meta.requiresAuth && !token) {
    return next({ path: '/login', query: { redirect: to.fullPath } });
  }

  // 3. Si la ruta requiere roles específicos (ej: admin 1, cocina 3)
  if (to.meta.roles && Array.isArray(to.meta.roles)) {
    if (!token) {
      return next({ path: '/login', query: { redirect: to.fullPath } });
    }
    if (!roleId || !to.meta.roles.includes(roleId)) {
      if (roleId === 2) {
        return next('/mis-pedidos');
      } else if (roleId === 3) {
        return next('/general-home');
      } else {
        return next('/');
      }
    }
  }

  next();
});

router.afterEach((to) => {
  if (!to.meta.useLoader) {
    globalLoading.value = false;
  } else {
    setTimeout(() => {
      globalLoading.value = false;
    }, 400);
  }
});

export default router;
