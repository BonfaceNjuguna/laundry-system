<template>
  <div class="flex h-screen bg-gray-100">
    <SideBar
      :username="username"
      @open-booking="openBookingModal = true"
      @logout="logout"
    />

    <!-- Main Dashboard Layout -->
    <main class="w-full p-4 grid grid-cols-2 gap-4">
      <div class="grid grid-rows-2 gap-4">
        <div class="rounded-xl bg-white p-4 shadow">
          <h2 class="text-xl font-semibold mb-2">Upcoming Bookings</h2>
          <ul>
            <li v-for="booking in bookings.upcoming" :key="booking.id" class="py-2 border-b text-sm">
              <strong>{{ booking.customer?.name }}</strong> -
              {{ booking.service?.name }} @ {{ booking.location }}<br />
              <span class="text-gray-500 text-xs">{{ formatDate(booking.start_date) }}</span>
            </li>
          </ul>

        </div>

        <div class="rounded-xl bg-white p-4 shadow">
          <h2 class="text-xl font-semibold mb-2">Pending Bookings</h2>
          <ul>
            <li v-for="booking in bookings.pending" :key="booking.id" class="py-2 border-b text-sm">
              <strong>{{ booking.customer?.name }}</strong> -
              {{ booking.service?.name }} @ {{ booking.location }} on {{ formatDate(booking.start_date) }}
            </li>
            <li v-if="bookings.pending.length === 0" class="text-gray-500">No pending bookings</li>
          </ul>
        </div>
      </div>

      <div class="row-span-2 rounded-xl bg-white p-4 shadow overflow-y-auto">
        <h2 class="text-xl font-semibold mb-4">All Bookings</h2>
        <ul>
          <li v-for="booking in bookings.all" :key="booking.id" class="py-2 border-b text-sm">
            {{ booking.location }} – {{ formatDate(booking.start_date) }} ({{ booking.status }})
          </li>
          <li v-if="bookings.all.length === 0" class="text-gray-500">No bookings found</li>
        </ul>
      </div>
    </main>

    <BookingModal
      v-if="openBookingModal"
      @close="openBookingModal = false"
      @refresh="fetchBookings()"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import { useRouter } from 'vue-router'
import BookingModal from '@/components/BookingModal.vue'
import SideBar from '@/components/SideBar.vue'

const username = ref('')
const loadingBookings = ref(false)
const openBookingModal = ref(false)
const newBookingDetails = ref('')
const submittingBooking = ref(false)
const router = useRouter()

// Grouped bookings
const bookings = ref({
  all: [],
  upcoming: [],
  pending: []
})

function formatDate(dateStr) {
  const date = new Date(dateStr)
  return date.toLocaleDateString()
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
  loadingBookings.value = true
  try {
    const res = await api.get('/api/bookings')
    const all = res.data
    const now = new Date()

    bookings.value.all = all
    bookings.value.pending = all.filter(b => b.status === 'pending')
    bookings.value.upcoming = all.filter(b =>
      b.status === 'confirmed' && new Date(b.start_date) > now
    )
  } catch {
    bookings.value = { all: [], pending: [], upcoming: [] }
  } finally {
    loadingBookings.value = false
  }
}

async function submitBooking() {
  if (newBookingDetails.value.trim() === '') return
  submittingBooking.value = true
  try {
    const res = await api.post('/api/bookings', { details: newBookingDetails.value.trim() })
    await fetchBookings()
    newBookingDetails.value = ''
    openBookingModal.value = false
  } catch {
    alert('Failed to add booking')
  } finally {
    submittingBooking.value = false
  }
}

function logout() {
  api.post('/api/logout').finally(() => {
    router.push('/login')
  })
}

onMounted(() => {
  fetchUser()
  fetchBookings()
})
</script>
