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
      notifications.value = notifications.value.filter(n => n.id !== id);
    }, 4000);
  };

  return {
    notifications,
    notify
  };
}