<template>
  <AuthenticatedLayout>
    <Head title="Change Password" />
    <v-row class="mb-4">
      <v-col cols="12" md="8">
        <h1 class="text-h4 font-weight-bold">Change Password</h1>
        <p class="text-subtitle-1 text-medium-emphasis">Update your account password</p>
      </v-col>
      <v-col cols="12" md="4" class="text-md-right">
        <v-btn
          variant="text"
          class="bg-black"
          @click="router.visit('/profile')"
        >
          Back to Profile
        </v-btn>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <v-card elevation="2">
          <v-card-title class="text-h6 bg-secondary text-white">
            <v-icon class="mr-2">mdi-lock</v-icon>
            Change Your Password
          </v-card-title>
          <v-card-text class="pt-4">
            <v-form ref="formRef" @submit.prevent="changePassword" lazy-validation>
              <v-text-field
                v-model="form.current_password"
                label="Current Password"
                :rules="[v => !!v || 'Current password is required']"
                required
                variant="outlined"
                prepend-inner-icon="mdi-lock-outline"
                class="mb-4"
                :type="showCurrentPassword ? 'text' : 'password'"
                  :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showCurrentPassword = !showCurrentPassword"
              ></v-text-field>

              <v-text-field
                v-model="form.password"
                label="New Password"
                :rules="[
                  v => !!v || 'New password is required',
                  v => (v && v.length >= 8) || 'Password must be at least 8 characters'
                ]"
                required
                variant="outlined"
                prepend-inner-icon="mdi-lock-plus"
                class="mb-4"
                :type="showPassword ? 'text' : 'password'"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showPassword = !showPassword"
              ></v-text-field>

              <v-text-field
                v-model="form.password_confirmation"
                label="Confirm New Password"
                :rules="[
                  v => !!v || 'Password confirmation is required',
                  v => v === form.password || 'Passwords do not match'
                ]"
                required
                variant="outlined"
                prepend-inner-icon="mdi-lock-check"
                class="mb-4"
                :type="showComfirmPassword ? 'text' : 'password'"
                :append-inner-icon="showComfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showComfirmPassword = !showComfirmPassword"
              ></v-text-field>
            </v-form>
          </v-card-text>
          <v-card-actions class="pb-4 px-4">
            <v-spacer></v-spacer>
            <v-btn variant="text" class="bg-black" @click="router.visit('/profile')">Cancel</v-btn>
            <v-btn
              color="primary"
              variant="elevated"
              @click="changePassword"
              :loading="changing"
            >
              Change Password
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card elevation="2">
          <v-card-title class="text-h6 bg-info text-white">
            <v-icon class="mr-2">mdi-information</v-icon>
            Password Requirements
          </v-card-title>
          <v-card-text class="pt-4">
            <v-alert
              type="info"
              variant="tonal"
              class="mb-4"
            >
              <div class="text-body-2">
                <strong>Password must:</strong>
                <ul class="mt-2 mb-0">
                  <li>Be at least 8 characters long</li>
                  <li>Contain a mix of letters, numbers, and symbols (recommended)</li>
                  <li>Be different from your current password</li>
                </ul>
              </div>
            </v-alert>

            <v-alert
              type="warning"
              variant="tonal"
            >
              <div class="text-body-2">
                <strong>Important:</strong> After changing your password, you will need to log in again with your new password on all devices.
              </div>
            </v-alert>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layout/AuthenticatedLayout.vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthStore } from '@/stores/auth'
const showCurrentPassword = ref(false)
const showPassword = ref(false)
const showComfirmPassword = ref(false)
const auth = useAuthStore()
const formRef = ref(null)
const changing = ref(false)

const form = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const changePassword = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  changing.value = true
  try {
    // alert(auth.user.id)
    await axios.put('/api/users/change-password/'+auth.user.id, form.value)

    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Password changed successfully',
      timer: 2000,
      showConfirmButton: false
    }).then(() => {
      // Clear form
      form.value = {
        current_password: '',
        password: '',
        password_confirmation: ''
      }
      if (formRef.value) formRef.value.reset()

      // Optionally redirect to profile or logout
      router.push('/profile')
    })
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to change password'
    const errors = error.response?.data?.errors

    let errorText = message
    if (errors) {
      errorText = Object.values(errors).flat().join('<br>')
    }

    Swal.fire({
      icon: 'error',
      title: 'Error',
      html: errorText
    })
  } finally {
    changing.value = false
  }
}
</script>
