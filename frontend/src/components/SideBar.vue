<template>
    <aside class="w-72 bg-white flex flex-col justify-between shadow-md p-6">
        <div>
            <h2 class="text-2xl font-bold mb-4 text-center">Laundry Matt</h2>
            <p class="text-center" v-if="username">Welcome, {{ username }}!</p>
            <p class="text-center text-gray-500" v-else>Loading user...</p>

            <router-link to="/bookings"
                class="block text-center text-green-600 hover:underline mt-4 mb-2">Bookings</router-link>
            <router-link to="/customers"
                class="block text-center text-blue-600 hover:underline mb-2">Customers</router-link>
            <router-link to="/services"
                class="block text-center text-blue-600 hover:underline mb-6">Services</router-link>
            <router-link to="/dashboard"
                class="block text-center text-blue-600 hover:underline mb-6">Dashboard</router-link>

            <button class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition mb-6"
                @click="$emit('open-booking')">
                Make a Booking
            </button>
        </div>

        <button class="w-full bg-red-600 text-white py-2 rounded-md hover:bg-red-700 transition"
            @click="$emit('logout')">
            Logout
        </button>
    </aside>
</template>

<script setup>
defineProps(['username'])
defineEmits(['open-booking', 'logout'])
</script>
