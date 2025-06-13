<template>
    <div class="flex h-screen bg-gray-100 font-sans">
        <Sidebar :username="username" @open-booking="openBookingModal = true" @logout="logout" />

        <main class="flex-1 p-4 overflow-y-auto">
            <div class="rounded-xl bg-white p-4 shadow">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Accounting</h2>
                </div>

                <!-- Filters -->
                <FilterBar v-model:filters="filters" :services="services" :customers="customers"
                    :is-applying="isApplying" @apply="fetchSummary" />

                <div v-if="isLoading" class="text-blue-500 font-semibold mb-4 text-center py-8">Loading data and
                    charts...</div>
            </div>

            <!-- Summary Cards and Charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                <div class="rounded-xl shadow p-4 bg-white">
                    <h2 class="font-bold text-lg mb-2 text-gray-700">Income</h2>
                    <p class="text-green-600 font-bold text-2xl">KES {{ incomeTotal.toFixed(2) }}</p>
                    <div class="relative w-full h-64">
                        <canvas ref="incomePieChart" class="w-full h-full"></canvas>
                    </div>
                    <ul class="mt-4 space-y-1 text-gray-600">
                        <li v-for="i in incomeBreakdown" :key="i.name" class="flex justify-between border-b border-gray-200 pb-1">
                            <span>{{ i.name }}</span>
                            <span>KES {{ i.amount.toFixed(2) }}</span>
                        </li>
                        <li v-if="incomeBreakdown.length === 0" class="text-center text-gray-500 italic">No income data
                            available.</li>
                    </ul>
                </div>

                <div class="rounded-xl shadow p-4 bg-white">
                    <h2 class="font-bold text-lg mb-2 text-gray-700">Expenses</h2>
                    <p class="text-red-600 font-bold text-2xl">KES {{ expenseTotal.toFixed(2) }}</p>
                    <div class="relative w-full h-64">
                        <canvas ref="expensePieChart" class="w-full h-full"></canvas>
                    </div>
                    <ul class="mt-4 space-y-1 text-gray-600">
                        <li v-for="e in expenseBreakdown" :key="e.name"
                            class="flex justify-between border-b border-gray-200 pb-1">
                            <span>{{ e.name }}</span>
                            <span>KES {{ e.amount.toFixed(2) }}</span>
                        </li>
                        <li v-if="expenseBreakdown.length === 0" class="text-center text-gray-500 italic">No expense
                            data available.</li>
                    </ul>
                </div>

                <div class="col-span-1 md:col-span-2 rounded-xl shadow p-4 mt-4 bg-white">
                    <h2 class="font-bold text-lg mb-2 text-gray-700">Revenue Summary</h2>
                    <div class="relative w-full h-64">
                        <canvas ref="summaryChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Chart from 'chart.js/auto'
import api from '@/api/axios' // Assuming this is your axios instance

import Sidebar from '@/components/SideBar.vue' // Assuming correct path
import FilterBar from '@/components/FilterBar.vue' // Assuming correct path

// Router + data
const router = useRouter()
const route = useRoute()

// Reactive state for user info, services, and customers
const username = ref('')
const services = ref([])
const customers = ref([])

/**
 * Generates the default month range for filters (current month).
 * @returns {object} An object containing 'from' and 'to' date strings in YYYY-MM-DD format.
 */
function getDefaultMonthRange() {
    const now = new Date()
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0) // Last day of current month
    return {
        from: firstDay.toISOString().slice(0, 10),
        to: lastDay.toISOString().slice(0, 10)
    }
}

// Filters state, initialized from route query or default month range
const filters = ref({
    from: route.query.from || getDefaultMonthRange().from,
    to: route.query.to || getDefaultMonthRange().to,
    service_id: route.query.service_id || '',
    customer_id: route.query.customer_id || '',
})

// Loading states
const isLoading = ref(false) // For overall data loading
const isApplying = ref(false) // For filter application state

// Summary totals
const incomeTotal = ref(0)
const expenseTotal = ref(0)

// Breakdown data for charts and lists
const incomeBreakdown = ref([])
const expenseBreakdown = ref([])

// Canvas DOM refs for Chart.js
const summaryChart = ref(null)
const incomePieChart = ref(null)
const expensePieChart = ref(null)

// Chart.js instances, stored reactively
const summaryChartInstance = ref(null)
const incomeChartInstance = ref(null)
const expenseChartInstance = ref(null)

/**
 * Performs a shallow comparison between two objects.
 * Useful for checking if query parameters have changed.
 * @param {object} obj1 - First object to compare.
 * @param {object} obj2 - Second object to compare.
 * @returns {boolean} True if objects have the same keys and corresponding values, false otherwise.
 */
