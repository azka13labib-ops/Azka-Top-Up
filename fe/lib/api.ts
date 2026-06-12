import axios from 'axios';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1';

export const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // Useful for Sanctum cookie-based session/auth if needed
});

// Response interceptor for easy error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Standardize error responses
    const message = error.response?.data?.message || 'Terjadi kesalahan sistem';
    const errors = error.response?.data?.errors || null;
    
    return Promise.reject({
      message,
      errors,
      status: error.response?.status,
      originalError: error,
    });
  }
);
