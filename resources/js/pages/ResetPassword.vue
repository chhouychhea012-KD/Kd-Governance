<template>
  <v-app>
    <v-container
      fluid
      class="d-flex align-center justify-center"
      style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
    >
      <v-card width="480" class="pa-8 rounded-xl" elevation="20">
        <div class="text-center mb-8">
          <img width="90px" style="margin: 0 auto;" src="logo.png" alt="">
          <h2 class="text-h4 font-weight-bold mb-2">KD Governance</h2>
          <p class="text-subtitle-1 text-medium-emphasis">Create new password</p>
        </div>

        <v-form ref="formRef" @submit.prevent="submit">
          <v-text-field
            v-model="form.email"
            label="Email Address"
            prepend-inner-icon="mdi-email"
            variant="outlined"
            density="comfortable"
            class="mb-3"
            readonly
          />

          <v-text-field
            v-model="form.token"
            label="Verification Code"
            prepend-inner-icon="mdi-key"
            variant="outlined"
            density="comfortable"
            class="mb-3"
            :rules="[v => !!v || 'Reset token is required']"
            :error-messages="errors.token"
            @input="errors.token = null"
            required
          />

          <v-text-field
            v-model="form.password"
            label="New Password"
            prepend-inner-icon="mdi-lock-plus"
            variant="outlined"
            density="comfortable"
            class="mb-3"
            :rules="[
              v => !!v || 'Password is required',
              v => (v && v.length >= 8) || 'Password must be at least 8 characters'
            ]"
            :error-messages="errors.password"
            @input="errors.password = null"
            :type="showPassword ? 'text' : 'password'"
            :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
            @click:append-inner="showPassword = !showPassword"
            required
          />

          <v-text-field
            v-model="form.password_confirmation"
            label="Confirm New Password"
            prepend-inner-icon="mdi-lock-check"
            variant="outlined"
            density="comfortable"
            class="mb-4"
            :rules="[
              v => !!v || 'Password confirmation is required',
              v => v === form.password || 'Passwords do not match'
            ]"
            :error-messages="errors.password_confirmation"
            @input="errors.password_confirmation = null"
            :type="showConfirmPassword ? 'text' : 'password'"
            :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
            @click:append-inner="showConfirmPassword = !showConfirmPassword"
            required
          />

          <v-btn
            type="submit"
            color="white"
            size="large"
            block
            :loading="loading"
            class="mb-4 bg-primary"
          >
            Reset Password
          </v-btn>

          <div class="text-center">
            <span class="text-caption text-medium-emphasis">
              Remember your password?
              <span @click="router.visit('/login')" class="text-primary text-decoration-none" style="cursor: pointer;">Sign in</span>
            </span>
          </div>
        </v-form>
      </v-card>
    </v-container>
  </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'

const page = usePage()
const formRef = ref(null)
const form = ref({
  email: '',
  token: '',
  password: '',
  password_confirmation: '',
})

const errors = ref({
  email: null,
  token: null,
  password: null,
  password_confirmation: null,
})

const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search)
  form.value.email = urlParams.get('email') || ''
  form.value.token = urlParams.get('token') || ''
})

const submit = async () => {
  Object.keys(errors.value).forEach(key => {
    errors.value[key] = null
  })

  const { valid } = await formRef.value.validate()
  if (!valid) return

  loading.value = true

  try {
    await axios.post('/api/auth/reset-password', {
      email: form.value.email,
      token: form.value.token,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
    })

    Swal.fire({
      icon: 'success',
      title: 'Password Reset Successful',
      text: 'Your password has been reset. You can now sign in with your new password.',
      timer: 3000,
      showConfirmButton: false,
    }).then(() => {
      router.visit('/login')
    })
  } catch (error) {
    if (error.response?.status === 422) {
      const responseErrors = error.response.data.errors || {}
      Object.keys(responseErrors).forEach(key => {
        if (errors.value[key] !== undefined) {
          errors.value[key] = responseErrors[key][0]
        }
      })
      if (error.response.data.message) {
        Swal.fire({ icon: 'error', title: 'Error', text: error.response.data.message })
      }
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.message || 'Something went wrong.',
      })
    }
  } finally {
    loading.value = false
  }
}
</script>