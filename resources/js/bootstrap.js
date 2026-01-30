import axios from 'axios';
window.axios = axios;

// Set base URL for API calls
window.axios.defaults.baseURL = window.location.origin;
window.axios.defaults.headers.common['Accept'] = 'application/json';

// Function to get CSRF token from meta tag
window.getCsrfToken = () => {
  const token = document.head.querySelector('meta[name="csrf-token"]');
  return token ? token.content : null;
};

// Function to set auth token
window.setAuthToken = (token) => {
  if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    delete window.axios.defaults.headers.common['Authorization'];
  }
};

// Response interceptor to handle authentication errors
window.axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      const status = error.response.status;

      // 401 Unauthorized - not authenticated
      if (status === 401) {
        window.location.href = '/login';
      }

      // 419 CSRF token mismatch
      if (status === 419) {
        window.location.href = '/login';
      }
    }

    return Promise.reject(error);
  }
);

// Refresh CSRF token
window.refreshCsrfToken = async () => {
  try {
    await window.axios.get('/login', {
      headers: {
        'Cache-Control': 'no-cache',
      }
    });

    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token) {
      window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
    }
  } catch (err) {
    console.error('Failed to refresh CSRF token:', err);
    throw err;
  }
};
