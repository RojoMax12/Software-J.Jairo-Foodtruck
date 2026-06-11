import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../components/LoginView.vue'
import RegisterView from '../components/RegisterView.vue'
import ForgotPasswordView from '../components/ForgotPasswordView.vue'
import HomeView from '../views/Home/HomeView.vue'

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
      path: '/admin',
      name: 'admin-home',
      component: () => import('../views/Admin/AdminHomeView.vue')
    },
    {
      path: '/admin/quotes',
      name: 'admin-quotes',
      component: () => import('../views/Admin/Quotes.vue')
    },
    {
      path: '/admin/orders',
      name: 'admin-orders',
      component: () => import('../views/Admin/Orders.vue')
    },
    {
      path: '/admin/generate-quote',
      name: 'admin-generate-quote',
      component: () => import('../views/Admin/AdminGenerateQuoteView.vue')
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
})


export default router
