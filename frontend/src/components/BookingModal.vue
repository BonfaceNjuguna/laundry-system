<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-2xl">
      <h2 class="text-xl font-semibold mb-4">{{ isEditing ? 'Edit Booking' : 'New Booking' }}</h2>

      <form @submit.prevent="saveBooking" class="space-y-4 text-gray-800">
        <div>
          <label class="block text-sm font-medium mb-1">Customer</label>
          <select v-model="form.customer_id" class="w-full border rounded p-2">
            <option value="">-- Select Customer --</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">
              {{ c.name }}
            </option>
          </select>
        </div>

        <div class="relative">
          <label class="block text-sm font-medium mb-1">Services</label>
          <div @click="toggleServiceDropdown" class="w-full border rounded p-2 cursor-pointer">
            {{ selectedServiceNames.length ? selectedServiceNames.join(', ') : 'Select services' }}
          </div>

          <div v-if="showServiceDropdown"
            class="absolute z-10 bg-white border rounded shadow-md w-full mt-1 max-h-60 overflow-y-auto">
            <label v-for="service in services" :key="service.id" class="flex items-center px-4 py-2 hover:bg-gray-100">
              <input type="checkbox" class="mr-2" :value="service.id" v-model="form.service_ids" />
              {{ service.name }}
            </label>
          </div>
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
          <button type="button" @click="$emit('close')" class="px-4 py-2 bg-gray-300 rounded cursor-pointer"
            :disabled="submitting">
            Cancel
          </button>
          <button type="submit"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-700 cursor-pointer disabled:opacity-60"
            :disabled="submitting">
            {{ submitting ? 'Processing...' : (isEditing ? 'Update' : 'Create') }}
          </button>
        </div>
        <div v-if="message" class="mt-2 text-center text-sm"
          :class="message.includes('successfully') ? 'text-green-600' : 'text-red-600'">
          {{ message }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import api from '@/api/axios'

const emit = defineEmits(['close', 'refresh'])
const props = defineProps({
  booking: Object,
})

const isEditing = ref(false)
const submitting = ref(false)
const message = ref('')

const form = ref({
  customer_id: '',
  service_ids: [],
  location: '',
  start_date: '',
  end_date: '',
  amount: '',
  status: 'confirmed',
  payment_method: '',
  is_paid: false,
})

const showServiceDropdown = ref(false)

function toggleServiceDropdown() {
  showServiceDropdown.value = !showServiceDropdown.value
}

const selectedServiceNames = computed(() => {
  return services.value
    .filter(s => Array.isArray(form.value.service_ids) && form.value.service_ids.includes(s.id))
    .map(s => s.name)
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
    service_ids: [],
    location: '',
    start_date: '',
    end_date: '',
    amount: '',
    status: 'confirmed',
    payment_method: '',
    is_paid: false,
  }
}

async function saveBooking() {
  submitting.value = true
  message.value = ''
  try {
    if (isEditing.value) {
      await api.put(`/api/bookings/${props.booking.id}`, form.value)
      message.value = 'Booking updated successfully!'
    } else {
      await api.post('/api/bookings', form.value)
      message.value = 'Booking created successfully!'
    }
    emit('refresh')
    setTimeout(() => {
      emit('close')
      message.value = ''
    }, 1000)
  } catch (error) {
    if (error.response?.data?.errors) {
      message.value = 'Validation failed: ' + JSON.stringify(error.response.data.errors)
    } else {
      message.value = 'Failed to save booking.'
    }
  } finally {
    submitting.value = false
  }
}

watch(
  () => props.booking,
  (newVal) => {
    if (newVal) {
      isEditing.value = true
      form.value = {
        ...newVal,
        service_ids: Array.isArray(newVal.services)
          ? newVal.services.map(s => s.id)
          : []
      }
    } else {
      isEditing.value = false
      resetForm()
    }
  },
  { immediate: true }
)

onMounted(fetchData)
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

function handleClickOutside(e) {
  const dropdown = document.querySelector('.relative')
  if (dropdown && !dropdown.contains(e.target)) {
    showServiceDropdown.value = false
  }
}
</script>