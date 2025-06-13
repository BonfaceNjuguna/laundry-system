<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :username="username"
            @open-booking="() => { selectedBooking.value = null; openBookingModal.value = true }" @logout="logout" />

        <main class="flex-1 p-4 overflow-y-auto">
            <div class="rounded-xl bg-white p-4 shadow">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Accounting</h2>
                </div>

                <!-- Filters -->
                <FilterBar v-model:filters="filters" :services="services" :customers="customers"
                    :is-applying="isApplying" @apply="fetchSummary" />

                <div v-if="isLoading" class="text-blue-500 font-semibold mb-4">Loading...</div>
            </div>

            <!-- Summary Cards and Charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                <div class="border rounded shadow p-4">
                    <h2 class="font-bold text-lg mb-2">Income</h2>
                    <p class="text-green-600 font-bold text-2xl">KES {{ incomeTotal }}</p>
                    <canvas ref="incomePieChart" class="w-full h-64"></canvas>
                    <ul class="mt-4">
                        <li v-for="i in incomeBreakdown" :key="i.name" class="flex justify-between">
                            <span>{{ i.name }}</span>
                            <span>KES {{ i.amount.toFixed(2) }}</span>
                        </li>
                    </ul>
                </div>

                <div class="border rounded shadow p-4">
                    <h2 class="font-bold text-lg mb-2">Expenses</h2>
                    <p class="text-red-600 font-bold text-2xl">KES {{ expenseTotal }}</p>
                    <canvas ref="expensePieChart" class="w-full h-64"></canvas>
                    <ul class="mt-4">
                        <li v-for="e in expenseBreakdown" :key="e.name" class="flex justify-between">
                            <span>{{ e.name }}</span>
                            <span>KES {{ e.amount.toFixed(2) }}</span>
                        </li>
                    </ul>
                </div>

                <div class="col-span-1 md:col-span-2 border rounded shadow p-4 mt-4">
                    <h2 class="font-bold mb-2">Revenue</h2>
                    <canvas ref="summaryChart" class="w-full h-64"></canvas>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Chart from 'chart.js/auto'
import api from '@/api/axios'
import { nextTick } from 'vue'

import Sidebar from '@/components/SideBar.vue'
import FilterBar from '@/components/FilterBar.vue'

// Router + data
const router = useRouter()
const route = useRoute()

const username = ref('')
const services = ref([])
const customers = ref([])

function getDefaultMonthRange() {
    const now = new Date()
    const first = new Date(now.getFullYear(), now.getMonth(), 1)
    const last = new Date(now.getFullYear(), now.getMonth() + 1, 0)
    return {
        from: first.toISOString().slice(0, 10),
        to: last.toISOString().slice(0, 10)
    }
}

const filters = ref({
    from: route.query.from || getDefaultMonthRange().from,
    to: route.query.to || getDefaultMonthRange().to,
    service_id: route.query.service_id || '',
    customer_id: route.query.customer_id || '',
})

const isLoading = ref(false)
const isApplying = ref(false)

const incomeTotal = ref(0)
const expenseTotal = ref(0)

const incomeBreakdown = ref([])
const expenseBreakdown = ref([])

// Canvas DOM refs
const summaryChart = ref(null)
const incomePieChart = ref(null)
const expensePieChart = ref(null)

// Chart.js instances
const summaryChartInstance = ref(null)
const incomeChartInstance = ref(null)
const expenseChartInstance = ref(null)

function shallowEqual(obj1, obj2) {
    const keys1 = Object.keys(obj1)
    const keys2 = Object.keys(obj2)
    if (keys1.length !== keys2.length) return false
    for (const key of keys1) {
        if (obj1[key] != obj2[key]) return false
    }
    return true
}

async function fetchUser() {
    try {
        const res = await api.get('/api/user')
        username.value = res.data.name
    } catch {
        username.value = ''
    }
}

async function fetchServices() {
    const res = await api.get('/api/services')
    services.value = res.data
}

