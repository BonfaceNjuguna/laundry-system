<template>
  <div class="flex h-screen bg-gray-100">

    <!-- Sidebar -->
    <aside class="w-96 bg-white flex flex-col justify-between shadow-md p-6">
      <div>
        <h2 class="text-2xl font-bold mb-4">Dashboard</h2>
        <p v-if="username">Welcome, {{ username }}!</p>
        <p v-else class="text-gray-500">Loading user...</p>

        <button
          class="mt-6 w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition"
          @click="openBookingModal = true"
          :disabled="loadingBookings"
        >
          Make a Booking
        </button>

        <div class="mt-6">
          <h3 class="font-semibold mb-2">Your Bookings</h3>
          <ul class="max-h-64 overflow-auto space-y-2">
            <li v-if="loadingBookings" class="text-gray-500">Loading bookings...</li>
            <li
              v-for="booking in bookings"
              :key="booking.id"
              class="p-2 bg-gray-50 rounded shadow-sm"
            >
              {{ booking.details }}
            </li>
            <li v-if="!loadingBookings && bookings.length === 0" class="text-gray-500">
              No bookings yet.
            </li>
          </ul>
        </div>
      </div>

      <button
        class="w-full bg-red-600 text-white py-2 rounded-md hover:bg-red-700 transition"
        @click="logout"
      >
        Logout
      </button>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-10">
      <!-- Booking modal -->
      <div
        v-if="openBookingModal"
        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center"
      >
        <div class="bg-white p-6 rounded shadow-lg w-96">
          <h3 class="text-lg font-semibold mb-4">Make a Booking</h3>
          <form @submit.prevent="submitBooking">
            <input
              v-model="newBookingDetails"
              type="text"
              placeholder="Booking details"
              class="w-full mb-4 p-2 border rounded"
              required
            />
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                class="px-4 py-2 rounded border"
                @click="openBookingModal = false"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700"
                :disabled="submittingBooking"
              >
                {{ submittingBooking ? 'Submitting...' : 'Submit' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import { useRouter } from 'vue-router'

const username = ref('')
const bookings = ref([])
const loadingBookings = ref(false)
const openBookingModal = ref(false)
const newBookingDetails = ref('')
const submittingBooking = ref(false)
const router = useRouter()

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
    bookings.value = res.data
  } catch {
    bookings.value = []
  } finally {
    loadingBookings.value = false
  }
}

async function submitBooking() {
  if (newBookingDetails.value.trim() === '') return
  submittingBooking.value = true
  try {
    const res = await api.post('/api/bookings', { details: newBookingDetails.value.trim() })
    bookings.value.push(res.data)
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
