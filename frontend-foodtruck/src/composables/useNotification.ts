import { ref } from 'vue';

interface Notification {
  id: number;
  message: string;
  type: 'success' | 'error'| 'warning';
}

const notifications = ref<Notification[]>([]);

export function useNotification() {
  const notify = (message: string, type: 'success' | 'error'| 'warning' = 'success') => {
    const id = Date.now();
    notifications.value.push({ id, message, type });

    setTimeout(() => {
      dismissNotification(id);
    }, 4000);
  };

  const dismissNotification = (id: number) => {
    notifications.value = notifications.value.filter(n => n.id !== id);
  };

  return {
    notifications,
    notify,
    dismissNotification
  };
}