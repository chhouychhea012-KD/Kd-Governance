<template>
  <AuthenticatedLayout>
    <Head title="My Profile" />
    <v-row class="mb-4">
      <v-col cols="12" md="8">
        <h1 class="text-h4 font-weight-bold">My Profile</h1>
        <p class="text-subtitle-1 text-medium-emphasis">View and manage your account information</p>
      </v-col>
      <v-col cols="12" md="4" class="text-md-right">
        <v-btn
          prepend-icon="mdi-pencil"
          class="mr-2 bg-primary"
          @click="router.visit('/profile/update')"
        >
          Update Profile
        </v-btn>
        <v-btn
          class="bg-secondary"
          prepend-icon="mdi-lock"
          @click="router.visit('/profile/change-password')"
        >
          Change Password
        </v-btn>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="8">
        <v-card elevation="2">
          <v-card-title class="text-h6 bg-primary text-white">
            <v-icon class="mr-2">mdi-account</v-icon>
            Profile Information
          </v-card-title>
          <v-card-text class="pt-4">
            <v-row>
              <v-col cols="12" class="text-center mb-4">
                <v-avatar size="120" class="mb-3">
                  <v-img
                    v-if="user?.user?.profile_image"
                    :src="'/images/profile/'+user?.user?.profile_image"
                    alt="Profile Image"
                  ></v-img>
                  <v-icon v-else size="60" color="primary">mdi-account-circle</v-icon>
                </v-avatar>
                <div class="text-h6 font-weight-medium">{{ user?.user?.name || 'N/A' }}</div>
              </v-col>
              <v-col cols="12" md="6">
                <div class="d-flex align-center mb-3">
                  <v-icon class="mr-3 text-primary">mdi-account-outline</v-icon>
                  <div>
                    <div class="text-caption text-medium-emphasis">Full Name</div>
                    <div class="text-body-1 font-weight-medium">{{ user?.user?.name || 'N/A' }}</div>
                  </div>
                </div>
              </v-col>
              <v-col cols="12" md="6">
                <div class="d-flex align-center mb-3">
                  <v-icon class="mr-3 text-primary">mdi-email</v-icon>
                  <div>
                    <div class="text-caption text-medium-emphasis">Email Address</div>
                    <div class="text-body-1 font-weight-medium">{{ user?.user?.email || 'N/A' }}</div>
                  </div>
                </div>
              </v-col>
              <v-col cols="12" md="6">
                <div class="d-flex align-center mb-3">
                  <v-icon class="mr-3 text-primary">mdi-phone</v-icon>
                  <div>
                    <div class="text-caption text-medium-emphasis">Phone Number</div>
                    <div class="text-body-1 font-weight-medium">{{ user?.user?.phone_number || 'N/A' }}</div>
                  </div>
                </div>
              </v-col>
              <v-col cols="12" md="6">
                <div class="d-flex align-center mb-3">
                  <v-icon class="mr-3 text-primary">mdi-account-group</v-icon>
                  <div>
                    <div class="text-caption text-medium-emphasis">Team</div>
                    <div class="text-body-1 font-weight-medium">{{ user?.team?.name || 'No team assigned' }}</div>
                  </div>
                </div>
              </v-col>
              <v-col cols="12">
                <div class="d-flex align-start mb-3">
                  <v-icon class="mr-3 text-primary mt-1">mdi-shield-account</v-icon>
                  <div>
                    <div class="text-caption text-medium-emphasis mb-2">Roles</div>
                    <div v-if="user?.roles?.length" class="d-flex flex-wrap gap-2">
                      <v-chip
                        v-for="role in user.roles"
                        :key="role.id"
                        color="primary"
                        size="small"
                        variant="outlined"
                      >
                        {{ role.name }}
                      </v-chip>
                    </div>
                    <div v-else class="text-body-1 font-weight-medium">No roles assigned</div>
                  </div>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card elevation="2">
          <v-card-title class="text-h6 bg-secondary text-white">
            <v-icon class="mr-2">mdi-information</v-icon>
            Account Stats
          </v-card-title>
          <v-card-text class="pt-4">
            <div class="text-center">
              <div class="text-h4 text-primary mb-2">{{ user?.evaluations?.length || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Evaluations</div>
            </div>
            <v-divider class="my-4"></v-divider>
            <div class="text-center">
              <div class="text-h4 text-success mb-2">{{ user?.improves?.length || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Improvements</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { Link, usePage, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layout/AuthenticatedLayout.vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const user = ref(null)
const loading = ref(false)

const fetchUserProfile = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/auth/me')
    
    user.value = response.data
  } catch (error) {
    Swal.fire('Error', 'Failed to load profile information', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchUserProfile()
})
</script>
