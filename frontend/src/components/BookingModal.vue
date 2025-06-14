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

        <!-- Services Section -->
        <div>
          <label class="block text-sm font-medium mb-1">Services</label>
          <div v-for="(service, idx) in form.services" :key="idx" class="flex gap-2 mb-2">
            <select v-model="service.service_id" class="border rounded p-2 flex-1">
              <option value="">Select Service</option>
              <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
            <input v-model.number="service.amount" type="number" min="0" placeholder="Amount"
              class="border rounded p-2 w-32" />
            <button type="button" @click="removeService(idx)" class="text-red-500"
              v-if="form.services.length > 1">Remove</button>
          </div>
          <button type="button" @click="addService" class="text-green-600">+ Add Service</button>
        </div>

        <!-- Expenses Section -->
        <div>
          <label class="block text-sm font-medium mb-1">Expenses</label>
          <div v-for="(expense, idx) in form.expenses" :key="idx" class="flex gap-2 mb-2">
            <select v-model="expense.category" class="border rounded p-2 flex-1">
              <option value="">Select Category</option>
              <option v-for="cat in expenseCategories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
            <input v-model.number="expense.amount" type="number" min="0" placeholder="Amount"
              class="border rounded p-2 w-32" />
            <input v-model="expense.description" type="text" placeholder="Description (optional)"
              class="border rounded p-2 flex-1" />
            <button type="button" @click="removeExpense(idx)" class="text-red-500"
              v-if="form.expenses.length > 1">Remove</button>
          </div>
          <button type="button" @click="addExpense" class="text-green-600">+ Add Expense</button>
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
        <div v-if="form.payment_method === 'mpesa'">
          <label class="block text-sm font-medium mb-1">Mpesa Code</label>
          <input v-model="form.mpesa_transaction_id" type="text" class="w-full border rounded p-2"
            placeholder="Enter Mpesa Transaction ID" />
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

const expenseCategories = [
  'Transport',
  'Detergents',
  'Salaries',
  'Electricity',
  'Water',
  'Maintenance',
  'Other'
]

const form = ref({
  customer_id: '',
  services: [{ service_id: '', amount: 0 }],
  expenses: [{ category: '', amount: 0, description: '' }],
  location: '',
  start_date: '',
  end_date: '',
  status: 'confirmed',
  payment_method: '',
  mpesa_transaction_id: '',
  is_paid: false,
})

function addService() {
  form.value.services.push({ service_id: '', amount: 0 })
}
function removeService(idx) {
  form.value.services.splice(idx, 1)
}
function addExpense() {
  form.value.expenses.push({ category: '', amount: 0, description: '' })
}
function removeExpense(idx) {
  form.value.expenses.splice(idx, 1)
}

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
    services: [{ service_id: '', amount: 0 }],
    expenses: [{ category: '', amount: 0, description: '' }],
    location: '',
    start_date: '',
    end_date: '',
    status: 'confirmed',
    payment_method: '',
    mpesa_transaction_id: '',
    is_paid: false,
  }
}

async function saveBooking() {
  submitting.value = true
  message.value = ''
  try {
    form.value.expenses = form.value.expenses.filter(e => e.category && e.amount > 0)

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
        services: Array.isArray(newVal.services)
          ? newVal.services.map(s => ({
            service_id: s.id,
            amount: s.pivot?.amount ?? 0
          }))
          : [{ service_id: '', amount: 0 }],
        expenses: Array.isArray(newVal.expenses)
          ? newVal.expenses.map(e => ({
            category: e.category,
            amount: e.amount,
            description: e.description || ''
          }))
          : [{ category: '', amount: 0, description: '' }],
        mpesa_transaction_id: newVal.mpesa_transaction_id || '',
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