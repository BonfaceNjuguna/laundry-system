<template>
  <div class="flex h-screen bg-gray-100">
    <SideBar :username="username" @open-booking="openBookingModal = true" @logout="logout" />

    <!-- Main Dashboard Layout -->
    <main class="flex-1 p-4 grid grid-cols-2 gap-4">
      <div class="grid grid-rows-2 gap-4">
        <div class="rounded-xl bg-white p-4 shadow">
          <h2 class="text-xl font-semibold mb-2 text-gray-800">Upcoming Bookings</h2>
          <ul>
            <li v-for="booking in bookings.upcoming" :key="booking.id" class="py-2 text-sm">
              <div class="flex justify-between items-center hover:bg-gray-100 rounded-md transition px-2 py-2">
                <div>
                  <strong>{{ booking.customer?.name }}</strong> <br />
                  <span class="text-gray-800 text-xs">
                    Location: {{ booking.location }} <br>
                    Services: {{booking.services ? booking.services.map(s => s.name).join(', ') : ''}}<br>
                    Date: {{ formatDate(booking.start_date) }} <br>
                  </span>
                </div>
              </div>
            </li>
            <li v-if="bookings.upcoming.length === 0" class="text-gray-800">No upcoming bookings</li>
          </ul>
        </div>

        <div class="rounded-xl bg-white p-4 shadow">
          <h2 class="text-xl font-semibold mb-2 text-gray-800">Unpaid Bookings</h2>
          <ul>
            <li v-for="booking in bookings.unpaid" :key="booking.id" class="py-2 text-sm">
              <div class="flex justify-between items-center hover:bg-gray-100 rounded-md transition px-2 py-2">
                <div>
                  <strong>{{ booking.customer?.name }}</strong><br />
                  <span class="text-gray-800 text-xs">
                    Location: {{ booking.location }} <br>
                    Services: {{booking.services ? booking.services.map(s => s.name).join(', ') : ''}}<br>
                    Date: {{ formatDate(booking.start_date) }} <br>
                  </span>
                </div>
              </div>
            </li>
            <li v-if="bookings.unpaid.length === 0" class="text-gray-800">No unpaid bookings</li>
          </ul>
        </div>
      </div>

      <div class="row-span-2 rounded-xl bg-white p-4 shadow overflow-y-auto">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">All Bookings</h2>
        <ul>
          <li v-for="booking in bookings.all" :key="booking.id" class="py-2 text-sm">
            <div class="flex justify-between items-center hover:bg-gray-100 rounded-md transition px-2 py-2">
              <div>
                <strong>{{ booking.customer?.name }}</strong><br />
                <span class="text-gray-800 text-xs">
                  Location: {{ booking.location }} <br>
                  Services: {{booking.services ? booking.services.map(s => s.name).join(', ') : ''}}<br>
                  Date: {{ formatDate(booking.start_date) }} <br>
                  Status: {{ booking.status }}
                </span>
              </div>
            </div>
          </li>
          <li v-if="bookings.all.length === 0" class="text-gray-800">No bookings found</li>
        </ul>
      </div>
    </main>

    <BookingModal v-if="openBookingModal" @close="openBookingModal = false" @refresh="fetchBookings()" />
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
  unpaid: []
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
    now.setHours(0, 0, 0, 0) // Normalize to start of day for comparison

    bookings.value.all = all

    bookings.value.upcoming = all.filter(b => {
      const endDate = new Date(b.end_date || b.start_date)
      return endDate >= now
    })

    bookings.value.unpaid = all.filter(b => !b.is_paid)
  } catch {
    bookings.value = { all: [], unpaid: [], upcoming: [] }
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
