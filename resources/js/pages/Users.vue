<template>
  <AuthenticatedLayout>
    <Head title="Users" />

    <v-row class="mb-4">
      <v-col cols="12" md="8">
        <h1 class="text-h4 font-weight-bold">Users</h1>
        <p class="text-subtitle-1 text-medium-emphasis">
          Manage user accounts and permissions
        </p>
      </v-col>
      <v-col cols="12" md="4" class="text-md-right">
        <v-btn
          v-if="$page.props.permissions?.some(p => p.slug === 'create-user')"
          class="bg-primary"
          prepend-icon="mdi-plus"
          @click="openCreateModal"
        >
          Create User
        </v-btn>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-card elevation="2">
          <v-card-title>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="search"
                  prepend-inner-icon="mdi-magnify"
                  label="Search users"
                  single-line
                  hide-details
                  clearable
                  variant="outlined"
                  density="compact"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-title>
          <v-card-text>
            <v-data-table
              :headers="headers"
              :items="Array.isArray(users) ? users : []"
              :search="search"
              :loading="loading"
              class="elevation-0"
            >
              <template v-slot:item.team="{ item }">
                <v-chip v-if="item.team" color="primary" size="small">
                  {{ item.team.name }}
                </v-chip>
                <span v-else class="text-medium-emphasis">N/A</span>
              </template>

              <template v-slot:item.sub_team="{ item }">
                <v-chip v-if="item.sub_team" color="secondary" size="small">
                  {{ item.sub_team.name }}
                </v-chip>
                <span v-else class="text-medium-emphasis">-</span>
              </template>

              <template v-slot:item.actions="{ item }">
                <v-btn
                  v-if="$page.props.permissions?.some(p => p.slug === 'update-user')"
                  icon size="small" color="primary" variant="text"
                  @click="editUser(item)"
                >
                  <v-icon>mdi-pencil</v-icon>
                </v-btn>

                <v-btn
                  v-if="$page.props.permissions?.some(p => p.slug === 'delete-user')"
                  icon size="small" color="error" variant="text"
                  @click="confirmDelete(item)"
                >
                  <v-icon>mdi-delete</v-icon>
                </v-btn>

                <v-btn
                  v-if="$page.props.permissions?.some(p => p.slug === 'reset-password')"
                  icon size="small" color="secondary" variant="text"
                  @click="openResetPasswordModal(item)"
                >
                  <v-icon>mdi-lock-reset</v-icon>
                </v-btn>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Create/Edit User Dialog -->
    <v-dialog v-model="dialog" max-width="700px" persistent>
      <v-card>
        <v-card-title class="text-h5 px-4 pt-4">
          {{ isEditing ? 'Edit User' : 'Create User' }}
        </v-card-title>
        <v-card-text>
          <v-form ref="formRef" @submit.prevent="saveUser" lazy-validation>
            <v-row>
              <v-col cols="12" md="6">
                <v-select
                  v-model="form.team_id"
                  :items="teams"
                  item-title="name"
                  item-value="id"
                  label="Team"
                  variant="outlined"
                  prepend-inner-icon="mdi-account-group"
                  clearable
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="form.sub_team_id"
                  :items="availableSubTeams"
                  item-title="name"
                  item-value="id"
                  label="Sub Team"
                  variant="outlined"
                  prepend-inner-icon="mdi-account-multiple"
                  clearable
                  hint="Select a sub-team under the chosen team"
                  persistent-hint
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="form.role_id"
                  :items="filteredRoles"
                  item-title="name"
                  item-value="id"
                  label="Role"
                  :rules="[v => !!v || 'Role is required']"
                  required
                  variant="outlined"
                  prepend-inner-icon="mdi-shield-account"
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.name"
                  label="Full Name"
                  :rules="[v => !!v || 'Name is required']"
                  required
                  variant="outlined"
                  prepend-inner-icon="mdi-account"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.email"
                  label="Email"
                  type="email"
                  :rules="[
                    v => !!v || 'Email is required',
                    v => /.+@.+\..+/.test(v) || 'Email must be valid'
                  ]"
                  required
                  variant="outlined"
                  prepend-inner-icon="mdi-email"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.phone_number"
                  label="Phone Number"
                  type="tel"
                  :rules="[v => !!v || 'Phone number is required']"
                  required
                  variant="outlined"
                  prepend-inner-icon="mdi-phone"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.password"
                  :label="isEditing ? 'Password (leave blank to keep current)' : 'Password'"
                  :rules="isEditing ? [] : [
                    v => !!v || 'Password is required',
                    v => (v && v.length >= 8) || 'Password must be at least 8 characters'
                  ]"
                  :class="{'d-none':isEditing}"
                  :required="!isEditing"
                  variant="outlined"
                  :type="showPassword ? 'text' : 'password'"
                  :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showPassword = !showPassword"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="pb-4 px-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" class="bg-black" @click="closeDialog">Cancel</v-btn>
          <v-btn color="primary" variant="elevated" @click="saveUser" :loading="saving">
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Reset Password Dialog -->
    <v-dialog v-model="resetDialog" max-width="500px" persistent>
      <v-card>
        <v-card-title class="text-h5 px-4 pt-4">
          Reset Password for {{ resetUser?.name || '' }}
        </v-card-title>
        <v-card-text>
          <v-form ref="resetFormRef" @submit.prevent="resetPassword" lazy-validation>
            <v-text-field
              v-model="newPassword"
              label="New Password"
              variant="outlined"
              :rules="[v => !!v || 'Password is required', v => (v && v.length >= 8) || 'Password must be at least 8 characters']"
              required
              prepend-inner-icon="mdi-lock"
              :type="showResetPassword ? 'text' : 'password'"
                :append-inner-icon="showResetPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showResetPassword = !showResetPassword"
            ></v-text-field>
          </v-form>
        </v-card-text>
        <v-card-actions class="pb-4 px-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" class="bg-black" @click="closeResetDialog">Cancel</v-btn>
          <v-btn color="secondary" variant="elevated" @click="resetPassword" :loading="savingReset">
            Reset
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layout/AuthenticatedLayout.vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const users = ref([])
const teams = ref([])
const roles = ref([])
const allTeams = ref([])
const search = ref('')
const dialog = ref(false)
const resetDialog = ref(false)
const loading = ref(false)
const saving = ref(false)
const savingReset = ref(false)
const isEditing = ref(false)
const showPassword = ref(false)
const showResetPassword = ref(false)
const formRef = ref(null)
const resetFormRef = ref(null)
const form = ref({
  id: null,
  name: '',
  email: '',
  phone_number: '',
  password: '',
  team_id: null,
  role_id: null,
  sub_team_id: null
})
const resetUser = ref(null)
const newPassword = ref('')

