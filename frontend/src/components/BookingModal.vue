<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-2xl">
      <h2 class="text-xl font-semibold mb-4">{{ isEditing ? 'Edit Booking' : 'New Booking' }}</h2>

      <form @submit.prevent="saveBooking" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Customer</label>
          <select v-model="form.customer_id" class="w-full border rounded p-2">
            <option value="">-- Select Customer --</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">
              {{ c.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Service</label>
          <select v-model="form.service_id" class="w-full border rounded p-2">
            <option value="">-- Select Service --</option>
            <option v-for="s in services" :key="s.id" :value="s.id">
              {{ s.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Location</label>
          <input v-model="form.location" type="text" class="w-full border rounded p-2" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Start Date</label>
            <input v-model="form.start_date" type="datetime-local" class="w-full border rounded p-2" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">End Date</label>
            <input v-model="form.end_date" type="datetime-local" class="w-full border rounded p-2" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Amount</label>
          <input v-model.number="form.amount" type="number" min="0" class="w-full border rounded p-2" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Status</label>
          <select v-model="form.status" class="w-full border rounded p-2">
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Payment Method</label>
          <select v-model="form.payment_method" class="w-full border rounded p-2">
            <option value="">-- None --</option>
            <option value="mpesa">Mpesa</option>
            <option value="cash">Cash</option>
            <option value="card">Card</option>
          </select>
        </div>

        <div class="flex items-center">
          <input v-model="form.is_paid" type="checkbox" id="is_paid" class="mr-2" />
          <label for="is_paid">Is Paid?</label>
        </div>

        <div class="flex justify-end space-x-2">
          <button type="button" @click="$emit('close')" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
            {{ isEditing ? 'Update' : 'Create' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/api/axios'

const emit = defineEmits(['close', 'refresh'])
const props = defineProps({
  booking: Object,
})

const isEditing = ref(false)
const form = ref({
  customer_id: '',
  service_id: '',
  location: '',
  start_date: '',
  end_date: '',
  amount: '',
  status: 'pending',
  payment_method: '',
  is_paid: false,
})

const customers = ref([])
const services = ref([])

async function fetchData() {
  const [custRes, servRes] = await Promise.all([
    api.get('/api/customers'),
    api.get('/api/services'),
  ])
  customers.value = custRes.data
  services.value = servRes.data
}

function resetForm() {
  form.value = {
    customer_id: '',
    service_id: '',
    location: '',
    start_date: '',
    end_date: '',
    amount: '',
    status: 'pending',
    payment_method: '',
    is_paid: false,
  }
}

async function saveBooking() {
  try {
    if (isEditing.value) {
      await api.put(`/api/bookings/${props.booking.id}`, form.value)
    } else {
      await api.post('/api/bookings', form.value)
    }
    emit('refresh')
    emit('close')
  } catch (error) {
    console.error('Error saving booking', error)
    if (error.response?.data?.errors) {
      alert('Validation failed: ' + JSON.stringify(error.response.data.errors))
    }
  }
}

watch(
  () => props.booking,
  (newVal) => {
    if (newVal) {
      isEditing.value = true
      form.value = { ...newVal }
    } else {
      isEditing.value = false
      resetForm()
    }
  },
  { immediate: true }
)

onMounted(fetchData)
</script>
