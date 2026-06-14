import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './assets/style.css'   



const app = createApp(App)

app.use(router)


app.directive('role', {
  mounted(el, binding) {
    const userParsed = localStorage.getItem('user');
    let currentRole = null;

    if (userParsed) {
      try {
        currentRole = JSON.parse(userParsed).id_rol;
      } catch (e) {
        console.error("Error parseando rol en directiva", e);
      }
    }

    // El valor que le pasas a la directiva (ejemplo: v-role="1" o v-role="[1, 3]")
    const allowedRoles = Array.isArray(binding.value) ? binding.value : [binding.value];

    // Si el rol del usuario NO está en la lista de permitidos, removemos el botón del HTML de raíz
    if (!allowedRoles.includes(currentRole)) {
      el.parentNode && el.parentNode.removeChild(el);
    }
  }
});

app.mount('#app')
