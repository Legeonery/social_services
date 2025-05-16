<!-- components/forms/users/UserFormWrapper.vue -->
<script setup lang="ts">
import { computed } from 'vue'
import AdminForm from './AddAdmin.vue'
import SocialWorkerForm from './SocialWorkerForm.vue'
import ClientForm from './ClientForm.vue' // ✅

const props = defineProps<{
  activeTab: 'clients' | 'social_workers' | 'admins'
  modelValue: any
  isEdit: boolean
}>()

const emit = defineEmits(['submit', 'cancel'])

const showAdminForm = computed(() => props.activeTab === 'admins')
const showSocialWorkerForm = computed(() => props.activeTab === 'social_workers')
const showClientForm = computed(() => props.activeTab === 'clients') // ✅
</script>

<template>
  <AdminForm
    v-if="showAdminForm"
    :model-value="modelValue"
    :is-edit="isEdit"
    @submit="emit('submit', $event)"
    @cancel="emit('cancel')"
  />

  <SocialWorkerForm
    v-else-if="showSocialWorkerForm"
    :model-value="modelValue"
    :is-edit="isEdit"
    @submit="emit('submit', $event)"
    @cancel="emit('cancel')"
  />

  <ClientForm
    v-else-if="showClientForm"
    :model-value="modelValue"
    :is-edit="isEdit"
    @submit="emit('submit', $event)"
    @cancel="emit('cancel')"
  />

  <div v-else class="p-4 text-gray-500">Форма не реализована.</div>
</template>
