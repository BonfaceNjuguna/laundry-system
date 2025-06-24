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
      const response = await axios.post('/api/login', form);
      const token = response.data.access_token;

      // Save token
      localStorage.setItem('token', token);

      await getUser();

      status.value = 'Login successful. Redirecting...';
      setTimeout(() => router.push('/home'), 1000);
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
    try {
      await axios.post('/api/logout');
    } catch (_) {}

    localStorage.removeItem('token');
    delete axios.defaults.headers.common['Authorization'];
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
