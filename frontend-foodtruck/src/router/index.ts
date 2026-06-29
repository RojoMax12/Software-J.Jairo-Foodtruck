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
      meta: { hideNavbar: true }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { hideNavbar: true }
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: ForgotPasswordView,
      meta: { hideNavbar: true }
    },
    {
      path: '/general-home',
      name: 'General-home',
      component: () => import('../views/Operations/GeneralHomeView.vue')
    },

    {
      path: '/checkorderstatus',
      name: 'checkorderstatus',
      component: () => import('../views/Operations/CheckOrderStatus.vue')
    },
  
    {
      path: '/general-home/orders',
      name: 'general-home-orders',
      component: () => import('../views/Operations/Orders.vue')
    },
    {
      path: '/general-home/generate-quote',
      name: 'general-home-generate-quote',
      component: () => import('../views/Operations/GenerateQuoteView.vue')
    },

    {
      path: '/general-home/inventory',
      name: 'general-home-inventory',
      component: () => import('../views/Operations/InventoryView.vue')
    },

    {
      path: '/general-home/admin/cash-flow',
      name: 'general-home-admin-cash-flow',
      component: () => import('../views/Admin/CashFlowView.vue')
    },

    {
      path: '/general-home/admin/product',
      name: 'general-home-admin-product',
      component: () => import('../views/Admin/ProductView.vue')
    },

    {
      path: '/general-home/admin/worker',
      name: 'general-home-admin-worker',
      component: () => import('../views/Admin/WorkerView.vue')
    },

    {
      path: '/cotizacion',
      name: 'quotation',
      component: () => import('../views/Checkout/QuotationView.vue')
    },
    {
      path: '/cotizacion-exitosa',
      name: 'CotizacionExitosa',
      component: () => import('@/views/Checkout/SuccesfulQuotationView.vue')
    },
    {
      path: '/mis-cotizaciones',
      name: 'my-quotations',
      component: () => import('@/views/Distributor/MyQuotationsView.vue')
    },
    {
    path: '/cotizacion/:id', 
    name: 'quotation-detail',
    component: () => import('@/views/Distributor/QuotationDetailView.vue'),
    },
    {
      path: '/mis-pedidos',
      name: 'my-orders',
      component: () => import('@/views/Distributor/MyOrdersView.vue')
    },
    {
      path: '/pedido/:id', 
      name: 'order-detail',
      component: () => import('@/views/Distributor/OrderDetailView.vue'),
    }
  ],
});

router.beforeEach((to, from, next) => {
  globalLoading.value = true;
  next();
});

// Cuando la página ya cargó, la apagamos (con un pequeño retraso intencional)
router.afterEach(() => {
  // Como pediste que "no se vea tan inmediato", agregamos 500ms de gracia
  setTimeout(() => {
    globalLoading.value = false;
  }, 500); 
});


export default router
