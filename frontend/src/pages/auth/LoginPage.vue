<template>
  <div class="flex items-center justify-center h-screen bg-gray-100">
    <form @submit.prevent="handleLogin" class="bg-white p-8 rounded shadow-md w-full max-w-md">
      <h2 class="flex text-2xl font-bold mb-6 justify-center text-gray-800">Tichi Cleaners</h2>

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
        :disabled="submitting"
      />
      <input
        v-model="form.password"
        type="password"
        placeholder="Password"
        class="w-full p-3 border mb-6 rounded"
        required
        :disabled="submitting"
      />
      <button
        type="submit"
        class="w-full bg-green-500 text-white py-2 px-4 rounded hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
        :disabled="submitting"
        :style="{ cursor: submitting ? 'not-allowed' : 'pointer' }"
      >
        {{ submitting ? 'Logging in...' : 'Login' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';

const form = reactive({
  email: '',
  password: '',
});

const submitting = ref(false);

const { login, errors, status } = useAuth();

onMounted(() => {
  status.value = '';
});

const handleLogin = async () => {
  submitting.value = true;
  try {
    await login(form);
  } finally {
    // If login redirects on success, this won't run, but if it fails:
    submitting.value = false;
  }
};
</script>
