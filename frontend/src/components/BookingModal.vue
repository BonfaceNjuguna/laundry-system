<!-- BookingModal.vue -->
<template>
  <div class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
    <div class="bg-white p-6 rounded-xl w-full max-w-lg shadow-lg">
      <h2 class="text-xl font-semibold mb-4">New Booking</h2>

      <label class="block mb-2 text-sm">Customer</label>
      <select v-model="form.customer_id" class="w-full mb-4 p-2 border rounded" @change="checkNewCustomer">
        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
        <option value="new">➕ Add New Customer</option>
      </select>

      <div v-if="form.customer_id === 'new'" class="mb-4">
        <input v-model="newCustomer.name" class="w-full p-2 border rounded mb-2" placeholder="Customer Name" />
        <input v-model="newCustomer.email" class="w-full p-2 border rounded mb-2" placeholder="Email" />
        <input v-model="newCustomer.phone" class="w-full p-2 border rounded" placeholder="Phone" />
      </div>

      <label class="block mb-2 text-sm">Service</label>
      <select v-model="form.service_id" class="w-full mb-4 p-2 border rounded" @change="checkNewService">
        <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
        <option value="new">➕ Add New Service</option>
      </select>

      <div v-if="form.service_id === 'new'" class="mb-4">
        <input v-model="newService.name" class="w-full p-2 border rounded mb-2" placeholder="Service Name" />
        <textarea v-model="newService.description" class="w-full p-2 border rounded mb-2" placeholder="Description"></textarea>
        <input v-model="newService.price" type="number" class="w-full p-2 border rounded" placeholder="Price" />
      </div>

      <input v-model="form.location" class="w-full p-2 border rounded mb-4" placeholder="Location" />
      <input v-model="form.start_date" type="date" class="w-full p-2 border rounded mb-4" />
      <input v-model="form.end_date" type="date" class="w-full p-2 border rounded mb-4" />
      <select v-model="form.status" class="w-full mb-4 p-2 border rounded">
        <option>pending</option>
        <option>confirmed</option>
        <option>completed</option>
        <option>cancelled</option>
      </select>

      <div class="flex justify-end gap-2">
        <button @click="$emit('close')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
        <button @click="submit" :disabled="submitting" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Submit</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'

const emit = defineEmits(['close', 'refresh'])

const customers = ref([])
const services = ref([])
const submitting = ref(false)

const form = ref({
  customer_id: '',
  service_id: '',
  location: '',
  start_date: '',
  end_date: '',
  status: 'pending',
})

const newCustomer = ref({ name: '', email: '', phone: '' })
const newService = ref({ name: '', description: '', price: '' })

onMounted(async () => {
  customers.value = (await api.get('/api/customers')).data
  services.value = (await api.get('/api/services')).data
})

function checkNewCustomer() {
  if (form.value.customer_id !== 'new') newCustomer.value = { name: '', email: '', phone: '' }
}

function checkNewService() {
  if (form.value.service_id !== 'new') newService.value = { name: '', description: '', price: '' }
}

async function submit() {
  submitting.value = true

  try {
    // Handle new customer
    if (form.value.customer_id === 'new') {
      const { data } = await api.post('/api/customers', newCustomer.value)
      form.value.customer_id = data.id
    }

    // Handle new service
    if (form.value.service_id === 'new') {
      const { data } = await api.post('/api/services', newService.value)
      form.value.service_id = data.id
    }

    await api.post('/api/bookings', form.value)
    emit('refresh')
    emit('close')
  } catch (err) {
    alert('Booking failed')
  } finally {
    submitting.value = false
  }
}
</script>
