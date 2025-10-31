<!-- resources/js/Pages/Talents/Index.vue -->
<template>
  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <!-- Header -->
        <div class="p-6 bg-white border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-2xl font-bold text-gray-800">Gestion des Talents</h2>
          <div class="flex gap-3">
            
            <button
              @click="showCreateModal = true"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nouveau Talent
            </button>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prénom</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut Sync</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="!talents.data || talents.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                  Aucun talent créé pour le moment
                </td>
              </tr>
              <tr v-for="talent in talents.data" :key="talent.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ talent.username }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ talent.first_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ talent.last_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ talent.email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <SyncStatus :status="talent.sync_status" :synced-at="talent.synced_at" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                  <button
                    @click="editTalent(talent)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Éditer
                  </button>
                  <button
                    @click="syncTalent(talent)"
                    class="text-green-600 hover:text-green-900"
                    title="Resynchroniser"
                  >
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(talent)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
              <tr v-if="talents.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                  Aucun talent enregistré
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="talents.last_page > 1" class="mt-4 flex justify-center space-x-2 mb-4">
          <button
            v-for="link in talents.links"
            :key="link.label"
            v-html="link.label"
            :disabled="!link.url"
            @click.prevent="link.url && router.get(link.url)"
  class="px-3 py-1 border rounded bg-white text-gray-800 hover:bg-gray-200 disabled:opacity-50 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700"
          />
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <TalentModal
      :show="showCreateModal || showEditModal"
      :talent="editingTalent"
      :errors="props.errors"  
      @close="closeModal"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import TalentModal from './Components/TalentModal.vue';
import SyncStatus from './Components/SyncStatus.vue';
import { onMounted, onUnmounted } from 'vue';

let pollInterval = null;

onMounted(() => {
  // poll every 5s
  pollInterval = setInterval(() => {
    router.get('/talents', {}, { preserveState: true, replace: true });
  }, 5000);
});

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});

const props = defineProps({
  talents: Object,
   errors: Object, // 1. Receive errors from Inertia
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingTalent = ref(null);

const form = useForm({
  id: null,
  first_name: '',
  last_name: '',
  email: '',
});

const editTalent = (talent) => {
  editingTalent.value = { ...talent };
  showEditModal.value = true;
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingTalent.value = null;
};

const handleSubmit = (data) => {
  if (editingTalent.value?.id) {
    router.put(`/talents/${editingTalent.value.id}`, data, {
      onSuccess: () => closeModal(),
    });
  } else {
    router.post('/talents', data, {
      onSuccess: () => closeModal(),
    });
  }
};

const confirmDelete = (talent) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer ${talent.first_name} ${talent.last_name} ?`)) {
    router.delete(`/talents/${talent.id}`);
  }
};

const syncTalent = (talent) => {
  router.post(`/talents/${talent.id}/sync`);
};

</script>