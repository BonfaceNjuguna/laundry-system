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
          <h2 class="text-xl font-semibold">All Bookings</h2>
          <button
            class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700"
            @click="openBooking(null)"
          >
            + Add Booking
          </button>
        </div>

        <ul v-if="bookings.all.length">
          <li
            v-for="booking in bookings.all"
            :key="booking.id"
            class="py-2 border-b text-sm flex justify-between items-center"
          >
            <div>
              <strong>{{ booking.customer?.name }}</strong> – 
              {{ booking.service?.name }} @ {{ booking.location }}<br />
              <span class="text-gray-500 text-xs">
                {{ formatDate(booking.start_date) }} ({{ booking.status }})
              </span>
            </div>
            <div class="flex gap-2">
              <button
                class="bg-yellow-400 px-2 py-1 text-xs rounded hover:bg-yellow-500"
                @click="openBooking(booking)"
              >
                Edit
              </button>
              <button
                class="bg-red-500 text-white px-2 py-1 text-xs rounded hover:bg-red-600"
                @click="deleteBooking(booking.id)"
              >
                Delete
              </button>
            </div>
          </li>
        </ul>
        <p v-else class="text-gray-500">No bookings found</p>
      </div>
    </main>

    <BookingModal
      v-if="openBookingModal"
      :booking="selectedBooking"
      @close="openBookingModal = false"
      @saved="handleBookingSaved"
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
const selectedBooking = ref(null)

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

function openBooking(booking) {
  selectedBooking.value = booking
  openBookingModal.value = true
}

async function deleteBooking(id) {
  if (confirm('Are you sure you want to delete this booking?')) {
    try {
      await api.delete(`/api/bookings/${id}`)
      fetchBookings()
    } catch {
      alert('Failed to delete booking')
    }
  }
}

function handleBookingSaved() {
  openBookingModal.value = false
  fetchBookings()
}

function logout() {
  api.post('/api/logout').finally(() => router.push('/login'))
}

onMounted(() => {
  fetchUser()
  fetchBookings()
})
</script>
