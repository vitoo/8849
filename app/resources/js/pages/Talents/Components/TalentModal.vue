<!-- resources/js/Pages/Talents/Components/TalentModal.vue -->
<template>
  <TransitionRoot :show="show" as="template">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-900/25" />

      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all">
              <DialogTitle class="text-lg font-medium leading-6 text-gray-900 mb-4">
                {{ talent ? 'Éditer un talent' : 'Nouveau talent' }}
              </DialogTitle>

              <form @submit.prevent="submit" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Prénom
                  </label>
                  <input
                    v-model="form.first_name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                 <p v-if="errors.first_name" class="text-sm text-red-600 mt-1">{{ errors.first_name }}</p>

                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nom
                  </label>
                  <input
                    v-model="form.last_name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <p v-if="errors.last_name" class="text-sm text-red-600 mt-1">{{ errors.last_name }}</p>

                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <p vif="errors.email" class="text-sm text-red-600 mt-1">{{ errors.email }}</p>
                </div>

                <div v-if="!talent" class="text-sm text-gray-500 bg-gray-50 p-3 rounded">
                  <strong>Username:</strong> {{ generatedUsername || 'Sera généré automatiquement' }}
                </div>

                <div class="flex justify-end gap-3 mt-6">
                  <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                  >
                    Annuler
                  </button>
                  <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                  >
                    {{ talent ? 'Mettre à jour' : 'Créer' }}
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';

const props = defineProps({
  show: Boolean,
  talent: Object,
  errors: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
});

const generatedUsername = computed(() => {
  if (form.value.first_name && form.value.last_name) {
    return (form.value.first_name + '-' + form.value.last_name)
      .toLowerCase()
      .replace(/[^a-z0-9-]/g, '-')
      .replace(/-+/g, '-');
  }
  return '';
});

watch(() => props.talent, (newTalent) => {
  if (newTalent) {
    form.value = {
      first_name: newTalent.first_name,
      last_name: newTalent.last_name,
      email: newTalent.email,
    };
  } else {
    form.value = {
      first_name: '',
      last_name: '',
      email: '',
    };
  }
}, { immediate: true });

const submit = () => {
  emit('submit', { ...form.value });
};
</script>