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
          <img width="90px" style="margin: 0 auto;" src="logo.png" alt="">
          <h2 class="text-h4 font-weight-bold mb-2">KD Governance</h2>
          <p class="text-subtitle-1 text-medium-emphasis">Reset your password</p>
        </div>

        <!-- Form -->
        <v-form ref="formRef" @submit.prevent="submit">
          <v-text-field
            v-model="form.email"
            label="Email Address"
            prepend-inner-icon="mdi-email"
            variant="outlined"
            density="comfortable"
            class="mb-4"
            :rules="[
              v => !!v || 'Email is required',
              v => /.+@.+\..+/.test(v) || 'Email must be valid'
            ]"
            :error-messages="errors.email"
            @input="errors.email = null"
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
            Send Reset Code
          </v-btn>

          <div class="text-center">
            <span class="text-caption text-medium-emphasis">
              Remember your password?
              <!-- <router-link to="/login" class="text-primary text-decoration-none">Sign in</router-link> -->
              <span @click="router.visit('/login')" class="text-primary text-decoration-none" style="cursor: pointer;">Sign in</span>
            </span>
          </div>
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

const formRef = ref(null)
const form = ref({
  email: '',
})

const errors = ref({
  email: null,
})

const loading = ref(false)

const submit = async () => {
  // Clear previous errors
  errors.value.email = null

  const { valid } = await formRef.value.validate()
  if (!valid) return

  loading.value = true

  try {
    await axios.post('/api/auth/forgot-password', {
      email: form.value.email
    })

    Swal.fire({
      icon: 'success',
      title: 'Reset Code Sent',
      text: 'Please check your email for the verification code.',
      timer: 1200,
      showConfirmButton: false,
    }).then(() => {
      // Correct Inertia syntax:
      router.visit(`/reset-password?email=${encodeURIComponent(form.value.email)}`)
    })
  } catch (error) {
    if (error.response?.status === 422) {
      const responseErrors = error.response.data.errors || {}
      if (responseErrors.email) {
        errors.value.email = responseErrors.email[0]
      }
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.message || 'Something went wrong. Please try again.',
      })
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.text-sm {
  font-size: 0.875rem;
}
</style>
