import axios from '@/api/axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const user = ref(null);
const errors = ref(null);

export function useAuth() {
  const router = useRouter();

  const getUser = async () => {
    try {
      const res = await axios.get('/api/user');
      user.value = res.data;
    } catch (err) {
      user.value = null;
    }
  };

  const login = async (form) => {
    errors.value = null;
    try {
      await axios.get('/sanctum/csrf-cookie'); // Important!
      await axios.post('/api/login', form);
      await getUser();
      router.push('/dashboard');
    } catch (err) {
      if (err.response.status === 422) {
        errors.value = err.response.data.errors;
      }
    }
  };

  const logout = async () => {
    await axios.post('/logout');
    user.value = null;
    router.push('/login');
  };

  return {
    user,
    errors,
    getUser,
    login,
    logout,
  };
}
