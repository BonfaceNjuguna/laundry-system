<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar
      :username="username"
      @open-booking="openBookingModal = true"
      @logout="logout"
    />

    <main class="flex-1 p-4 overflow-y-auto">
      <div class="rounded-xl bg-white p-4 shadow">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">All Services</h2>
          <button
            class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700"
            @click="openServiceModal(null)"
          >
            + Add Service
          </button>
        </div>

        <ul v-if="services.length">
          <li
            v-for="service in services"
            :key="service.id"
            class="py-2 border-b text-sm flex justify-between items-center"
          >
            <div>
              <strong>{{ service.name }}</strong><br />
              <span class="text-gray-500 text-xs">
                Price: {{ service.price }}
              </span>
            </div>
            <div class="flex gap-2">
              <button
                class="bg-yellow-400 px-2 py-1 text-xs rounded hover:bg-yellow-500"
                @click="openServiceModal(service)"
              >
                Edit
              </button>
              <button
                class="bg-red-500 text-white px-2 py-1 text-xs rounded hover:bg-red-600"
                @click="deleteService(service.id)"
              >
                Delete
              </button>
            </div>
          </li>
        </ul>
        <p v-else class="text-gray-500">No services found</p>
      </div>
    </main>

    <BookingModal
      v-if="openBookingModal"
      @close="openBookingModal = false"
      @refresh="fetchServices"
    />

    <ServiceModal
      v-if="showServiceModal"
      :service="selectedService"
      @close="showServiceModal = false"
      @saved="handleServiceSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Sidebar from '@/components/SideBar.vue'
import BookingModal from '@/components/BookingModal.vue'
import ServiceModal from '@/components/ServiceModal.vue'
import api from '@/api/axios'

const router = useRouter()
const username = ref('')
const openBookingModal = ref(false)
const services = ref([])

const showServiceModal = ref(false)
const selectedService = ref(null)

async function fetchUser() {
  try {
    const res = await api.get('/api/user')
    username.value = res.data.name
  } catch {
    username.value = ''
  }
}

async function fetchServices() {
  try {
    const res = await api.get('/api/services')
    services.value = res.data
  } catch {
    services.value = []
  }
}

function openServiceModal(service) {
  selectedService.value = service
  showServiceModal.value = true
}

async function deleteService(id) {
  if (confirm('Are you sure you want to delete this service?')) {
    try {
      await api.delete(`/api/services/${id}`)
      fetchServices()
    } catch {
      alert('Failed to delete service')
    }
  }
}

function handleServiceSaved() {
  showServiceModal.value = false
  fetchServices()
}

function logout() {
  api.post('/api/logout').finally(() => router.push('/login'))
}

onMounted(() => {
  fetchUser()
  fetchServices()
})
</script>
