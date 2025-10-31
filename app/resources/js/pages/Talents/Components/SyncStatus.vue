<!-- resources/js/Pages/Talents/Components/SyncStatus.vue -->
<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium gap-1"
    :class="statusClasses"
  >
    <svg class="w-3 h-3" :class="{ 'animate-spin': status === 'pending' }" fill="currentColor" viewBox="0 0 20 20">
      <path v-if="status === 'synced'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
      <path v-else-if="status === 'pending'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
      <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
    </svg>
    {{ statusText }}
    <span v-if="syncedAt" class="text-xs opacity-75">
      ({{ formattedDate }})
    </span>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
  syncedAt: String,
});

const statusClasses = computed(() => {
  switch (props.status) {
    case 'synced':
      return 'bg-green-100 text-green-800';
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'never':
      return 'bg-gray-100 text-gray-800';
    default:
      return 'bg-red-100 text-red-800';
  }
});

const statusText = computed(() => {
  switch (props.status) {
    case 'synced':
      return 'Synchronisé';
    case 'pending':
      return 'En attente';
    case 'never':
      return 'Jamais synchronisé';
    default:
      return 'Erreur';
  }
});

const formattedDate = computed(() => {
  if (!props.syncedAt) return '';
  const date = new Date(props.syncedAt);
  return date.toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
});
</script>