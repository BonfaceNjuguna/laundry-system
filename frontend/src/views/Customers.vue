<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar
      :username="username"
      @open-booking="openBookingModal = true"
      @logout="logout"
    />

    <!-- Main Content -->
    <main class="flex-1 p-4 overflow-y-auto">
      <div class="rounded-xl bg-white p-4 shadow overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">All Customers</h2>
          <button
            class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700"
            @click="openCustomerModal(null)"
          >
            + Add Customer
          </button>
        </div>

        <ul v-if="customers.length">
          <li
            v-for="customer in customers"
            :key="customer.id"
            class="py-2 border-b text-sm flex justify-between items-center"
          >
            <div>
              <strong>{{ customer.name }}</strong><br />
              <span class="text-gray-500 text-xs">
                Phone: {{ customer.phone }}<br />
                Email: {{ customer.email || 'N/A' }}
              </span>
            </div>
            <div class="flex gap-2">
              <button
                class="bg-yellow-400 px-2 py-1 text-xs rounded hover:bg-yellow-500"
                @click="openCustomerModal(customer)"
              >
                Edit
              </button>
              <button
                class="bg-red-500 text-white px-2 py-1 text-xs rounded hover:bg-red-600"
                @click="deleteCustomer(customer.id)"
              >
                Delete
              </button>
            </div>
          </li>
        </ul>
        <p v-else class="text-gray-500">No customers found</p>
      </div>
    </main>

    <!-- Booking Modal -->
    <BookingModal
      v-if="openBookingModal"
      @close="openBookingModal = false"
      @refresh="fetchCustomers"
    />

    <!-- Customer Modal -->
    <CustomerModal
      v-if="showCustomerModal"
      :customer="selectedCustomer"
      @close="showCustomerModal = false"
      @saved="handleCustomerSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Sidebar from '@/components/SideBar.vue'
import BookingModal from '@/components/BookingModal.vue'
import CustomerModal from '@/components/CustomerModal.vue'
import api from '@/api/axios'

const router = useRouter()
const username = ref('')
const openBookingModal = ref(false)

const customers = ref([])
const showCustomerModal = ref(false)
const selectedCustomer = ref(null)

async function fetchUser() {
  try {
    const res = await api.get('/api/user')
    username.value = res.data.name
  } catch {
    username.value = ''
  }
}

async function fetchCustomers() {
  try {
    const res = await api.get('/api/customers')
    customers.value = res.data
  } catch {
    customers.value = []
  }
}

function logout() {
  api.post('/api/logout').finally(() => router.push('/login'))
}

function openCustomerModal(customer) {
  selectedCustomer.value = customer
  showCustomerModal.value = true
}

async function deleteCustomer(id) {
  if (confirm('Are you sure you want to delete this customer?')) {
    try {
      await api.delete(`/api/customers/${id}`)
      fetchCustomers()
    } catch {
      alert('Failed to delete customer')
    }
  }
}

function handleCustomerSaved() {
  showCustomerModal.value = false
  fetchCustomers()
}

onMounted(() => {
  fetchUser()
  fetchCustomers()
})
</script>