function shallowEqual(obj1, obj2) {
    const keys1 = Object.keys(obj1)
    const keys2 = Object.keys(obj2)
    if (keys1.length !== keys2.length) return false
    for (const key of keys1) {
        if (obj1[key] != obj2[key]) return false // Loose comparison for string/number values
    }
    return true
}

/**
 * Fetches the current authenticated user's name.
 */
async function fetchUser() {
    try {
        const res = await api.get('/api/user')
        username.value = res.data.name
    } catch (err) {
        console.error('Error fetching user:', err)
        username.value = '' // Reset username on error
    }
}

/**
 * Fetches the list of available services.
 */
async function fetchServices() {
    try {
        const res = await api.get('/api/services')
        services.value = res.data
    } catch (err) {
        console.error('Error fetching services:', err)
        services.value = [] // Reset services on error
    }
}

/**
 * Fetches the list of available customers.
 */
async function fetchCustomers() {
    try {
        const res = await api.get('/api/customers')
        customers.value = res.data
    } catch (err) {
        console.error('Error fetching customers:', err)
        customers.value = [] // Reset customers on error
    }
}

/**
 * Logs out the user and redirects to the login page.
 */
function logout() {
    api.post('/api/logout').finally(() => router.push('/login'))
}

/**
 * Fetches the accounting summary data (bookings and expenses) based on current filters,
 * processes it, and then renders the charts.
 */
async function fetchSummary() {
    isApplying.value = true
    isLoading.value = true
    try {
        // Fetch bookings and expenses in parallel for efficiency
        const [bookingsRes, expensesRes] = await Promise.all([
            api.get('/api/bookings', { params: filters.value }),
            api.get('/api/expenses', { params: filters.value }),
        ])

        // --- Process Income Data ---
        const serviceMap = {}
        bookingsRes.data.forEach(b => {
            // Ensure b.services is an array before iterating
            if (Array.isArray(b.services)) {
                b.services.forEach(s => {
                    if (!serviceMap[s.name]) serviceMap[s.name] = 0
                    serviceMap[s.name] += parseFloat(b.amount) // Ensure amount is parsed as float
                })
            }
        })
        // Convert map to array for reactivity and display
        incomeBreakdown.value = Object.entries(serviceMap).map(([name, amount]) => ({ name, amount }))

        // --- Process Expense Data ---
        const expenseMap = {}
        expensesRes.data.forEach(e => {
            if (!expenseMap[e.category]) expenseMap[e.category] = 0
            expenseMap[e.category] += parseFloat(e.amount) // Ensure amount is parsed as float
        })
        // Convert map to array for reactivity and display
        expenseBreakdown.value = Object.entries(expenseMap).map(([name, amount]) => ({ name, amount }))

        // Calculate totals
        incomeTotal.value = incomeBreakdown.value.reduce((sum, i) => sum + i.amount, 0)
        expenseTotal.value = expenseBreakdown.value.reduce((sum, e) => sum + e.amount, 0)

        // Render (or re-render) charts with the new data
        renderCharts(incomeBreakdown.value, expenseBreakdown.value, incomeTotal.value, expenseTotal.value)

    } catch (err) {
        console.error('Error fetching summary:', err)
        // Optionally reset data on error
        incomeTotal.value = 0;
        expenseTotal.value = 0;
        incomeBreakdown.value = [];
        expenseBreakdown.value = [];
        renderCharts([], [], 0, 0); // Render empty charts on error
    } finally {
        isApplying.value = false
        isLoading.value = false
    }
}

/**
 * Renders or updates the Chart.js instances.
 * Destroys existing instances before creating new ones to prevent memory leaks.
 * @param {Array} incomeData - Data for income pie chart.
 * @param {Array} expenseData - Data for expense pie chart.
 * @param {number} income - Total income for bar chart.
 * @param {number} expense - Total expense for bar chart.
 */
