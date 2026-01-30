import axios from 'axios'
import Swal from 'sweetalert2'

/**
 * Handle API errors with user-friendly messages
 */
const handleApiError = (error, customMessage = null) => {
  console.error('API Error:', error)

  let message = customMessage || 'An error occurred. Please try again.'
  
  if (error.response) {
    const status = error.response.status
    
      // Handle specific status codes
    switch (status) {
      case 401:
        message = 'Session expired. Please login again.'
        // Redirect to login
        setTimeout(() => {
          window.location.href = '/login'
        }, 1500)
        break
      case 419:
        message = 'Your session has expired. Please refresh the page and try again.'
        break
      case 403:
        message = 'You do not have permission to perform this action.'
        break
      case 404:
        message = 'The requested resource was not found.'
        break
      case 422:
        // Validation errors
        if (error.response.data.errors) {
          const errors = Object.values(error.response.data.errors).flat()
          message = errors.join('<br>')
        } else if (error.response.data.message) {
          message = error.response.data.message
        }
        break
      case 500:
        message = 'Server error. Please contact support if this persists.'
        break
      default:
        message = error.response.data.message || message
    }
  } else if (error.request) {
    message = 'Network error. Please check your connection.'
  }

  Swal.fire({
    icon: 'error',
    title: 'Error',
    html: message,
    confirmButtonColor: '#1976d2'
  })

  return Promise.reject(error)
}

/**
 * Wrapper for GET requests with error handling
 */
export const apiGet = async (url, config = {}) => {
  try {
    const response = await axios.get(url, config)
    return response
  } catch (error) {
    throw handleApiError(error)
  }
}

/**
 * Wrapper for POST requests with error handling
 */
export const apiPost = async (url, data = {}, config = {}) => {
  try {
    const response = await axios.post(url, data, config)
    return response
  } catch (error) {
    throw handleApiError(error)
  }
}

/**
 * Wrapper for PUT requests with error handling
 */
export const apiPut = async (url, data = {}, config = {}) => {
  try {
    const response = await axios.put(url, data, config)
    return response
  } catch (error) {
    throw handleApiError(error)
  }
}

/**
 * Wrapper for DELETE requests with error handling
 */
export const apiDelete = async (url, config = {}) => {
  try {
    const response = await axios.delete(url, config)
    return response
  } catch (error) {
    throw handleApiError(error)
  }
}

/**
 * Show success message
 */
export const showSuccess = (message = 'Operation completed successfully!') => {
  Swal.fire({
    icon: 'success',
    title: 'Success',
    text: message,
    timer: 2000,
    showConfirmButton: false
  })
}

/**
 * Show confirmation dialog
 */
export const showConfirm = async (title = 'Are you sure?', text = '') => {
  const result = await Swal.fire({
    title,
    text,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, proceed!',
    cancelButtonText: 'Cancel'
  })
  
  return result.isConfirmed
}

export default {
  get: apiGet,
  post: apiPost,
  put: apiPut,
  delete: apiDelete,
  showSuccess,
  showConfirm,
  handleApiError
}
