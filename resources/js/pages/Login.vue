<template>
  <v-app>
    <v-container
      fluid
      class="d-flex align-center justify-center"
      style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
    >
      <v-card width="480" class="pa-8 rounded-xl" elevation="20">
        <!-- Logo -->
        <div class="text-center mb-8">
          <!-- <v-avatar  size="80" class="mb-4"> -->
            <!-- <v-icon size="48" color="white">mdi-shield-star</v-icon> -->
             <img width="90px" style="margin: 0 auto;" src="logo.png" alt="">
          <!-- </v-avatar> -->
          <h2 class="text-h4 font-weight-bold mb-2">KD Governance</h2>
          <p class="text-subtitle-1 text-medium-emphasis">Sign in to your account</p>
        </div>

        <!-- Form -->
        <v-form ref="formRef" @submit.prevent="submit">
          <v-text-field
            v-model="form.username"
            label="Username or Email"
            prepend-inner-icon="mdi-account"
            variant="outlined"
            density="comfortable"
            class="mb-3"
            :rules="[v => !!v || 'Username or Email is required']"
            :error-messages="errors.username"
            @input="errors.username = null"
            required
          />

          <v-text-field
            v-model="form.password"
            label="Password"
            prepend-inner-icon="mdi-lock"
            variant="outlined"
            density="comfortable"
            class="mb-2"
            :rules="[v => !!v || 'Password is required']"
            :error-messages="errors.password"
            @input="errors.password = null"
            required
            :type="showPassword ? 'text' : 'password'"
            :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
            @click:append-inner="showPassword = !showPassword"
          />

          <div class="d-flex align-center justify-space-between mb-6">
            <v-checkbox
              v-model="form.remember"
              label="Remember me"
              density="compact"
              hide-details
            />
            <!-- <span @click="router.visit('/forgot-password')" class="text-primary text-decoration-none" style="cursor: pointer;">Forgot password?</span> -->
          </div>

          <v-btn
            type="submit"
            color="white"
            size="large"
            block
            :loading="loading"
            class="mb-4 bg-primary"
          >
            Login
          </v-btn>

          <!-- <div class="text-center">
            <span class="text-caption text-medium-emphasis">
              Don't have an account? 
              <a href="#" class="text-primary text-decoration-none">Sign up</a>
            </span>
          </div> -->
        </v-form>
      </v-card>
    </v-container>
  </v-app>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthStore } from '@/stores/auth'
const auth = useAuthStore()
const showPassword = ref(false)
const formRef = ref(null)
const form = ref({
  username: '',
  password: '',
  remember: false,
})

const errors = ref({
  username: null,
  password: null,
})

const loading = ref(false)

const submit = async () => {
  errors.value.username = null
  errors.value.password = null
  
  const { valid } = await formRef.value.validate()
  if (!valid) return
  loading.value = true

  var credentials = {
    username: form.value.username,
    password: form.value.password,
    remember: form.value.remember
  }

  try {
    router.post('/login', form.value, {
      onSuccess: async () => {
        const response = await axios.post('/api/auth/login', credentials)
        const { user, token, roles, permissions } = response.data

        auth.setAuth({ user, token, roles, permissions })
        Swal.fire({
          icon: 'success',
          title: 'Login successful',
          text: 'Welcome back!',
          timer: 1500,
          showConfirmButton: false,
        })
      },
      onError: (err) => {
        // Change formErrors to errors
        if (err.username) {
          errors.value.username = err.username
        }
        if (err.password) {
          errors.value.password = err.password
        }

        Swal.fire({
          icon: 'error',
          title: 'Authentication Error',
          text: err.username || err.password || 'Please check your credentials.',
        })
      }
    })
  } catch (error) {
    if (error.response?.status === 422) {
      const responseErrors = error.response.data.errors || {}
      
      if (responseErrors.username) {
        errors.value.username = responseErrors.username[0]
      }
      
      if (responseErrors.password) {
        errors.value.password = responseErrors.password[0]
      }

      Swal.fire({
        icon: 'error',
        title: 'Authentication Error',
        text: responseErrors.username?.[0] || responseErrors.password?.[0] || 'Please check your credentials.',
      })
    } else if (error.response?.status === 401) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid Credentials',
        text: 'The username or password you entered is incorrect.',
      })
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Server Error',
        text: error.response?.data?.message || 'Something went wrong. Please try again.',
      })
    }
  } finally {
    loading.value = false
    form.value.password = ''
  }
}
</script>

<style scoped>
.text-sm {
  font-size: 0.875rem;
}
</style>