async function fetchCustomers() {
    const res = await api.get('/api/customers')
    customers.value = res.data
}

function logout() {
    api.post('/api/logout').finally(() => router.push('/login'))
}

async function fetchSummary() {
    isApplying.value = true
    isLoading.value = true
    try {
        const currentQuery = { ...route.query }
        const targetQuery = { ...filters.value }
        if (!shallowEqual(currentQuery, targetQuery)) {
            router.replace({ query: targetQuery })
            return
        }

        const [bookingsRes, expensesRes] = await Promise.all([
            api.get('/api/bookings', { params: filters.value }),
            api.get('/api/expenses', { params: filters.value }),
        ])

        // Income grouping
        const serviceMap = {}
        bookingsRes.data.forEach(b => {
            if (Array.isArray(b.services)) {
                b.services.forEach(s => {
                    if (!serviceMap[s.name]) serviceMap[s.name] = 0
                    serviceMap[s.name] += parseFloat(b.amount)
                })
            }
        })
        incomeBreakdown.value = Object.entries(serviceMap).map(([name, amount]) => ({ name, amount }))

        // Expense grouping
        const expenseMap = {}
        expensesRes.data.forEach(e => {
            if (!expenseMap[e.category]) expenseMap[e.category] = 0
            expenseMap[e.category] += parseFloat(e.amount)
        })
        expenseBreakdown.value = Object.entries(expenseMap).map(([name, amount]) => ({ name, amount }))

        incomeTotal.value = incomeBreakdown.value.reduce((sum, i) => sum + i.amount, 0)
        expenseTotal.value = expenseBreakdown.value.reduce((sum, e) => sum + e.amount, 0)

        await nextTick() 

        renderCharts(incomeBreakdown.value, expenseBreakdown.value, incomeTotal.value, expenseTotal.value)
    } catch (err) {
        console.error('Error fetching summary', err)
    } finally {
        isApplying.value = false
        isLoading.value = false
    }
}

function renderCharts(incomeData, expenseData, income, expense) {
    const profit = income - expense

    // Destroy old chart instances
    summaryChartInstance.value?.destroy()
    incomeChartInstance.value?.destroy()
    expenseChartInstance.value?.destroy()

    // Only render if refs are available
    if (!summaryChart.value || !incomePieChart.value || !expensePieChart.value) return

    // Summary bar chart
    const barCtx = summaryChart.value.getContext('2d')
    summaryChartInstance.value = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['Income', 'Expenses', 'Profit'],
            datasets: [{
                label: 'KES',
                data: [income, expense, profit],
                backgroundColor: ['green', 'red', 'blue']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    })

    // Income pie chart
    const incomeCtx = incomePieChart.value.getContext('2d')
    incomeChartInstance.value = new Chart(incomeCtx, {
        type: 'pie',
        data: {
            labels: incomeData.map(i => i.name),
            datasets: [{
                data: incomeData.map(i => i.amount),
                backgroundColor: ['#4ade80', '#60a5fa', '#fbbf24', '#f87171', '#a78bfa', '#34d399', '#f472b6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    })

    // Expense pie chart
    const expenseCtx = expensePieChart.value.getContext('2d')
    expenseChartInstance.value = new Chart(expenseCtx, {
        type: 'pie',
        data: {
            labels: expenseData.map(e => e.name),
            datasets: [{
                data: expenseData.map(e => e.amount),
                backgroundColor: ['#f87171', '#fbbf24', '#60a5fa', '#a78bfa', '#4ade80', '#f472b6', '#34d399']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    })
}

watch(
    () => route.query,
    (newQuery) => {
        if (!shallowEqual(newQuery, filters.value)) {
            Object.assign(filters.value, newQuery)
            fetchSummary()
        }
    }
)

onMounted(() => {
    fetchUser()
    fetchServices()
    fetchCustomers()
    fetchSummary()
})

onBeforeUnmount(() => {
    summaryChartInstance.value?.destroy()
    incomeChartInstance.value?.destroy()
    expenseChartInstance.value?.destroy()
})
</script>
