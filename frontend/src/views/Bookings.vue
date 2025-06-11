<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar :username="username" @open-booking="() => { selectedBooking.value = null; openBookingModal.value = true }"
      @logout="logout" />

    <main class="flex-1 p-4 overflow-y-auto">
      <div class="rounded-xl bg-white p-4 shadow">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold text-gray-800">All Bookings</h2>
          <button class="bg-green-500 text-white px-4 py-1 rounded hover:bg-green-700 cursor-pointer"
            @click="openBooking(null)">
            + Add a Booking
          </button>
        </div>

        <ul v-if="bookings.all.length">
          <li v-for="booking in bookings.all" :key="booking.id" class="py-2 text-sm">
            <div class="flex justify-between items-center hover:bg-gray-100 rounded-md transition px-2 py-2">
              <div>
                <strong>{{ booking.customer?.name }}</strong> –
                {{ booking.service?.name }} @ {{ booking.location }}<br />
                <span class="text-gray-800 text-xs">
                  {{ formatDate(booking.start_date) }} <br>
                  Services: {{booking.services ? booking.services.map(s => s.name).join(', ') : ''}}<br>
                  Status: {{ booking.status }}
                </span>
              </div>
              <div class="flex gap-2">
                <button class="bg-green-500 px-2 py-1 text-white text-xs rounded hover:bg-green-600"
                  @click="openBooking(booking)">
                  Edit
                </button>
                <button class="bg-red-500 text-white px-2 py-1 text-xs rounded hover:bg-red-600"
                  @click="deleteBooking(booking.id)">
                  Delete
                </button>
              </div>
            </div>
          </li>
        </ul>
        <p v-else class="text-gray-800">No bookings found</p>
      </div>
    </main>

    <BookingModal v-if="openBookingModal" :booking="selectedBooking" @close="openBookingModal = false"
      @saved="handleBookingSaved" @refresh="fetchBookings" />
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
