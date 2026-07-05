import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../components/LoginView.vue'
import RegisterView from '../components/RegisterView.vue'
import ForgotPasswordView from '../components/ForgotPasswordView.vue'
import HomeView from '../views/Home/HomeView.vue'
import { globalLoading } from '@/composables/useLoading'; // Importa el estado

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
      path: '/general-home',
      name: 'General-home',
      component: () => import('../views/Operations/GeneralHomeView.vue'),
      meta: {useLoader: true}
    },

    {
      path: '/checkorderstatus',
      name: 'checkorderstatus',
      component: () => import('../views/Operations/CheckOrderStatus.vue'),
      meta: {useLoader: true}
    },
  
    {
      path: '/general-home/orders',
      name: 'general-home-orders',
      component: () => import('../views/Operations/Orders.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/general-home/generate-quote',
      name: 'general-home-generate-quote',
      component: () => import('../views/Operations/GenerateQuoteView.vue'),
      meta: {useLoader: true}
    },

    {
      path: '/general-home/inventory',
      name: 'general-home-inventory',
      component: () => import('../views/Operations/InventoryView.vue'),
      meta: {useLoader: true}
    },

    {
      path: '/general-home/admin/cash-flow',
      name: 'general-home-admin-cash-flow',
      component: () => import('../views/Admin/CashFlowView.vue'),
      meta: {useLoader: true}
    },

    {
      path: '/general-home/admin/product',
      name: 'general-home-admin-product',
      component: () => import('../views/Admin/ProductView.vue'),
      meta: {useLoader: true}
    },

    {
      path: '/general-home/admin/worker',
      name: 'general-home-admin-worker',
      component: () => import('../views/Admin/WorkerView.vue'),
      meta: {useLoader: true}
    },

    {
      path: '/cotizacion',
      name: 'quotation',
      component: () => import('../views/Checkout/QuotationView.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/cotizacion-exitosa',
      name: 'CotizacionExitosa',
      component: () => import('@/views/Checkout/SuccesfulQuotationView.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/mis-cotizaciones',
      name: 'my-quotations',
      component: () => import('@/views/Distributor/MyQuotationsView.vue'),
      meta: {useLoader: true}
    },
    {
    path: '/cotizacion/:id', 
    name: 'quotation-detail',
    component: () => import('@/views/Distributor/QuotationDetailView.vue'),
    meta: {useLoader: true}
    },
    {
      path: '/mis-pedidos',
      name: 'my-orders',
      component: () => import('@/views/Distributor/MyOrdersView.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/pedido/:id', 
      name: 'order-detail',
      component: () => import('@/views/Distributor/OrderDetailView.vue'),
      meta: {useLoader: true}
    }
  ],
});

// router/index.ts
router.beforeEach((to, from, next) => {
  // Solo activamos si la ruta lo requiere
  if (to.meta.useLoader) {
    globalLoading.value = true;
  }
  
  // Usamos un pequeño retraso para asegurar que Vue procese el estado "true"
  // antes de renderizar la nueva ruta
  setTimeout(() => {
    next();
  }, 50); 
});

router.afterEach((to) => {
  // Si la ruta no usa loader, aseguramos que esté apagado
  if (!to.meta.useLoader) {
    globalLoading.value = false;
  } else {
    // Si la ruta sí usa loader, apagamos después de un tiempo
    setTimeout(() => {
      globalLoading.value = false;
    }, 600);
  }
});


export default router
