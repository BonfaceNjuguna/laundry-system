<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Shared Sidebar -->
    <Sidebar
      :username="username"
      @open-booking="openBookingModal = true"
      @logout="logout"
    />

    <!-- Main Content -->
    <main class="flex-1 p-4 overflow-y-auto">
      <div class="rounded-xl bg-white p-4 shadow overflow-y-auto">
        <h2 class="text-xl font-semibold mb-4">All Bookings</h2>
        <ul v-if="bookings.all.length">
          <li
            v-for="booking in bookings.all"
            :key="booking.id"
            class="py-2 border-b text-sm"
          >
            <strong>{{ booking.customer?.name }}</strong> – 
            {{ booking.service?.name }} @ {{ booking.location }}<br />
            <span class="text-gray-500 text-xs">
              {{ formatDate(booking.start_date) }} ({{ booking.status }})
            </span>
          </li>
        </ul>
        <p v-else class="text-gray-500">No bookings found</p>
      </div>
    </main>

    <!-- Booking Modal -->
    <BookingModal
      v-if="openBookingModal"
      @close="openBookingModal = false"
      @refresh="fetchBookings"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Sidebar from '@/components/SideBar.vue'
import BookingModal from '@/components/BookingModal.vue'
import api from '@/api/axios'

const router = useRouter()
const username = ref('')
const openBookingModal = ref(false)

const bookings = ref({
  all: [],
})

function formatDate(dateStr) {
  const date = new Date(dateStr)
  return date.toLocaleDateString(undefined, {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

async function fetchUser() {
  try {
    const res = await api.get('/api/user')
    username.value = res.data.name
  } catch {
    username.value = ''
  }
}

async function fetchBookings() {
  try {
    const res = await api.get('/api/bookings')
    bookings.value.all = res.data
  } catch {
    bookings.value.all = []
  }
}

function logout() {
  api.post('/api/logout').finally(() => router.push('/login'))
}

onMounted(() => {
  fetchUser()
  fetchBookings()
})
</script>
