<template>
  <AuthenticatedLayout>
    <Head title="Update Profile" />
    
    <v-container class="py-10">
      <v-row justify="center">
        <v-col cols="12" md="8" lg="6">
          <v-card elevation="10" class="rounded-xl overflow-hidden">
            <v-sheet height="120" class="bg-gradient-header d-flex align-end justify-center">
              <div class="mb-n12 position-relative">
                <v-avatar size="140" class="elevation-6 border-white shadow-lg" color="grey-lighten-4">
                  <v-img 
                    v-if="previewUrl || currentImageUrl" 
                    :src="previewUrl || currentImageUrl" 
                    cover
                  >
                    <template v-slot:placeholder>
                      <v-row class="fill-height ma-0" align="center" justify="center">
                        <v-progress-circular indeterminate color="primary"></v-progress-circular>
                      </v-row>
                    </template>
                  </v-img>
                  <v-icon v-else size="80" color="grey-lighten-1">mdi-account</v-icon>
                  
                  <v-btn
                    icon="mdi-camera"
                    color="primary"
                    size="small"
                    class="position-absolute shadow-sm"
                    style="bottom: 5px; right: 5px; border: 2px solid white"
                    @click="$refs.fileInput.click()"
                  ></v-btn>
                </v-avatar>
                <input
                  ref="fileInput"
                  type="file"
                  hidden
                  accept="image/*"
                  @change="onFileSelected"
                />
              </div>
            </v-sheet>

            <v-card-text class="mt-15 px-8">
              <div class="text-center mb-8">
                <h1 class="text-h5 font-weight-bold text-grey-darken-3">{{ form.name || 'Account Settings' }}</h1>
                <p class="text-body-2 text-medium-emphasis">Update your personal information and profile picture</p>
              </div>

              <v-form ref="formRef" @submit.prevent="updateProfile">
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.name"
                      label="Full Name"
                      variant="outlined"
                      prepend-inner-icon="mdi-account-outline"
                      rounded="lg"
                      :rules="[v => !!v || 'Name is required']"
                    ></v-text-field>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.email"
                      label="Email Address"
                      variant="outlined"
                      prepend-inner-icon="mdi-email-outline"
                      rounded="lg"
                      :rules="[v => !!v || 'Email is required', v => /.+@.+\..+/.test(v) || 'Invalid Email']"
                    ></v-text-field>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.phone_number"
                      label="Phone Number"
                      variant="outlined"
                      prepend-inner-icon="mdi-phone-outline"
                      rounded="lg"
                      :rules="[v => !!v || 'Phone number is required']"
                    ></v-text-field>
                  </v-col>

                  <!-- <v-col cols="12">
                    <v-select
                      v-model="form.team_id"
                      :items="teams"
                      item-title="name"
                      item-value="id"
                      label="Select Team"
                      variant="outlined"
                      prepend-inner-icon="mdi-account-group-outline"
                      rounded="lg"
                      clearable
                    ></v-select>
                  </v-col> -->
                </v-row>
              </v-form>
            </v-card-text>

            <v-divider class="mx-8"></v-divider>

            <v-card-actions class="pa-8">
              <v-btn 
                variant="text" 
                class="bg-black"
                @click="router.visit('/profile')"
              >
                Cancel
              </v-btn>
              <v-spacer></v-spacer>
              <v-btn
                color="primary"
                variant="elevated"
                size="large"
                rounded="pill"
                class="px-10"
                :loading="saving"
                @click="updateProfile"
              >
                Save Changes
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layout/AuthenticatedLayout.vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const formRef = ref(null)
const fileInput = ref(null)
const saving = ref(false)
const teams = ref([])
const profileImageFile = ref(null)
const currentImageUrl = ref(null)
const previewUrl = ref(null)

const form = ref({
  name: '',
  email: '',
  phone_number: '',
  team_id: null
})

const fetchUserProfile = async () => {
  try {
    const response = await axios.get('/api/auth/me')
    const user = response.data.user
    form.value = {
      name: user.name,
      email: user.email,
      phone_number: user.phone_number,
      team_id: user.team_id
    }
    if(response.data.user.profile_image){
      currentImageUrl.value = '/images/profile/'+response.data.user.profile_image
    }else{
      currentImageUrl.value = response.data.user.profile_image
    }
    
  } catch (error) {
    console.error('Failed to load profile')
  }
}

const fetchTeams = async () => {
  try {
    const response = await axios.get('/api/teams/parent')
    teams.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching teams')
  }
}

const onFileSelected = (e) => {
  const file = e.target.files[0]
  if (!file) return

  if (file.size > 5 * 1024 * 1024) {
    Swal.fire('Error', 'Image must be smaller than 5MB', 'error')
    return
  }

  profileImageFile.value = file
  previewUrl.value = URL.createObjectURL(file)
}

const updateProfile = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  saving.value = true
  try {
    const userResponse = await axios.get('/api/auth/me')
    const userId = userResponse.data.user.id

    const formData = new FormData()
    // Essential for Laravel to handle Multipart on PUT
    formData.append('_method', 'PUT') 
    
    formData.append('name', form.value.name)
    formData.append('email', form.value.email)
    formData.append('phone_number', form.value.phone_number)
    
    if (form.value.team_id) {
      formData.append('team_id', form.value.team_id)
    }

    if (profileImageFile.value) {
      formData.append('profile_image', profileImageFile.value)
    }

    // Submit as POST because of _method spoofing
    await axios.post(`/api/users/profile/${userId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    Swal.fire({
      icon: 'success',
      title: 'Profile Updated',
      text: 'Your account has been successfully updated.',
      timer: 1500,
      showConfirmButton: false
    }).then(() => {
      router.visit('/profile')
    })
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Error updating profile'
    Swal.fire('Update Failed', errorMsg, 'error')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchUserProfile()
  fetchTeams()
})
</script>

<style scoped>
.bg-gradient-header {
  background: linear-gradient(135deg, #1867C0 0%, #3a1c71 100%);
}
.border-white {
  border: 5px solid white !important;
}
.shadow-lg {
  box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
.shadow-sm {
  box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
}
</style>