function renderCharts(incomeData, expenseData, income, expense) {
    const profit = income - expense

    // --- Destroy old chart instances and explicitly nullify their refs ---
    if (summaryChartInstance.value) {
        summaryChartInstance.value.destroy()
        summaryChartInstance.value = null // Crucial for releasing references
    }
    if (incomeChartInstance.value) {
        incomeChartInstance.value.destroy()
        incomeChartInstance.value = null // Crucial for releasing references
    }
    if (expenseChartInstance.value) {
        expenseChartInstance.value.destroy()
        expenseChartInstance.value = null // Crucial for releasing references
    }

    // --- Only render if canvas DOM refs are available ---
    // This guard prevents errors if the component hasn't fully mounted or elements are removed.
    if (!summaryChart.value || !incomePieChart.value || !expensePieChart.value) {
        console.warn('One or more canvas elements not found, skipping chart rendering.')
        return
    }

    // --- Summary Bar Chart (Income, Expenses, Profit) ---
    const barCtx = summaryChart.value.getContext('2d')
    summaryChartInstance.value = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['Income', 'Expenses', 'Profit'],
            datasets: [{
                label: 'KES',
                data: [income, expense, profit],
                backgroundColor: [
                    'rgba(76, 175, 80, 0.8)', // Green for Income
                    'rgba(244, 67, 54, 0.8)', // Red for Expenses
                    'rgba(33, 150, 243, 0.8)'  // Blue for Profit
                ],
                borderColor: [
                    'rgba(76, 175, 80, 1)',
                    'rgba(244, 67, 54, 1)',
                    'rgba(33, 150, 243, 1)'
                ],
                borderWidth: 1,
                borderRadius: 4, // Rounded corners for bars
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allow canvas to resize based on container
            plugins: {
                legend: { display: false }, // Hide legend as labels are self-explanatory
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `${context.dataset.label}: KES ${context.parsed.y.toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true, // Start Y-axis from zero
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    })

    // --- Income Pie Chart (Breakdown by Service) ---
    const incomeCtx = incomePieChart.value.getContext('2d')
    incomeChartInstance.value = new Chart(incomeCtx, {
        type: 'pie',
        data: {
            labels: incomeData.map(i => i.name),
            datasets: [{
                data: incomeData.map(i => i.amount),
                backgroundColor: [
                    '#4ade80', '#60a5fa', '#fbbf24', '#f87171', '#a78bfa', // Tailwind greens, blues, yellows, reds, purples
                    '#34d399', '#f472b6', '#818cf8', '#facc15', '#6ee7b7' // More diverse palette
                ],
                hoverOffset: 4, // Slight offset on hover
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right', // Place legend on the right
                    labels: {
                        boxWidth: 20,
                        padding: 10,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let sum = 0;
                            let dataArr = context.dataset.data;
                            dataArr.map(data => { sum += data; });
                            let percentage = (context.parsed / sum * 100).toFixed(2) + '%';
                            return `${context.label}: KES ${context.parsed.toFixed(2)} (${percentage})`;
                        }
                    }
                }
            }
        }
    })

    // --- Expense Pie Chart (Breakdown by Category) ---
    const expenseCtx = expensePieChart.value.getContext('2d')
    expenseChartInstance.value = new Chart(expenseCtx, {
        type: 'pie',
        data: {
            labels: expenseData.map(e => e.name),
            datasets: [{
                data: expenseData.map(e => e.amount),
                backgroundColor: [
                    '#f87171', '#fbbf24', '#60a5fa', '#a78bfa', '#4ade80', // Tailwind reds, yellows, blues, purples, greens
                    '#f472b6', '#34d399', '#ef4444', '#eab308', '#22c55e' // More diverse palette
                ],
                hoverOffset: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 20,
                        padding: 10,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let sum = 0;
                            let dataArr = context.dataset.data;
                            dataArr.map(data => { sum += data; });
                            let percentage = (context.parsed / sum * 100).toFixed(2) + '%';
                            return `${context.label}: KES ${context.parsed.toFixed(2)} (${percentage})`;
                        }
                    }
                }
            }
        }
    })
}

// Watch `filters` deeply. Any change in the filters object will trigger this.
watch(
    filters,
    (newFilters) => {
        // Update the route query parameters to reflect the current filters
        const currentRouteQuery = { ...route.query };
        if (!shallowEqual(newFilters, currentRouteQuery)) {
            router.replace({ query: newFilters }).catch(err => {
                // Catch navigation errors, e.g., redundant navigation
                if (err.name !== 'NavigationDuplicated') {
                    console.error("Error navigating with filters:", err);
                }
            });
        }
        // Always fetch summary when filters change (filters is the source of truth)
        fetchSummary();
    },
    { deep: true } // Important: deep watch to detect changes to nested properties of filters
)

// Lifecycle hook: actions to perform when the component is mounted to the DOM
onMounted(() => {
    // Fetch initial static data
    fetchUser()
    fetchServices()
    fetchCustomers()

    // Perform the initial fetch and render of charts based on the filters
    // (which were initialized from route.query or defaults)
    fetchSummary()
})

// Lifecycle hook: actions to perform just before the component is unmounted
onBeforeUnmount(() => {
    // Destroy all chart instances to free up memory when the component is removed
    summaryChartInstance.value?.destroy()
    incomeChartInstance.value?.destroy()
    expenseChartInstance.value?.destroy()
})
</script>