// Computed property to get sub-teams (teams with parent_id) that belong to the selected team
const availableSubTeams = computed(() => {
  if (!form.value.team_id || !allTeams.value.length) return []
  return allTeams.value.filter(team => team.parent_id === form.value.team_id)
})

// Watch for team changes and fetch sub-teams
watch(() => form.value.team_id, async (newTeamId, oldTeamId) => {
  if (newTeamId !== oldTeamId) {
    form.value.sub_team_id = null
    await fetchSubTeams(newTeamId)
  }
})

const headers = ref([
  { title: 'ID', key: 'id', sortable: true },
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Team', key: 'team', sortable: false },
  { title: 'Sub Team', key: 'sub_team', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
])

const filteredRoles = computed(() => {
  if (!auth.roles || auth.roles.length === 0) return []
  const currentUserMinLevel = Math.min(...auth.roles.map(r => r.level))
  return roles.value.filter(role => role.level >= currentUserMinLevel)
})

const fetchUsers = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/users')
    users.value = response.data.data || response.data
  } catch (error) {
    Swal.fire('Error', 'Failed to fetch users', 'error')
  } finally {
    loading.value = false
  }
}

const fetchTeams = async () => {
  try {
    const response = await axios.get('/api/teams/parent')
    teams.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching teams:', error)
  }
}

const fetchSubTeams = async (parentId) => {
  if (!parentId) {
    allTeams.value = []
    return
  }
  try {
    const response = await axios.get(`/api/teams/${parentId}/sub-teams`)
    allTeams.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching sub teams:', error)
    allTeams.value = []
  }
}

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/roles')
    roles.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching roles:', error)
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = { 
    id: null, 
    name: '', 
    email: '', 
    phone_number: '', 
    password: '', 
    team_id: null, 
    role_id: null,
    sub_team_id: null
  }
  dialog.value = true
}

const editUser = async (user) => {
  isEditing.value = true
  form.value = {
    ...user,
    password: '',
    team_id: user.team?.id || user.team_id || null,
    role_id: user.roles?.[0]?.id || null,
    sub_team_id: user.sub_team?.id || user.sub_team_id || null
  }
  
  // Fetch sub-teams if user has a team assigned
  if (form.value.team_id) {
    await fetchSubTeams(form.value.team_id)
    // After fetching subTeams, ensure the correct sub_team is selected
    if (user.sub_team?.id) {
      form.value.sub_team_id = user.sub_team.id
    } else if (user.sub_team_id) {
      form.value.sub_team_id = user.sub_team_id
    }
  }
  
  dialog.value = true
}

const saveUser = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  saving.value = true
  try {
    const payload = { ...form.value }
    if (isEditing.value && !payload.password) delete payload.password

    if (isEditing.value) {
      await axios.put(`/api/users/${form.value.id}`, payload)
    } else {
      await axios.post('/api/users', payload)
    }

    Swal.fire({ icon: 'success', title: 'Success', text: `User ${isEditing.value ? 'updated' : 'created'} successfully`, timer: 2000, showConfirmButton: false })
    await fetchUsers()
    closeDialog()
  } catch (error) {
    const message = error.response?.data?.message || 'Validation Error'
    const errors = error.response?.data?.errors
    let errorText = message
    if (errors) errorText = Object.values(errors).flat().join('<br>')
    Swal.fire({ icon: 'error', title: 'Error', html: errorText })
  } finally {
    saving.value = false
  }
}

const confirmDelete = (item) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `You are about to delete ${item.name}`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`/api/users/${item.id}`)
        Swal.fire('Deleted!', 'User has been deleted.', 'success')
        fetchUsers()
      } catch (error) {
        Swal.fire('Error', 'Failed to delete user', 'error')
      }
    }
  })
}

const openResetPasswordModal = (user) => {
  resetUser.value = user
  newPassword.value = ''
  resetDialog.value = true
}

const resetPassword = async () => {
  const { valid } = await resetFormRef.value.validate()
  if (!valid) return
  savingReset.value = true
  try {
    await axios.post(`/api/users/reset-password/${resetUser.value.id}`, { password: newPassword.value })
    Swal.fire({ icon: 'success', title: 'Success', text: 'Password reset successfully', timer: 2000, showConfirmButton: false })
    closeResetDialog()
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to reset password'
    Swal.fire({ icon: 'error', title: 'Error', text: message })
  } finally {
    savingReset.value = false
  }
}

const closeDialog = () => { dialog.value = false; if (formRef.value) formRef.value.reset() }
const closeResetDialog = () => { resetDialog.value = false; if (resetFormRef.value) resetFormRef.value.reset() }

onMounted(() => {
  fetchUsers()
  fetchTeams()
  fetchRoles()
})
</script>
