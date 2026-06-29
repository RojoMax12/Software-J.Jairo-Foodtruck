import { ref } from 'vue';

// Al exportarlo de esta manera, cualquier componente que lo importe compartirá el mismo valor
export const globalLoading = ref(false);