<template>
    <aside class="w-72 bg-white flex flex-col justify-between shadow-md p-6">
        <div>
            <h2 class="text-2xl font-bold mb-4 text-center text-gray-800">Tichi Cleaners</h2>
            <p class="text-center text-gray-800" v-if="username">Welcome, {{ username }}!</p>
            <p class="text-center text-gray-800" v-else>Loading user...</p>

            <router-link
                to="/bookings"
                class="flex items-center justify-center rounded-md transition-colors mt-4 mb-2 px-2 py-2 text-gray-700 hover:bg-gray-100"
                active-class="text-green-700 bg-gray-100"
                exact-active-class="text-green-700 bg-gray-100"
            >
                <BookOpenIcon class="w-5 h-5 mr-2" /> Bookings
            </router-link>
            <router-link
                to="/customers"
                class="flex items-center justify-center rounded-md transition-colors mb-2 px-2 py-2 text-gray-700 hover:bg-gray-100"
                active-class="text-green-700 bg-gray-100"
                exact-active-class="text-green-700 bg-gray-100"
            >
                <UserGroupIcon class="w-5 h-5 mr-2" /> Customers
            </router-link>
            <router-link
                to="/services"
                class="flex items-center justify-center rounded-md transition-colors mb-6 px-2 py-2 text-gray-700 hover:bg-gray-100"
                active-class="text-green-700 bg-gray-100"
                exact-active-class="text-green-700 bg-gray-100"
            >
                <Cog6ToothIcon class="w-5 h-5 mr-2" /> Services
            </router-link>
            <router-link
                to="/dashboard"
                class="flex items-center justify-center rounded-md transition-colors mb-6 px-2 py-2 text-gray-700 hover:bg-gray-100"
                active-class="text-green-700 bg-gray-100"
                exact-active-class="text-green-700 bg-gray-100"
            >
                <ChartBarIcon class="w-5 h-5 mr-2" /> Dashboard
            </router-link>

            <button class="w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-700 transition mb-6 cursor-pointer"
                @click="$emit('open-booking')">
                + Make a Booking
            </button>
        </div>

        <button class="w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-700 transition cursor-pointer flex items-center justify-center gap-2"
            @click="$emit('logout')">
            <ArrowLongLeftIcon class="w-5 h-5" /> Logout
        </button>
    </aside>
</template>

<script setup>
import { BookOpenIcon, UserGroupIcon, Cog6ToothIcon, ChartBarIcon, ArrowLongLeftIcon } from '@heroicons/vue/24/outline'
defineProps(['username'])
defineEmits(['open-booking', 'logout'])
</script>