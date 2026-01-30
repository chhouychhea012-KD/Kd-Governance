import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || null,
    user: null,
    roles: [],
    permissions: [],
    isInitialized: false,
  }),

  actions: {
    async login(credentials) {
      // alert(0)
      try {
        // Get CSRF cookie first (required for Sanctum SPA auth)
        // await axios.get('/sanctum/csrf-cookie')
        // alert(credentials)
        const response = await axios.post('/api/auth/login', credentials)
        const { user, token, roles, permissions } = response.data

        this.setAuth({ user, token, roles, permissions })
        return { success: true }
      } catch (error) {
        console.error('Login error:', error)
        return {
          success: false,
          message: error.response?.data?.message || 'Login failed',
          errors: error.response?.data?.errors || {}
        }
      }
    },

    setAuth(data) {
      this.token = data.token || '',
      this.user = data.user
      
      // Set roles and permissions from response or user object
      this.roles = data.roles || data.user?.roles || []
      this.permissions = data.permissions || data.user?.permissions || []

      // Store token in localStorage for persistence
      if (data.token) {
        localStorage.setItem('auth_token', data.token)
        window.setAuthToken(data.token)
      }

      this.isInitialized = true
    },

    // Initialize from stored token
    async initializeFromToken() {
      if (this.isInitialized) return

      const token = localStorage.getItem('auth_token')
      if (token) {
        this.token = token
        window.setAuthToken(token)

        try {
          // Fetch current user info
          const response = await axios.get('/api/auth/me')
          this.user = response.data.user
          this.roles = response.data.roles || []
          this.permissions = response.data.permissions || []
          this.isInitialized = true
        } catch (error) {
          console.error('Failed to initialize from token:', error)
          // Token might be expired, clear it
          this.clearAuth()
        }
      } else {
        this.isInitialized = true
      }
    },

    setPermissions(permissions) {
      this.permissions = permissions || []
    },

    setRoles(roles) {
      this.roles = roles || []
    },

    setUser(user) {
      this.user = user
    },

    async logout() {
      try {
        if (this.token) {
          await axios.post('/api/auth/logout')
        }
      } catch (error) {
        console.error('Logout API error:', error)
        // Continue with local logout even if API call fails
      }

      this.clearAuth()
    },

    clearAuth() {
      this.token = null
      this.user = null
      this.roles = []
      this.permissions = []
      this.isInitialized = false

      // Clear localStorage
      localStorage.removeItem('auth_token')
      window.setAuthToken(null)
    },

    hasRole(role) {
      if (!this.roles || !Array.isArray(this.roles)) return false
      return this.roles.some(r => r.slug === role || r.name === role)
    },

    can(permission) {
      // Check if user is super-admin (has all permissions)
      
      if (this.isSuperAdmin) return true

      if (!this.permissions || !Array.isArray(this.permissions)) return false
      return this.permissions.some(perm => perm.slug === permission || perm.name === permission)
    },

    hasAnyRole(roles) {
      if (!Array.isArray(roles)) roles = [roles]
      return roles.some(role => this.hasRole(role))
    },

    hasAllRoles(roles) {
      if (!Array.isArray(roles)) roles = [roles]
      return roles.every(role => this.hasRole(role))
    },

    canAny(permissions) {
      if (!Array.isArray(permissions)) permissions = [permissions]
      return permissions.some(permission => this.can(permission))
    },

    canAll(permissions) {
      if (!Array.isArray(permissions)) permissions = [permissions]
      return permissions.every(permission => this.can(permission))
    },
  },

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    isSuperAdmin: (state) => {
      if (!state.roles || !Array.isArray(state.roles)) return false
      return state.roles.some(role => role.slug === 'super-admin' || role.name === 'Super Admin')
    },
    userRoles: (state) => state.roles.map(r => r.slug || r.name),
    userPermissions: (state) => state.permissions.map(p => p.slug || p.name),
  },
})
