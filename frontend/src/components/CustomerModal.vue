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
          :disabled="submitting"
        >
          Cancel
        </button>
        <button
          class="px-4 py-1 bg-green-500 text-white rounded hover:bg-green-700 disabled:opacity-60"
          @click="saveCustomer"
          :disabled="submitting"
        >
          {{ submitting ? 'Saving...' : 'Save' }}
        </button>
      </div>
      <div v-if="message" class="mt-2 text-center text-sm" :class="message.includes('successfully') ? 'text-green-600' : 'text-red-600'">
        {{ message }}
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
const message = ref('')

watch(() => props.customer, (val) => {
  form.value = val
    ? { name: val.name, phone: val.phone, email: val.email || '' }
    : { name: '', phone: '', email: '' }
}, { immediate: true })

async function saveCustomer() {
  submitting.value = true
  message.value = ''
  try {
    if (props.customer?.id) {
      await api.put(`/api/customers/${props.customer.id}`, form.value)
      message.value = 'Customer updated successfully!'
    } else {
      await api.post('/api/customers', form.value)
      message.value = 'Customer created successfully!'
    }
    emit('saved')
    setTimeout(() => {
      emit('close')
      message.value = ''
    }, 1000)
  } catch (err) {
    message.value = 'Failed to save customer'
  } finally {
    submitting.value = false
  }
}
</script>