<template>
  <div class="h-screen flex items-center justify-center bg-gray-100">
    <form @submit.prevent="handleLogin" class="bg-white p-6 rounded shadow-md w-96">
      <h2 class="text-2xl font-bold mb-4">Login</h2>
      <div class="mb-4">
        <label>Email</label>
        <input v-model="form.email" type="email" class="w-full border px-3 py-2 rounded" />
        <p v-if="errors?.email" class="text-red-500 text-sm">{{ errors.email[0] }}</p>
      </div>
      <div class="mb-4">
        <label>Password</label>
        <input v-model="form.password" type="password" class="w-full border px-3 py-2 rounded" />
        <p v-if="errors?.password" class="text-red-500 text-sm">{{ errors.password[0] }}</p>
      </div>
      <button class="bg-blue-500 text-white w-full py-2 rounded">Login</button>
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

const { login, errors } = useAuth();

const handleLogin = () => {
  login(form);
};
</script>
