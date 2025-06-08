import axios from '@/api/axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const user = ref(null);
const errors = ref(null);
const status = ref(null);

export function useAuth() {
  const router = useRouter();

  const getUser = async () => {
    try {
      const res = await axios.get('/api/user');
      user.value = res.data;
    } catch {
      user.value = null;
    }
  };

  const login = async (form) => {
    errors.value = null;
    status.value = null;

    try {
      // 1. First get CSRF cookie
      await axios.get('/sanctum/csrf-cookie');
      // 2. Then attempt login
      await axios.post('/api/login', form);

      await getUser();

      status.value = 'Login successful. Redirecting...';
      setTimeout(() => router.push('/dashboard'), 1000);
    } catch (err) {
      if (err.response?.status === 422) {
        const allErrors = Object.values(err.response.data.errors).flat().join(' ');
        errors.value = allErrors;
      } else if (err.response?.status === 401) {
        errors.value = err.response.data.message || 'Invalid email or password.';
      } else {
        errors.value = 'Something went wrong. Please try again.';
      }
    }
  };

  const logout = async () => {
    await axios.post('/api/logout');
    user.value = null;
    router.push('/login');
  };

  return {
    user,
    errors,
    status,
    getUser,
    login,
    logout,
  };
}
