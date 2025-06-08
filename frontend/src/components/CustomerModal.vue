<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md shadow-lg">
      <h2 class="text-lg font-semibold mb-4">
        {{ customer?.id ? 'Edit Customer' : 'Add Customer' }}
      </h2>

      <div class="space-y-3">
        <input
          type="text"
          v-model="form.name"
          placeholder="Customer Name"
          class="w-full p-2 border rounded"
        />
        <input
          type="text"
          v-model="form.phone"
          placeholder="Phone Number"
          class="w-full p-2 border rounded"
        />
        <input
          type="email"
          v-model="form.email"
          placeholder="Email (optional)"
          class="w-full p-2 border rounded"
        />
      </div>

      <div class="mt-4 flex justify-end gap-2">
        <button
          class="px-4 py-1 bg-gray-300 rounded hover:bg-gray-400"
          @click="$emit('close')"
        >
          Cancel
        </button>
        <button
          class="px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
          @click="saveCustomer"
          :disabled="submitting"
        >
          {{ submitting ? 'Saving...' : 'Save' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import api from '@/api/axios'

const props = defineProps({
  customer: Object
})
const emit = defineEmits(['close', 'saved'])

const form = ref({
  name: '',
  phone: '',
  email: ''
})
const submitting = ref(false)

watch(() => props.customer, (val) => {
  form.value = val
    ? { name: val.name, phone: val.phone, email: val.email || '' }
    : { name: '', phone: '', email: '' }
}, { immediate: true })

async function saveCustomer() {
  submitting.value = true
  try {
    if (props.customer?.id) {
      await api.put(`/api/customers/${props.customer.id}`, form.value)
    } else {
      await api.post('/api/customers', form.value)
    }
    emit('saved')
  } catch (err) {
    alert('Failed to save customer')
  } finally {
    submitting.value = false
  }
}
</script>
