import axios from 'axios';

const api = axios.create({
  baseURL: 'https://tichicleanersapi.tandalakenya.com',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

// Inject token into headers for every request
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
