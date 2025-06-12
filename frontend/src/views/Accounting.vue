<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :username="username"
            @open-booking="() => { selectedBooking.value = null; openBookingModal.value = true }" @logout="logout" />
        <main class="flex-1 p-4 overflow-y-auto">
            <div class="rounded-xl bg-white p-4 shadow">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Accounting</h2>
                </div>
            </div>
        </main>
    </div>
</template>


<script setup>
import Sidebar from '@/components/SideBar.vue'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'

const router = useRouter()
const username = ref('')

async function fetchUser() {
  try {
    const res = await api.get('/api/user')
    username.value = res.data.name
  } catch {
    username.value = ''
  }
}

function logout() {
  api.post('/api/logout').finally(() => router.push('/login'))
}

onMounted(() => {
  fetchUser()
})
</script>