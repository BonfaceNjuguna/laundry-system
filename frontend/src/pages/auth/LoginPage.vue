<template>
  <div class="flex items-center justify-center h-screen bg-gray-100">
    <form @submit.prevent="handleLogin" class="bg-white p-8 rounded shadow-md w-full max-w-md">
      <h2 class="text-2xl font-bold mb-6">Login</h2>

      <div v-if="errors" class="mb-4 p-3 bg-red-100 text-red-700 rounded">
        {{ errors }}
      </div>

      <div v-if="status" class="mb-4 p-3 bg-green-100 text-green-700 rounded">
        {{ status }}
      </div>

      <input
        v-model="form.email"
        type="email"
        placeholder="Email"
        class="w-full p-3 border mb-4 rounded"
        required
      />
      <input
        v-model="form.password"
        type="password"
        placeholder="Password"
        class="w-full p-3 border mb-6 rounded"
        required
      />
      <button
        type="submit"
        class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700"
      >
        Login
      </button>
    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useAuth } from '@/composables/useAuth';

const form = reactive({
  email: '',
  password: '',
});

const { login, errors, status } = useAuth();

const handleLogin = () => {
  login(form);
};
</script>
